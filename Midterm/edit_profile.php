<?php
include 'auth.php';
include 'db.php';

// Kiểm tra login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Load provinces from JSON file
$provinces = json_decode(file_get_contents('provinces.json'), true);
if ($provinces === null) {
    die("Lỗi khi đọc file tỉnh/thành phố");
}

// Lấy thông tin user
$sql = "SELECT u.*, s.student_name, s.email, s.phone_number, s.address, s.dep_id, d.dep_name, s.dob
        FROM users u
        LEFT JOIN student s ON u.student_id = s.student_id
        LEFT JOIN d d ON s.dep_id = d.dep_id
        WHERE u.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Nếu không tồn tại user hoặc role khác student
if (!$user || $user['role'] !== 'student') {
    $_SESSION['error'] = "Access denied.";
    header('Location: home.php');
    exit();
}

// Nếu submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    $update = $conn->prepare("UPDATE student SET email = ?, phone_number = ?, address = ? WHERE student_id = ?");
    $update->bind_param('sssi', $email, $phone, $address, $user['student_id']);
    if ($update->execute()) {
        $_SESSION['success'] = "Profile updated successfully.";
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>

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
            text-align: center;
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
            <h5 class="m-0 font-weight-bold">Edit Your Profile</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="edit_profile.php">

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['student_id']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['student_name']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <input type="text" class="form-control" 
                        value="<?php 
                            if (!empty($user['dep_id'])) {
                                echo htmlspecialchars($user['dep_id']);
                                if (!empty($user['dep_name'])) {
                                    echo ' - ' . htmlspecialchars($user['dep_name']);
                                }
                            } else {
                                echo 'Not specified';
                            }
                        ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" required class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                </div>

                <div class="form-group">
                <label>Address</label>
                <select name="address" class="form-control select2-province" required>
                    <option value="">-- Choose provide / city --</option>
                    <?php foreach($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>">
                            <?php echo htmlspecialchars($province); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

                <div class="d-flex justify-content-between">
                    <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
