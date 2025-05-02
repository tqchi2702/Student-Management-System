<?php
include 'auth.php';
include 'db.php';

// Kiểm tra login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Lấy username đăng nhập
$username = $_SESSION['username'];

// Lấy thông tin user
$sql = "SELECT u.*, s.student_name, s.email, s.phone_number, s.address, s.major, s.dob
        FROM users u
        LEFT JOIN student s ON u.student_id = s.student_id
        WHERE u.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
        .profile-item {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .profile-item:last-child {
            border-bottom: none;
        }
        .profile-label {
            font-weight: bold;
            color: #5a5c69;
            width: 200px;
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
            <h5 class="m-0 font-weight-bold">Your Profile</h5>
        </div>
        <div class="card-body">
            <div class="profile-item d-flex">
                <div class="profile-label">Student ID:</div>
                <div><?php echo htmlspecialchars($user['student_id']); ?></div>
            </div>

            <?php if ($user['student_id']): ?>
                <div class="profile-item d-flex">
                    <div class="profile-label">Full Name:</div>
                    <div><?php echo htmlspecialchars($user['student_name']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Email:</div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Phone:</div>
                    <div><?php echo htmlspecialchars($user['phone_number']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Address:</div>
                    <div><?php echo htmlspecialchars($user['address']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Major:</div>
                    <div><?php echo htmlspecialchars($user['major']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Date of Birth:</div>
                    <div><?php echo htmlspecialchars($user['dob']); ?></div>
                </div>
            <?php else: ?>
                <div class="profile-item">
                    <div class="text-danger">No student information linked to this account.</div>
                </div>
            <?php endif; ?>

            <div class="mt-4 text-right">
                <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-user-edit"></i> Edit Profile</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
