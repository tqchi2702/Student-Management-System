<?php
include 'auth.php';
include 'db.php';

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

// Xử lý hủy gán tài khoản
if (isset($_GET['unassign'])) {
    $user_id = $_GET['unassign'];
    
    $stmt = $conn->prepare("UPDATE users SET student_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Account unassigned successfully!";
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

    <h1>User Management</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <a href="newuser.php" class="btn btn-add mb-2"><i class="fas fa-plus"></i> Add New User</a>
        </div>

        <?php
        $users = $conn->query("SELECT u.*, s.student_name FROM users u LEFT JOIN student s ON u.student_id = s.student_id");
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
            
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['role']}</td>
                    <td>{$student_info}</td>
                    <td class='text-center'>";
            
            if ($row['role'] == 'student') {
                if ($row['student_id']) {
                    echo "<a href='admin.php?unassign={$row['id']}' class='btn btn-sm btn-unassign mb-1' onclick=\"return confirm('Are you sure you want to unassign this account?');\"><i class='fas fa-unlink'></i></a>";
                } else {
                    echo "<a href='#assignModal{$row['id']}' class='btn btn-sm btn-assign mb-1' data-toggle='modal'><i class='fas fa-link'></i></a>";
                    
                    // Modal for assignment
                    echo "<div class='modal fade' id='assignModal{$row['id']}' tabindex='-1' role='dialog'>
                            <div class='modal-dialog' role='document'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title'>Assign Student to {$row['username']}</h5>
                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>
                                    <form method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='user_id' value='{$row['id']}'>
                                            <div class='form-group'>
                                                <label>Select Student</label>
                                                <select name='student_id' class='form-control' required>";
                    
                    $free_students_result = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL)");
                    while($student = $free_students_result->fetch_assoc()) {
                        echo "<option value='{$student['student_id']}'>{$student['student_id']} - {$student['student_name']}</option>";
                    }
                    
                    echo "</select>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                                            <button type='submit' name='assign' class='btn btn-primary'>Assign</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                }
            }
            
            echo "<a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>
                  <a href='delete_user.php?id={$row['id']}' class='btn btn-sm btn-delete mb-1' onclick=\"return confirm('Are you sure you want to delete this user?');\"><i class='fas fa-trash'></i></a>
                  </td>
                </tr>";
        }

        echo "</tbody></table>";
        echo '</div>';
        ?>

        <!-- Assignment Form for Bulk Assignment -->
        <!-- <div class="assign-form">
            <h4>Bulk Assign Accounts to Students</h4>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Available Student Accounts</label>
                        <select multiple class="form-control" size="5">
                            <?php 
                            while($user = $free_users->fetch_assoc()) {
                                echo "<option>{$user['username']} (ID: {$user['id']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x"></i>
                    </div>
                    <div class="form-group col-md-5">
                        <label>Students Without Accounts</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">-- Select Student --</option>
                            <?php 
                            $free_students_result = $conn->query("SELECT * FROM student WHERE student_id NOT IN (SELECT student_id FROM users WHERE student_id IS NOT NULL)");
                            while($student = $free_students_result->fetch_assoc()) {
                                echo "<option value='{$student['student_id']}'>{$student['student_id']} - {$student['student_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <button type="submit" name="assign" class="btn btn-primary">Assign Selected</button>
                    </div>
                </div>
            </form>
        </div> -->
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>