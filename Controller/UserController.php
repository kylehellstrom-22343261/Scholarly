<?php
require_once 'Model/UserModel.php';
require_once 'Core/Database.php'; // If Database.php is used

class UserController extends Controller
{
    private $userModel;
    public function __construct()
    {
        $this->userModel = new UserModel();

        if (!isset($_COOKIE['Login_Info']) || $this->userModel->getUserByEmail($_COOKIE["Login_Info"])['PermissionLevel'] != 0) {
            $_SESSION['error'] = "Insufficient Permissions";
            header("Location: ?controller=auth&action=login");
            exit();
        } else {
            // print_r($_COOKIE);
        }
    }

    public function profile()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);
        // print_r($user);
        require_once 'View/User/UserProfileView.php';
    }

    public function updateProfile()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);

        // Check if userModel is set
        if (!$this->userModel) {
            die("Error: userModel is NULL! Check if it is being initialized correctly.");
        }

        $this->userModel->updateUserDetails(
            $user['Email']
            ,empty($_POST['FirstName']) ? $user['FirstName'] : $_POST['FirstName']
            ,empty($_POST['LastName']) ? $user['LastName'] : $_POST['LastName']
        );

        header("Location: ?controller=user&action=profile");
    }

    public function changePasswordView()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);

        $this->view('User/ChangePasswordView', ['user'=>$user]);
    }

    public function changePassword()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);

        // Check if userModel is set
        if (!$this->userModel) {
            die("Error: userModel is NULL! Check if it is being initialized correctly.");
        }

        $this->userModel->updatePassword(
            $user['Email']
            ,empty($_POST['NewPassword']) ? $user['Password'] : $_POST['NewPassword']
        );

        $this->view('User/ChangePasswordView', ['user'=>$user]);
    }

    public function settings()
    {
        require_once 'View/User/UserSettingsView.php';
    }

    public function restaurantView()
    {
        // Fetch all restaurants from the database
        $restaurants = $this->userModel->getBusinessByType("Restaurant");

        // print_r($restaurants);

        // Get search query from Form POST
        $searchQuery = $_POST['search'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter restaurants based on the search query
        if (!empty($searchQuery)) {
            $restaurants = array_filter($restaurants, function ($restaurant) use ($searchQuery) {
                return stripos($restaurant['BusinessName'], $searchQuery) !== false;
            });
        }

        require_once 'View/User/RestaurantView.php';
    }

    public function eventsView()
    {
        $events = $this->userModel->getBusinessByType("Event");

        // Get search query from Form POST
        $searchQuery = $_POST['search'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter restaurants based on the search query
        if (!empty($searchQuery)) {
            $events = array_filter($events, function ($events) use ($searchQuery) {
                return stripos($events['BusinessName'], $searchQuery) !== false;
            });
        }

        require_once 'View/User/EventsView.php';
    }

    public function servicesView()
    {
        $services = $this->userModel->getBusinessByType("Service");

        // Get search query from Form POST
        $searchQuery = $_POST['search'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter restaurants based on the search query
        if (!empty($searchQuery)) {
            $services = array_filter($services, function ($services) use ($searchQuery) {
                return stripos($services['BusinessName'], $searchQuery) !== false;
            });
        }

        require_once 'View/User/ServicesView.php';
    }

    public function activitiesView()
    {
        $activities = $this->userModel->getBusinessByType("Activity");

        // Get search query from Form POST
        $searchQuery = $_POST['search'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter restaurants based on the search query
        if (!empty($searchQuery)) {
            $activities = array_filter($activities, function ($activities) use ($searchQuery) {
                return stripos($activities['BusinessName'], $searchQuery) !== false;
            });
        }
        require_once 'View/User/ActivitiesView.php';
    }

    public function bookingView($businessName)
    {
        // Check if business is already set
        if (!isset($_SESSION['current_business']) || $_SESSION['current_business'] !== $businessName) {
            $_SESSION['cart'] = [];
            $_SESSION['current_business'] = $businessName;
        }

        // Generate a unique CSRF token to prevent duplicate submissions
        if (!isset($_SESSION['form_token'])) {
            $_SESSION['form_token'] = bin2hex(random_bytes(32));
        }

        // Handle Add to Cart Request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_name'])) {
            // Validate CSRF token
            if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
                die("Invalid form submission."); // TODO: Add error redirect
            }

            $itemName = $_POST['item_name'];
            $itemPrice = $_POST['item_price'];


            // Check if item is already in the cart
            if (isset($_SESSION['cart'][$itemName])) {
                $_SESSION['cart'][$itemName]['quantity'] += 1;
            } else {
                $_SESSION['cart'][$itemName] = [
                    'name' => $itemName,
                    'price' => $itemPrice,
                    'quantity' => 1
                ];
            }

            // Regenerate token to prevent re-submission on refresh
            $_SESSION['form_token'] = bin2hex(random_bytes(32));
            // Redirect to the same page to clear POST data and prevent duplicate submissions
            header("Location: ?controller=user&action=bookingView&businessName=" . urlencode($businessName));
            exit();
        }

        // Fetch items from db
        $items = $this->userModel->getItemsByBusiness($businessName);
        $business = $this->userModel->getBusinessByBusinessName($businessName);

        if ($businessName) {
            $this->view('User/BookingView', ['items' => $items, 'business' => $business]);
        } else {
            $_SESSION['error'] = "Could not find business";
            $this->view('User/BookingView', []);
            echo "Business not found.";
        }
    }

    public function orderConfirmView()
    {
        $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['item_name'], $_POST['quantity'])) {
                $itemName = $_POST['item_name'];
                $quantity = $_POST['quantity'];
            } elseif (isset($_POST['remove_item_name'])) {
                $removeItemName = $_POST['remove_item_name'];

                // Remove the item from the session
                foreach ($cartItems as $key => $item) {
                    if ($item['name'] === $removeItemName) {
                        unset($cartItems[$key]);
                        break;
                    }
                }
            }
            $_SESSION['cart'] = $cartItems;
        }

        // Calculate the total price
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }


        require_once 'View/User/OrderConfirmView.php';
    }

    public function reviewView()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);
        if (!$user || $user['VerifiedCustomer'] != 1) {
            $_SESSION['error'] = "You must be a verified customer to view this page.";

            // Stops view redirect and keeps user on current view
            header("Location: " . $_SERVER['HTTP_REFERER'] ?? '?controller=user&action=sendMessagesView');
            exit();
        }

        $reviews = $this->userModel->getReviewByReviewID();

        // Get search query from Form POST
        $searchQuery = $_POST['search'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter Reviews based on the search query
        if (!empty($searchQuery)) {
            $reviews = array_filter($reviews, function ($reviews) use ($searchQuery) {
                return stripos($reviews['BusinessName'], $searchQuery) !== false;
            });
        }
        require_once 'View/User/ReviewsView.php';
    }


    public function historyView()
    {
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"])['UserID'];
        // print_r($user);
        $restaurant = $this->userModel->getBusinessStatsByType("Restaurant", $user);
        $services = $this->userModel->getBusinessStatsByType("Service", $user);
        $events = $this->userModel->getBusinessStatsByType("Event", $user);
        $activities = $this->userModel->getBusinessStatsByType("Activity", $user);

        require_once 'View/User/HistoryView.php';
    }


    public function placeOrder()
    {
        $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        if (!is_null($cartItems)) {
            $orderID = $this->userModel->getOrderID();
            foreach ($cartItems as $item) {
                $quantity = $item['quantity'];
                for ($i = 0; $i < $quantity; $i++) {
                    $itemName = $item['name'];
                    $price = $item['price'];
                    $businessName = $_SESSION['current_business'];
                    $userID = $this->userModel->getUserByEmail($_COOKIE["Login_Info"])['UserID'];
                    $this->userModel->placeOrder($businessName, $price, $userID, $orderID, $itemName);
                }
            }
            $_SESSION['success'] = "Order Placed!";
        } else {
            $_SESSION['error'] = "Cart is empty.";
        }
        // $this->view('User/RestaurantView', []);
        header("Location: ?controller=user&action=restaurantView");
        exit();
    }

    public function addReviewView()
    {
        $businesses = $this->userModel->getBusinesses();
        // Fetch user details using the logged-in email from cookies
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);

        // Check if user exists
        if (!$user) {
            die("Error: User not found!");
        }
        require_once 'View/User/AddReviewView.php';
    }

    public function addReview()
    {
        // Fetch user details
        $user = $this->userModel->getUserByEmail($_COOKIE["Login_Info"]);

        if (!$user) {
            die("Error: User not found.");
        }

        $userID = $user['UserID'];

        // Get form data
        $businessName = $_POST['business'] ?? 'EMPTY';
        $rating = $_POST['rating'] ?? 'EMPTY';
        $comment = $_POST['comment'] ?? 'EMPTY';
        $business = $this->userModel->getBusinessByBusinessName($businessName)[0]['UserID'];
        print_r($business);

        // Add review
        $this->userModel->createReview($userID, $business, $rating, $comment, $businessName);
        header("Location: ?controller=user&action=reviewView");
        exit();
    }

    public function userMessagesView()
    {
        $users = $this->userModel->getUsersByVerifiedCustomer(0); // 0 = normal user
        $businessUsers = $this->userModel->getUsersByVerifiedCustomer(1); // business user

        // Get search query from Form POST
        $searchUserQuery = $_POST['searchUser'] ?? '';
        $searchBusinessQuery = $_POST['searchBusiness'] ?? '';
        // echo "<br> Search Q: "; print_r($searchQuery);

        // Filter Reviews based on the search query
        if (!empty($searchUserQuery)) {
            $users = array_filter($users, function ($users) use ($searchUserQuery) {
                return stripos($users['Email'], $searchUserQuery) !== false;
            });
        }

        if (!empty($searchBusinessQuery)) {
            $businessUsers = array_filter($businessUsers, function ($businessUsers) use ($searchBusinessQuery) {
                return stripos($businessUsers['Email'], $searchBusinessQuery) !== false;
            });
        }

        require_once 'View/User/userMessagesView.php';
    }

    public function sendMessage()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $senderID = $this->userModel->getUserByEmail($_COOKIE['Login_Info'])['UserID'];
            $receiverID = $_POST['receiverID'];
            $message = trim($_POST['messageText']);

            if ($this->userModel->getUserById($receiverID)['PermissionLevel'] == 1) {
                $this->userModel->createInquiry($senderID, $receiverID, $message);
            } else {
                $this->userModel->createMessage($senderID, $receiverID, $message);
            }

            $_SESSION['success'] = "Message sent successfully!";
        } else {
            $_SESSION['error'] = "Message failed to send";
        }
        // Stops view redirect and keeps user on current view
        header("Location: " . $_SERVER['HTTP_REFERER'] ?? '?controller=user&action=sendMessagesView');
        exit();
    }

    public function sendMessageView($receiverID)
    {

        // Sender is the logged-in user
        $senderID = $this->userModel->getUserByEmail($_COOKIE['Login_Info'])['UserID'];
        if ($this->userModel->getUserById($receiverID)['PermissionLevel'] == 1) {
            $previousMessages = $this->userModel->getUserInquiries($senderID, $receiverID);
        } else {
            $previousMessages = $this->userModel->getUserMessages($senderID, $receiverID);
        }

        print_r($previousMessages);
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // The user submitted a message
            $message = trim($_POST['messageText']);

            // Insert the new message in the DB (pending, etc.)
            // e.g. $this->userModel->createMessage($messageID, $senderID, $receiverID, $message);

            $_SESSION['success'] = "Message sent successfully!";
            // Optional: redirect or stay on the same page
            // header("Location: ?controller=user&action=sendMessageView&receiverID=$receiverID");
            // exit();
        }

        // Always fetch previous messages so the user can see the conversation

        // Pass $previousMessages, $receiverID, etc. to the view
        require_once 'View/User/MessagingView.php';
    }
}
