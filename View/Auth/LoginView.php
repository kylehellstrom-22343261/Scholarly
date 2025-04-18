<!DOCTYPE html>
<html>

<head>
    <title>Scholarly Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="Public/css/Styles.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid p-0 position-relative">
        <img src="https://www.studiesup.com/wp-content/uploads/import/UL_Glucksman-Library-1-scaled.jpg" style="float:left; min-height:650px; height:100vh">

        <div style="background-color:white; width:40vw; min-width:500px; min-height:650px; height:100vh; right:0;" class="justify-content-center position-absolute">
            <div class="d-flex justify-content-center" style="margin-top:25vh;">
                <div class="form-signin">
                    <img src="Public/Images/scholarly logo.png" style="height:5rem; width:auto;">

                    <h1 class="pt-5 pb-4">Log in</h1>

                    <?php if (!empty($_SESSION['error'])) : ?>
                        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                            <div id="errorToast" class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <?php echo $_SESSION['error'] ?>
                                    </div>
                                    <!-- <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"><?php unset($_SESSION['error']) ?></button> -->
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="?controller=auth&action=login">
                        <div class="form-floating mb-4">
                            <input name="Email" id="emailInput" type="email" class="form-control" placeholder="name@ul.ie" value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>" required>

                            <label for="emailInput">UL Email Address</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input name="Password" id="passwordInput" type="password" class="form-control" placeholder="Password" required>

                            <label for="passwordInput">Password</label>
                        </div>

                        <div class="pt-4">
                            <button class="position-relative start-50 translate-middle btn" type="submit">Submit</button>
                        </div>
                    </form>

                    <div class="pt-4" style="text-align:center">
                        Register a new account <a href="?controller=auth&action=registerView">here</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>