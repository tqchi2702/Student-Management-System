<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] != 'admin') {
    echo "<h2 style='color: red; text-align: center; margin-top: 50px;'>Bạn không có quyền truy cập trang này!</h2>";
    exit();
}
?>
