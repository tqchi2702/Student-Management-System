<?php
include 'auth.php';
include 'db.php';

// Check login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Only allow students
$sql = "SELECT role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'student') {
    $_SESSION['error'] = "Access denied.";
    header('Location: home.php');
    exit();
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: change_password.php');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header('Location: change_password.php');
        exit();
    }

    $checkPass = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $checkPass->bind_param('s', $username);
    $checkPass->execute();
    $passResult = $checkPass->get_result();
    $userPass = $passResult->fetch_assoc();

    if (!password_verify($current_password, $userPass['password'])) {
        $_SESSION['error'] = "Current password is incorrect.";
        header('Location: change_password.php');
        exit();
    }

    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $updatePass = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $updatePass->bind_param('ss', $new_password_hashed, $username);
    $updatePass->execute();

    $_SESSION['success'] = "Password changed successfully.";
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fc;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #343a40;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            width: 100%;
            text-align: left;
            font-weight: bold;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .logout {
            color: #ff4c4c;
        }
        .content {
            margin-left: 240px;
            padding: 40px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 0.35rem;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 0.35rem 0.35rem 0 0;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-heading mb-4">
        <a href="home.php">
            <img src="https://www.is.vnu.edu.vn/wp-content/uploads/2022/04/icon_negative_yellow_text-08-539x600.png" alt="School Logo" style="width: 80px; height: auto;">
        </a>
    </div>

    <a href="home.php"><i class="fas fa-home"></i> Home</a>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="view.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="admin.php"><i class="fas fa-users-cog"></i> Manage Users</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
    <?php endif; ?>

    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Content -->
<div class="content">
    <div class="card">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Change Password</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="change_password.php">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required class="form-control">
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="form-control">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
