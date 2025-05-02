<?php
include 'auth.php';
include 'db.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No student ID provided!";
    header("Location: view.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);
$sql = "SELECT s.*, u.username as account_username 
        FROM student s 
        LEFT JOIN users u ON s.student_id = u.student_id 
        WHERE s.student_id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    $_SESSION['error'] = "Student not found!";
    header("Location: view.php");
    exit();
}

$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details</title>
    
    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #2e59d9;
            --background-color: #f8f9fc;
            --card-shadow: 0 0 15px rgba(0,0,0,0.1);
            --border-radius: 0.35rem;
        }
        
        body {
            background-color: var(--background-color);
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
            box-shadow: var(--card-shadow);
            border-radius: var(--border-radius);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }
        
        .detail-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            width: 200px;
            color: #5a5c69;
        }
        
        .account-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .account-active {
            background-color: #1cc88a;
            color: white;
        }
        
        .account-inactive {
            background-color: #e74a3b;
            color: white;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
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
    <div class="card">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Student Details</h5>
        </div>
        <div class="card-body">
            <div class="detail-row">
                <div class="detail-label">Student ID</div>
                <div><?php echo htmlspecialchars($student['student_id']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Full Name</div>
                <div><?php echo htmlspecialchars($student['student_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div><?php echo htmlspecialchars($student['email']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Phone Number</div>
                <div><?php echo htmlspecialchars($student['phone_number']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Address</div>
                <div><?php echo htmlspecialchars($student['address']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Major</div>
                <div><?php echo htmlspecialchars($student['major']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Date of Birth</div>
                <div><?php echo htmlspecialchars($student['dob']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Account Status</div>
                <div>
                    <?php if ($student['account_username']): ?>
                        <span class="account-badge account-active">
                            <i class="fas fa-check-circle"></i> Active (<?php echo htmlspecialchars($student['account_username']); ?>)
                        </span>
                    <?php else: ?>
                        <span class="account-badge account-inactive">
                            <i class="fas fa-times-circle"></i> No account
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                <a href="edit_student.php?id=<?php echo htmlspecialchars($student['student_id']); ?>" class="btn btn-primary mr-2">
                    <i class="fas fa-edit"></i> Edit Student
                </a>
                <a href="view.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>