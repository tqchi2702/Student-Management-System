<?php
include 'auth.php';
include 'db.php';

$success = false;
$errorMessage = '';

// Load provinces from JSON file
$provinces = json_decode(file_get_contents('provinces.json'), true);
if ($provinces === null) {
    die("Lỗi khi đọc file tỉnh/thành phố");
}

// Function to generate student ID
function generateStudentID($conn) {
    $year = date('y');
    $result = $conn->query("SELECT MAX(student_id) as max_id FROM student WHERE student_id LIKE '$year%'");
    $row = $result->fetch_assoc();
    $last_id = $row['max_id'];
    
    if ($last_id) {
        $numeric_part = (int)substr($last_id, 2);
        $numeric_part++;
    } else {
        $numeric_part = 1;
    }
    
    return $year . str_pad($numeric_part, 4, '0', STR_PAD_LEFT);
}

// Function to generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

// Get departments from database
// Get departments from database
$departments = [];  // Better variable name than just $d
$dept_result = $conn->query("SELECT dep_id, dep_name FROM d"); // Changed table name from 'd' to 'department'

if ($dept_result === false) {
    die("Error fetching departments: " . $conn->error);
}

while ($row = $dept_result->fetch_assoc()) {
    $departments[$row['dep_id']] = $row['dep_name']; // Fixed syntax error
}

// To debug, check what departments were actually loaded:
// print_r($departments);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Generate student ID
        $student_id = generateStudentID($conn);
        
        // Validate and sanitize inputs
        $student_name = $conn->real_escape_string($_POST['student_name']);
        $dep_id = $conn->real_escape_string($_POST['dep_id']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $address = $conn->real_escape_string($_POST['address']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone_number = $conn->real_escape_string($_POST['phone_number']);

        // Check if email exists
        $check = $conn->query("SELECT email FROM student WHERE email = '$email'");
        if ($check->num_rows > 0) {
            throw new Exception("Email đã tồn tại!");
        }

        // Check if phone number exists
        $check = $conn->query("SELECT phone_number FROM student WHERE phone_number = '$phone_number'");
        if ($check->num_rows > 0) {
            throw new Exception("Số điện thoại đã tồn tại!");
        }

        // Start transaction
        $conn->begin_transaction();

        // Insert new student
        $stmt = $conn->prepare("INSERT INTO student (student_id, student_name, dep_id, dob, address, email, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $student_id, $student_name, $dep_id, $dob, $address, $email, $phone_number);
        $stmt->execute();
        $stmt->close();

        // Generate password and create user account
        // $password = generateRandomPassword();
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $password = $student_id; // Dùng student_id làm mật khẩu
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?, ?, 'student', ?)");
        $stmt->bind_param("sss", $student_id, $hashed_password, $student_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        $success = true;
        $_SESSION['new_student_account'] = [
            'student_id' => $student_id,
            'password' => $password,
            'name' => $student_name,
            'email' => $email
        ];
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 20px; }
        .btn-register { background-color: #28a745; color: white; }
        .btn-back { background-color: #6c757d; color: white; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .form-group { margin-bottom: 20px; }
        .info-display {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .text-danger { color: #dc3545!important; }
        .bg-light { background-color: #f8f9fa!important; }
        .copy-btn { cursor: pointer; }
        .copy-btn:hover { opacity: 0.8; }
    </style>
</head>
<body>


 <?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <h2>Add New Student</h2>
    <div class="card">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Successful!
                
                <div class="form-group mt-3">
                    <label>Student ID</label>
                    <div class="info-display">
                        <?php echo $student_id; ?>
                    </div>
                </div>
                
                <!-- <div class="form-group">
                    <label>Student Name</label>
                    <div class="info-display">
                        <?php echo $student_id; ?>
                        <small class="text-muted">(Sử dụng mã sinh viên để đăng nhập)</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu tạm thời</label>
                    <div class="info-display bg-light">
                        <span class="text-danger font-weight-bold"><?php echo $password; ?></span>
                        <span class="copy-btn" onclick="copyToClipboard('<?php echo $password; ?>')">
                            <i class="fas fa-copy"></i> Copy
                        </span>
                    </div>
                    
                </div> -->
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Students List
                    </a>
                    <a href="admin.php" class="btn btn-primary">
                        <i class="fas fa-users-cog"></i> Manage Users
                    </a>
                </div>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Student ID</label>
                <div class="info-display">
                    <?php echo generateStudentID($conn); ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="student_name" class="form-control" placeholder="Nhập họ tên đầy đủ" required>
            </div>
            
            <div class="form-group">
                <label>Department</label>
                <select name="dep_id" class="form-control" required>
                    <option value="">-- Choose a department --</option>
                    <?php foreach ($departments as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Date Of Birth</label>
                <input type="date" name="dob" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Tỉnh/Thành phố</label>
                <select name="address" class="form-control select2-province" required>
                    <option value="">-- Chọn tỉnh/thành phố --</option>
                    <?php foreach($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>">
                            <?php echo htmlspecialchars($province); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone_number" class="form-control" placeholder="Nhập số điện thoại" required>
            </div>
            
            <button type="submit" class="btn btn-register btn-block">
                <i class="fas fa-user-plus"></i> Add new Student
            </button>
            <a href="view.php" class="btn btn-back btn-block mt-2">
                <i class="fas fa-arrow-left"></i> Back to Student List
            </a>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert("Đã sao chép mật khẩu vào clipboard!");
        });
    }
</script> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-province').select2({
        placeholder: "Chọn hoặc tìm kiếm tỉnh/thành",
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>