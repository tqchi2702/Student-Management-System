<?php
include 'auth.php';
include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List</title>

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
        .btn-view {
            background-color: #17a2b8;
            color: white;
        }
        .btn-edit {
            background-color: #ffc107;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
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

    <h1>Student List</h1>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <form method="GET" action="view.php" class="form-inline">
                <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search by name or ID..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary mb-2" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="register.php" class="btn btn-add mb-2"><i class="fas fa-plus"></i> Add Student</a>
        </div>

        <?php
        include 'auth.php';
        $servername = "localhost:3307";
        $username = "root";
        $pass = "";
        $dbname = "dbstudent";

        $mysql = new mysqli($servername, $username, $pass, $dbname);

        if ($mysql->connect_error) {
            echo "<div class='alert alert-danger'>Connection Failed: " . $mysql->connect_error . "</div>";
        } else {
            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
            if ($searchTerm) {
                $sql = "SELECT * FROM student WHERE student_name LIKE '%$searchTerm%' OR student_id LIKE '%$searchTerm%'";
            } else {
                $sql = "SELECT * FROM student";
            }

            $result = $mysql->query($sql);

            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo "<thead class='thead-dark'><tr> 
                    <th>Student ID</th> 
                    <th>Name</th>  
                    <th>Major</th> 
                    <th>Date of Birth</th>  
                    <th>Address</th> 
                    <th>Email</th> 
                    <th>Phone Number</th> 
                    <th>Actions</th>
                  </tr></thead><tbody>";

            while($row = $result->fetch_assoc()) {
                $id = htmlspecialchars($row['student_id']);
                echo "<tr>";
                echo "<td>" . $id . "</td>";
                echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['major']) . "</td>";
                echo "<td>" . htmlspecialchars($row['dob']) . "</td>";
                echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                echo "<td class='text-center'>
                    <a href='view_student.php?id=$id' class='btn btn-sm btn-view mb-1'><i class='fas fa-eye'></i></a>
                    <a href='edit_student.php?id=$id' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>
                    <a href='delete_student.php?id=$id' class='btn btn-sm btn-delete mb-1' onclick=\"return confirm('Are you sure you want to delete this student?');\"><i class='fas fa-trash'></i></a>
                </td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
            echo '</div>';
            $mysql->close();
        }
        ?>
    </div>

</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
