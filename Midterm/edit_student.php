<?php
include 'auth.php';
include 'db.php';

// Load provinces from JSON file
$provinces = json_decode(file_get_contents('provinces.json'), true);
if ($provinces === null) {
    die("Lỗi khi đọc file tỉnh/thành phố");
}

// Generate CSRF token
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$student_id = $_GET['id'];

// Fetch all departments for dropdown
$departments = $conn->query("SELECT * FROM d")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['token']) {
        $_SESSION['error'] = "CSRF token validation failed";
        header("Location: edit_student.php?id=$student_id");
        exit();
    }

    $student_id = $conn->real_escape_string($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['student_name']);
    $dep_id = $conn->real_escape_string($_POST['dep_id']); 
    $dob = $conn->real_escape_string($_POST['dob']);
    $address = $conn->real_escape_string($_POST['address']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone_number']);

    if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG images are allowed.";
        } elseif ($_FILES['img']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image must be less than 2MB.";
        } else {
            $upload_dir = "image/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $new_name = $upload_dir . $student_id . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['img']['tmp_name'], $new_name)) {
                if ($img && file_exists($img)) unlink($img);
                $img = $new_name;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }


    // Validate phone number format
    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $_SESSION['error'] = "Invalid phone number format";
        header("Location: edit_student.php?id=$student_id");
        exit();
    }

    // Check if phone number exists for another student
    $checkPhoneSql = "SELECT student_id FROM student WHERE phone_number = '$phone' AND student_id != '$student_id'";
    $checkResult = $conn->query($checkPhoneSql);
    
    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Phone number already exists for another student!";
    } else {
        $sql = "UPDATE student SET 
                student_name = '$name', 
                dep_id = '$dep_id', 
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

// Fetch student data
$sql = "SELECT * FROM student WHERE student_id = '$student_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Student not found";
    header("Location: view.php");
    exit();
}

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
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <h2>Edit Student</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
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
                <label>Department</label>
                <select name="dep_id" class="form-control" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department['dep_id']); ?>" 
                            <?php echo ($row['dep_id'] == $department['dep_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['dep_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($row['dob']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <select name="address" class="form-control select2-province" required>
                    <option value="">-- Choose provide / city --</option>
                    <?php foreach($provinces as $province): ?>
                        <option value="<?php echo htmlspecialchars($province); ?>" 
    <?php echo ($row['address'] == $province) ? 'selected' : ''; ?>>
    <?php echo htmlspecialchars($province); ?>
</option>

                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>
                <small class="form-text text-muted">Format: 10-15 digits</small>
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
            <div class="form-group"><input type="file" name="profile_picture" accept="image/*"></div>

            
            
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