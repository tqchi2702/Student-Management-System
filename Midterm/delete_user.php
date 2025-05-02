<?php
include 'auth.php';
include 'db.php';

if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('You do not have permission to delete users.'); window.location.href='admin.php';</script>";
    exit;
}

$id = $_GET['id'];

$check_admin = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $check_admin->fetch_assoc();

if ($user['role'] == 'admin') {
    echo "<script>alert('You cannot delete an admin account.'); window.location.href='admin.php';</script>";
    exit;
}

$sql = "DELETE FROM users WHERE id = $id";
if ($conn->query($sql) === TRUE) {
    echo "<script>alert('User deleted successfully'); window.location.href='admin.php';</script>";
} else {
    echo "Error deleting user: " . $conn->error;
}
?>
