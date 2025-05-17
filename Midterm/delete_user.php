<?php
include 'auth.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the user to be deleted
$id = intval($_GET['id']); // Sanitize input
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];

// Check if user is trying to delete themselves
if ($id == $current_user_id) {
    $_SESSION['error'] = "You cannot delete your own account";
    header("Location: admin.php");
    exit;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Permission checks
if ($current_user_role == 'superadmin') {
    // Superadmin can delete anyone except themselves (already checked above)
} elseif ($current_user_role == 'admin') {
    // Admin can only delete student and department accounts
    if ($user['role'] == 'admin' || $user['role'] == 'superadmin') {
        $_SESSION['error'] = "You don't have permission to delete this user";
        header("Location: admin.php");
        exit;
    }
} else {
    // Other roles can't delete users
    $_SESSION['error'] = "You don't have permission to delete users";
    header("Location: admin.php");
    exit;
}

// Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['message'] = "User deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting user: " . $conn->error;
}

header("Location: admin.php");
exit;
?>