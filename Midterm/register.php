<?php
include 'auth.php';
include 'db.php';

$success = false;
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Validate and sanitize inputs
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $student_name = $conn->real_escape_string($_POST['student_name']);
        $major = $conn->real_escape_string($_POST['major']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $address = $conn->real_escape_string($_POST['address']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone_number = $conn->real_escape_string($_POST['phone_number']);

        // Check if student ID already exists
        $check = $conn->query("SELECT student_id FROM student WHERE student_id = '$student_id'");
        if ($check->num_rows > 0) {
            throw new Exception("Student ID already exists!");
        }

        // Check if email already exists
        $check = $conn->query("SELECT email FROM student WHERE email = '$email'");
        if ($check->num_rows > 0) {
            throw new Exception("Email already exists!");
        }

        // Check if phone number already exists
        $check = $conn->query("SELECT phone_number FROM student WHERE phone_number = '$phone_number'");
        if ($check->num_rows > 0) {
            throw new Exception("Phone number already exists!");
        }

        // Insert new student
        $stmt = $conn->prepare("INSERT INTO student (student_id, student_name, major, dob, address, email, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $student_id, $student_name, $major, $dob, $address, $email, $phone_number);

        if ($stmt->execute()) {
            $success = true;
            $_SESSION['message'] = "Student added successfully!";
        }

        $stmt->close();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
    
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
        }
        .sidebar a {
            color: white;
            padding: 12px 20px;
            width: 100%;
            text-decoration: none;
            display: block;
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
        .btn-register {
            background-color: #28a745;
            color: white;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
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
    <div class="card">
        <h2>Register New Student</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Student added successfully! Redirecting...
            </div>
            <meta http-equiv="refresh" content="2;url=view.php">
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" class="form-control" placeholder="Enter student ID" required>
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="student_name" class="form-control" placeholder="Enter full name" required>
            </div>
            
            <div class="form-group">
                <label>Major</label>
                <input type="text" name="major" class="form-control" placeholder="Enter major" required>
            </div>
            
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" placeholder="Enter address" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone_number" class="form-control" placeholder="Enter phone number" required>
            </div>
            
            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Register Student
            </button>
            <a href="view.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Student List
            </a>
        </form>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>