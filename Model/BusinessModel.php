<?php
if (!file_exists('Core/Model.php')) {
    die("Error: Core/Model.php not found! Check the file path.");
}

require_once 'Core/Model.php';

class BusinessModel extends Model{

    public function __construct() {
        parent::__construct();
    }

    public function getBusinessByUserID($userId) {
        return $this->db->query("SELECT * FROM Business WHERE UserId = ?", [$userId])->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrderPriceByUserID($userID, $orderPrice)
    {
        return $this->db->query("UPDATE BusinessStats 
SET OrderPrice = ?, OrderStatus = 'Completed' 
WHERE UserID = ? AND OrderStatus = 'Pending';",
            [$orderPrice, $userID]
        );
    }

    public function getSimilarItemNames($itemName){
        $existingNames = $this->db->query(
            "SELECT ItemName FROM Item WHERE ItemName LIKE ?"
            ,[$itemName])->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($existingNames)) {
            return "Business Name already exists";
        }
    }

    // Register new Item
    public function addItem($businessName, $description, $price, $itemName){
        // Add new item to Item table
        $this->db->query(
            "INSERT INTO Item (BusinessName, Description, ItemPrice, ItemName) 
                VALUES (?, ?, ?, ?)"
            ,[$businessName, $description, $price, $itemName]
        );
        
        return $this->db->lastInsertId();
    }

    public function setResponseByReviewID($response, $reviewID){
        $query = "UPDATE Review SET Response = ? WHERE ReviewID = ?";
        return $this->db->query($query, [$response, $reviewID]);
    }

}