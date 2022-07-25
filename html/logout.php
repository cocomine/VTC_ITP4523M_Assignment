<?php
session_start();
unset($_SESSION['isLogin']);
unset($_SESSION['staffID']);
unset($_SESSION['staffName']);
unset($_SESSION['position']);
header("location: ./login.php");