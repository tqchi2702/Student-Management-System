<?php
include("db.php");
include("auth.php");

function generateNewDepId($conn) {
    $get_last_id = mysqli_query($conn, "SELECT dep_id FROM d ORDER BY dep_id DESC LIMIT 1");
    if(mysqli_num_rows($get_last_id) > 0) {
        $row = mysqli_fetch_assoc($get_last_id);
        $last_id = $row['dep_id'];
        $number = (int)substr($last_id, 2);
        $new_number = str_pad($number + 1, 2, '0', STR_PAD_LEFT);
    } else {
        $new_number = "01";
    }
    return "IS" . $new_number;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $dep_name = trim($_POST['dep_name']);
    $dep_email = trim($_POST['dep_email']);
    $dep_password = $_POST['dep_password'];
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    
    if (!filter_var($dep_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ";
    } elseif (strlen($dep_password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        // Generate new department ID
        $new_dep_id = generateNewDepId($conn);
        
        // Hash password
        $hashed_password = password_hash($dep_password, PASSWORD_DEFAULT);
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert into department table
            $stmt = $conn->prepare("INSERT INTO d (dep_id, dep_name, dep_email, dep_password, location, status) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $new_dep_id, $dep_name, $dep_email, $hashed_password, $location, $status);
            $stmt->execute();
            $stmt->close();
            
            // Insert into users table using dep_id as username
            $role = "department"; // Set role as department
            $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) 
                                        VALUES (?, ?, ?)");
            $stmt_user->bind_param("sss", $new_dep_id, $hashed_password, $role);
            $stmt_user->execute();
            $stmt_user->close();
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success = "Thêm khoa thành công với ID: $new_dep_id. Tài khoản đăng nhập: $new_dep_id";
        } catch (Exception $e) {
            // Rollback transaction if there's an error
            mysqli_rollback($conn);
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Khoa mới</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; }
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
        .sidebar a:hover { background-color: #495057; }
        .logout { color: #ff4c4c; }
        .content { margin-left: 240px; padding: 40px; }
        h2 { text-align: center; color: #343a40; margin-bottom: 30px; }
        .card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 30px; }
        .btn-submit { background-color: #28a745; border-color: #28a745; }
        .btn-back { background-color: #6c757d; border-color: #6c757d; }
        .alert { margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .badge-success { background-color: #28a745; }
        .badge-secondary { background-color: #6c757d; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <h2><i class="fas fa-plus-circle"></i> Add New Department</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <div class="mt-3">
                    <a href="department_list.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View Department List
                    </a>
                    <a href="department.php" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Add New Department
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="dep_name">Department Name:</label>
                    <input type="text" class="form-control" id="dep_name" name="dep_name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="dep_email">Email:</label>
                    <input type="email" class="form-control" id="dep_email" name="dep_email" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="dep_password">Password:</label>
                    <input type="password" class="form-control" id="dep_password" name="dep_password" required minlength="6">
                    <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" class="form-control" id="location" name="location" required maxlength="200">
                </div>
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <a href="department_list.php" class="btn btn-back ml-2">
                        <i class="fas fa-arrow-left"></i> Back to Department list
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>