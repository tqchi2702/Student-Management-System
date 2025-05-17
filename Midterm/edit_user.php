<?php
include 'auth.php';
include 'db.php';

if (!isset($_GET['id'])) {
    die("Missing user ID");
}

$id = intval($_GET['id']);
if ($id <= 0) {
    die("Invalid user ID");
}

$sql = "SELECT u.*, s.student_name FROM users u LEFT JOIN student s ON u.student_id = s.student_id WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found");
}

$user = $result->fetch_assoc();
$stmt->close();

$is_student = ($user['role'] == 'student');
$is_superadmin = ($user['role'] == 'superadmin');
$is_admin = ($user['role'] == 'admin');

$current_user_role = $_SESSION['role'];
$current_user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($current_user_role != 'superadmin' && ($is_superadmin || $is_admin)) {
        die("You don't have permission to edit this account");
    }

    $username = $conn->real_escape_string($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
    $role = $is_student ? 'student' : $conn->real_escape_string($_POST['role']);
    $student_id = isset($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : null;

    $is_transferring_superadmin = false;
    if ($current_user_role == 'superadmin' && $role == 'superadmin' && $id != $current_user_id) {
        $is_transferring_superadmin = true;
        $stmt = $conn->prepare("UPDATE users SET role='admin' WHERE id=?");
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $stmt->close();
    }

    $update = "UPDATE users SET username=?, password=?, role=?";
    $params = [$username, $password, $role];
    $types = "sss";

    if ($role == 'student') {
        $update .= ", student_id=?";
        $params[] = $student_id;
        $types .= "s";
    } else {
        $update .= ", student_id=NULL";
    }

    $update .= " WHERE id=?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($update);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($is_transferring_superadmin) {
            session_destroy();
            header("Location: login.php");
            exit();
        }

        $_SESSION['message'] = "User updated successfully";
        header("Location: admin.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

$students = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL AND id != $id)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; }
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 220px; height: 100%;
            background-color: #343a40;
            padding-top: 20px;
            display: flex; flex-direction: column; align-items: center;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px; width: 100%;
            text-align: left;
            box-sizing: border-box;
            font-weight: bold;
        }
        .sidebar a:hover { background-color: #495057; }
        .logout { color: #ff4c4c; }
        .content { margin-left: 240px; padding: 40px; }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
            border-radius: 10px;
        }
        .form-group { margin-bottom: 20px; }
        .view-mode {
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-secondary {
            background-color: #858796;
            border-color: #858796;
        }
        h2 {
            color: #4e73df;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <h2><i class="fas fa-user-edit mr-2"></i>Edit User</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user mr-2"></i>Username</label>
                <div class="view-mode"><?php echo htmlspecialchars($user['username']); ?></div>
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>

            <div class="form-group">
                <label><i class="fas fa-key mr-2"></i>New Password</label>
                <input type="password" name="password" class="form-control">
                <small class="form-text text-muted">Leave blank to keep the current password.</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-user-shield mr-2"></i>Role</label>
                <?php if ($is_student): ?>
                    <div class="view-mode">Student</div>
                    <input type="hidden" name="role" value="student">
                <?php elseif ($is_superadmin && $current_user_role != 'superadmin'): ?>
                    <div class="view-mode">Super Admin</div>
                    <input type="hidden" name="role" value="superadmin">
                <?php else: ?>
                    <select name="role" class="form-control" required>
                        <?php if ($current_user_role == 'superadmin'): ?>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="superadmin" <?= $user['role'] == 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                        <?php else: ?>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <?php endif; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <?php if (!($is_student || $is_superadmin) || $current_user_role == 'superadmin'): ?>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Update</button>
                <?php endif; ?>
                <a href="admin.php" class="btn btn-secondary"><i class="fas fa-times mr-2"></i>Cancel</a>
            </div>

            <?php if ($is_superadmin && $current_user_role != 'superadmin'): ?>
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i> You can't edit Super Admin accounts.
                </div>
            <?php endif; ?>

            <?php if ($current_user_role == 'superadmin' && $is_admin): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle mr-2"></i> If you promote this admin to Super Admin, your account will be demoted and you'll be logged out.
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

</body>
</html>
