<?php
include 'auth.php';
include 'db.php';

// Check if current user is superadmin or admin
$current_user_role = $_SESSION['role'];
$current_user_id = $_SESSION['user_id']; // To check if editing own account

// Xử lý gán tài khoản cho student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign'])) {
    $student_id = $_POST['student_id'];
    $user_id = $_POST['user_id'];
    
    $stmt = $conn->prepare("UPDATE users SET student_id = ? WHERE id = ?");
    $stmt->bind_param("si", $student_id, $user_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Account assigned successfully!";
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fc;
        }
        /* Sidebar/Navbar */
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
        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .btn-add {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
            white-space: nowrap;
        }
        .btn-edit {
            background-color: #ffc107;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-assign {
            background-color: #17a2b8;
            color: white;
        }
        .btn-unassign {
            background-color: #6c757d;
            color: white;
        }
        .assign-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .role-admin {
            background-color: #e9f7ef;
        }
        .role-superadmin {
            background-color: #f0e9f7;
        }
    </style>
</head>

<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">

    <h1>User Management</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card">
        <?php if ($current_user_role == 'superadmin' || $current_user_role == 'admin'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <a href="newuser.php" class="btn btn-add mb-2"><i class="fas fa-plus"></i> Add New User</a>
            </div>
        <?php endif; ?>

        <?php
        $users = $conn->query("SELECT u.*, s.student_name FROM users u LEFT JOIN student s ON u.student_id = s.student_id ORDER BY role, username");
        $free_students = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL)");
        $free_users = $conn->query("SELECT * FROM users WHERE student_id IS NULL AND role = 'student'");
        
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-hover">';
        echo "<thead class='thead-dark'><tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Assigned Student</th>
                <th>Actions</th>
              </tr></thead><tbody>";

        while ($row = $users->fetch_assoc()) {
            $student_info = $row['student_id'] ? "{$row['student_id']} - {$row['student_name']}" : 'Not assigned';
            $row_class = '';
            if ($row['role'] == 'admin') $row_class = 'role-admin';
            if ($row['role'] == 'superadmin') $row_class = 'role-superadmin';
            
            echo "<tr class='$row_class'>
                    <td>{$row['id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['role']}</td>
                    <td>{$student_info}</td>
                    <td class='text-center'>";

            // Edit and Delete buttons (with permission checks)
            if ($current_user_role == 'superadmin') {
                // Superadmin can edit anyone
                echo "<a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>";
                
                // Superadmin can delete anyone except themselves
                if ($row['id'] != $current_user_id) {
                    echo "<a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-delete mb-1' onclick=\"return confirm('Are you sure you want to delete this user?');\"><i class='fas fa-trash'></i></a>";
                } else {
                    // Disabled button for own account
                    echo "<button class='btn btn-sm btn-delete mb-1' disabled title='Cannot delete your own account'><i class='fas fa-trash'></i></button>";
                }
            } elseif ($current_user_role == 'admin') {
                // Admin can only edit/delete student and department accounts
                if (in_array($row['role'], ['student', 'department'])) {
                    echo "<a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>
                          <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-delete mb-1' onclick=\"return confirm('Are you sure you want to delete this user?');\"><i class='fas fa-trash'></i></a>";
                }
                // Admin can edit their own account (but not delete it)
                elseif ($row['id'] == $current_user_id && $row['role'] == 'admin') {
                    echo "<a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>";
                }
            }
            
            echo "</td>
                </tr>";
        }

        echo "</tbody></table>";
        echo '</div>';
        ?>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>