<?php
include("db.php");
include("auth.php");

// Kiểm tra xem có phải người dùng admin không
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'department')) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$dep_id = '';

// Lấy thông tin department hiện tại
if (isset($_GET['id'])) {
    $dep_id = $_GET['id'];
    $query = "SELECT * FROM d WHERE dep_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $dep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: department_list.php");
        exit();
    }
    
    $department = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: department_list.php");
    exit();
}

// Xử lý form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $dep_name = trim($_POST['dep_name']);
    $dep_email = trim($_POST['dep_email']);
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    
    if (empty($dep_name) || empty($dep_email) || empty($location)) {
        $error = "Vui lòng điền đầy đủ thông tin";
    } elseif (!filter_var($dep_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ";
    } else {
        // Kiểm tra xem email đã tồn tại chưa (ngoại trừ email hiện tại)
        $check_email = $conn->prepare("SELECT dep_id FROM d WHERE dep_email = ? AND dep_id != ?");
        $check_email->bind_param("ss", $dep_email, $dep_id);
        $check_email->execute();
        $email_result = $check_email->get_result();
        
        if ($email_result->num_rows > 0) {
            $error = "Email này đã được sử dụng bởi department khác";
        } else {
            // Bắt đầu transaction
            $conn->begin_transaction();
            
            try {
                // Kiểm tra xem người dùng có muốn thay đổi mật khẩu không
                if (!empty($_POST['dep_password'])) {
                    $dep_password = $_POST['dep_password'];
                    
                    if (strlen($dep_password) < 6) {
                        $error = "Mật khẩu phải có ít nhất 6 ký tự";
                        $conn->rollback();
                    } else {
                        $hashed_password = password_hash($dep_password, PASSWORD_DEFAULT);
                        
                        // Cập nhật thông tin và mật khẩu trong bảng d
                        $stmt = $conn->prepare("UPDATE d SET dep_name = ?, dep_email = ?, dep_password = ?, location = ?, status = ? WHERE dep_id = ?");
                        $stmt->bind_param("ssssss", $dep_name, $dep_email, $hashed_password, $location, $status, $dep_id);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Cập nhật mật khẩu trong bảng users
                        $stmt_user = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                        $stmt_user->bind_param("ss", $hashed_password, $dep_id);
                        $stmt_user->execute();
                        $stmt_user->close();
                    }
                } else {
                    // Chỉ cập nhật thông tin, không thay đổi mật khẩu
                    $stmt = $conn->prepare("UPDATE d SET dep_name = ?, dep_email = ?, location = ?, status = ? WHERE dep_id = ?");
                    $stmt->bind_param("sssss", $dep_name, $dep_email, $location, $status, $dep_id);
                    $stmt->execute();
                    $stmt->close();
                }
                
                // Commit transaction nếu không có lỗi
                $conn->commit();
                
                $success = "Cập nhật thông tin department thành công!";
                // Cập nhật lại thông tin của department sau khi update
                $query = "SELECT * FROM d WHERE dep_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $dep_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $department = $result->fetch_assoc();
                $stmt->close();
            } catch (Exception $e) {
                // Rollback transaction nếu có lỗi
                $conn->rollback();
                $error = "Lỗi: " . $e->getMessage();
            }
        }
        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin department</title>
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
        .form-group { margin-bottom: 20px; }
        .btn-submit { background-color: #28a745; color: white; }
        .btn-back { background-color: #6c757d; color: white; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    </style>
</head>
<body>

<!-- Sidebar -->
 <?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <h2>Edit Department Information</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="dep_id">Department ID:</label>
                <input type="text" class="form-control" id="dep_id" value="<?php echo htmlspecialchars($department['dep_id']); ?>" readonly>
                <small class="text-muted">ID không thể thay đổi</small>
            </div>
            
            <div class="form-group">
                <label for="dep_name">Department Name:</label>
                <input type="text" class="form-control" id="dep_name" name="dep_name" value="<?php echo htmlspecialchars($department['dep_name']); ?>" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="dep_email">Email:</label>
                <input type="email" class="form-control" id="dep_email" name="dep_email" value="<?php echo htmlspecialchars($department['dep_email']); ?>" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="dep_password">Password (leave blank to keep current):</label>
                <input type="password" class="form-control" id="dep_password" name="dep_password" minlength="6">
                <small class="text-muted">Mật khẩu phải có ít nhất 6 ký tự. Để trống nếu không muốn thay đổi mật khẩu.</small>
            </div>
            
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($department['location']); ?>" required maxlength="200">
            </div>
            
            <div class="form-group">
                <label for="status">Status:</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="active" <?php echo ($department['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($department['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-submit btn-block">
    <i class="fas fa-save"></i> Save Changes
</button>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="department_list.php" class="btn btn-back btn-block mt-2">
        <i class="fas fa-arrow-left"></i> Back To Department List
    </a>
<?php elseif ($_SESSION['role'] === 'department'): ?>
    <a href="view_department.php?id=<?= $_SESSION['username'] ?>" class="btn btn-back btn-block mt-2">
        <i class="fas fa-arrow-left"></i> Back To My Department
    </a>
<?php endif; ?>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>