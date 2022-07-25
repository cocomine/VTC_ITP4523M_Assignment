<?php
include "function/config.inc.php";
session_start();
$login_error = false;

/* check is login */
if (@$_SESSION['isLogin']) {
    header("location: ./");
    exit();
}

/* check login */
if (!empty($_POST['staffID']) && !empty($_POST['pwd'])) {
    $conn = new mysqli(SQL_HOST, SQL_USERNAME, SQL_PASSWORD, SQL_DATABASE) or die("Database connect error!");
    $staffID = filter_var($_POST['staffID'], FILTER_SANITIZE_STRING);
    $pwd = filter_var($_POST['pwd'], FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("SELECT staffID, staffName, position FROM staff WHERE staffID = ?");
    $stmt->bind_param("s", $staffID);
    $stmt->execute();
    $result = $stmt->get_result();

    echo $result->num_rows;
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        $_SESSION['isLogin'] = true;
        $_SESSION['staffID'] = $row['staffID'];
        $_SESSION['staffName'] = $row['staffName'];
        $_SESSION['position'] = $row['position'] === "Manager" ? 1 : 0; //Manager = 1, Staff = 0

        header("location: ./");
        exit();
    }

    $login_error = true;
}
?>

<html class="login" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Login - Sales System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/metisMenu.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/slicknav.min.css">
    <link rel="stylesheet" href="assets/css/typography.css">
    <link rel="stylesheet" href="assets/css/default-css.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <script src="assets/js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>
<div id="preloader">
    <div class="loader"></div>
</div>

<!--Login From Start-->
<div class="login-area login-s2">
    <div class="container">
        <div class="login-box ptb--100">
            <form method="post">
                <div class="login-form-head">
                    <h2>Sign In</h2>
                </div>
                <?php
                if ($login_error) {
                    echo "<div class='alert alert-danger' role='alert'>Login information error!</div>";
                }
                ?>
                <div class="login-form-body">
                    <div class="form-gp">
                        <label for="staffID">Staff ID</label>
                        <input type="text" id="staffID" name="staffID">

                    </div>
                    <div class="form-gp">
                        <label for="pwd">Password</label>
                        <input type="password" id="pwd" name="pwd">
                    </div>
                    <div class="submit-btn-area">
                        <button id="form_submit" type="submit" class="btn btn-rounded btn-primary mb-3">Submit</button>
                        <br>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Login From End-->

<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/metisMenu.min.js"></script>
<script src="assets/js/jquery.slimscroll.min.js"></script>
<script src="assets/js/jquery.slicknav.min.js"></script>


<script src="assets/js/plugins.js"></script>
<script src="assets/js/scripts.js"></script>
</body>

</html>