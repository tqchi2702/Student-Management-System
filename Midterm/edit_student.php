<?php
include 'auth.php';
include 'db.php';

$student_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['student_name']);
    $major = $conn->real_escape_string($_POST['major']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone_number']);

    // Check if phone number already exists for another student
    $checkPhoneSql = "SELECT student_id FROM student WHERE phone_number = '$phone' AND student_id != '$student_id'";
    $checkResult = $conn->query($checkPhoneSql);
    
    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Phone number already exists for another student!";
    } else {
        $sql = "UPDATE student SET 
                student_name = '$name', 
                major = '$major', 
                dob = '$dob', 
                address = '$address', 
                email = '$email', 
                phone_number = '$phone'
                WHERE student_id = '$student_id'";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Student updated successfully";
            header("Location: view.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating record: " . $conn->error;
        }
    }
}

$sql = "SELECT * FROM student WHERE student_id = '$student_id'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    
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
            box-sizing: border-box;
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
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-heading text-white mb-4">
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
    <?php endif; ?>

    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Content -->
<div class="content">
    <h2>Edit Student</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
            
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['student_id']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="student_name" class="form-control" value="<?php echo htmlspecialchars($row['student_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Major</label>
                <input type="text" name="major" class="form-control" value="<?php echo htmlspecialchars($row['major']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($row['dob']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Account Status</label>
                <?php
                $account = $conn->query("SELECT username FROM users WHERE student_id = '".$row['student_id']."'")->fetch_assoc();
                if ($account): ?>
                    <div class="alert alert-success">
                        Has account: <?php echo htmlspecialchars($account['username']); ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">No account assigned</div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-save">Save Changes</button>
            <a href="view.php" class="btn btn-back">Back to Student List</a>
        </form>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>