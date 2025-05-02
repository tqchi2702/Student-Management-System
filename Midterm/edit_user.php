<?php
include 'auth.php';
include 'db.php';

$id = $_GET['id'];
$sql = "SELECT u.*, s.student_name FROM users u LEFT JOIN student s ON u.student_id = s.student_id WHERE u.id = $id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $role = $_POST['role'];
    $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;

    // Fixed the SQL query construction
    $update = "UPDATE users SET username='$username', password='$password', role='$role'";
    if ($role == 'student') {
        $update .= ", student_id=" . ($student_id ? "'$student_id'" : "NULL");
    } else {
        $update .= ", student_id=NULL";
    }
    $update .= " WHERE id=$id";
    
    if ($conn->query($update)) {
        $_SESSION['message'] = "User updated successfully";
        header("Location: admin.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Get available students (not assigned to other users)
$students = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL AND id != $id)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    
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
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        #student-assignment {
            display: <?php echo ($user['role'] == 'student') ? 'block' : 'none'; ?>;
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
    <h2>Edit User</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required id="role-select">
                    <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="student" <?php echo ($user['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                </select>
            </div>
            
            <div class="form-group" id="student-assignment">
                <label>Assign to Student</label>
                <select name="student_id" class="form-control">
                    <option value="">-- Not assigned --</option>
                    <?php while($student = $students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>" 
                            <?php echo ($user['student_id'] == $student['student_id']) ? 'selected' : ''; ?>>
                            <?php echo $student['student_id'] . ' - ' . $student['student_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if ($user['student_id']): ?>
                    <small class="text-muted">Currently assigned to: <?php echo $user['student_id'] . ' - ' . $user['student_name']; ?></small>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Update User</button>
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