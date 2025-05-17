<?php
include 'auth.php';
include 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);
    $student_id = isset($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : null;

    // Check if username exists
    $check = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Username already exists!";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, password, role, student_id) VALUES ('$username', '$password', '$role', ";
        $sql .= $student_id ? "'$student_id'" : "NULL";
        $sql .= ")";
        
        if ($conn->query($sql)) {
            $_SESSION['message'] = "User added successfully";
            header("Location: admin.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    }
}

// Get available students (not assigned to any account)
$students = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
    
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
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        #student-assignment {
            display: none;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
 <?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <h2>Add New User</h2>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required id="role-select">
                    <!-- //<option value="user">User</option> -->
                    <option value="admin">Admin</option>
                    <option value="student">Student</option>
                    <option value="department">Department</option>
                </select>
            </div>
            
            <div class="form-group" id="student-assignment">
                <label>Assign to Student</label>
                <select name="student_id" class="form-control">
                    <option value="">-- Not assigned --</option>
                    <?php while($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>">
                            <?php echo $student['student_id'] . ' - ' . $student['student_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Add User</button>
            <a href="admin.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        $('#role-select').change(function() {
            if ($(this).val() === 'student') {
                $('#student-assignment').show();
            } else {
                $('#student-assignment').hide();
            }
        });
    });
</script>

</body>
</html>