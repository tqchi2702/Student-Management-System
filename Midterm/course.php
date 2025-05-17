<?php
include 'auth.php';
include 'db.php';

// Chỉ admin mới được truy cập
if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: home.php");
    exit;
}

// Hàm tự động generate mã khóa học
function generateCourseCode($conn) {
    $result = $conn->query("SELECT MAX(course_id) as max_code FROM courses WHERE course_id LIKE 'C%'");
    $row = $result->fetch_assoc();
    
    if ($row['max_code']) {
        $last_num = (int) substr($row['max_code'], 1);
        $new_num = $last_num + 1;
    } else {
        $new_num = 1;
    }
    
    return 'C' . str_pad($new_num, 5, '0', STR_PAD_LEFT);
}

// Xử lý thêm khóa học mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $lecturer = $_POST['lecturer'];
    $schedule = $_POST['schedule'];
    
    $stmt = $conn->prepare("INSERT INTO courses (course_id, course_name, credits, lecturer, schedule) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $course_id, $course_name, $credits, $lecturer, $schedule);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Thêm khóa học thành công!";
        header("Location: course.php");
        exit;
    } else {
        $_SESSION['error'] = "Lỗi khi thêm khóa học: " . $conn->error;
    }
}

// Xử lý xóa khóa học
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    
    // Kiểm tra xem có sinh viên đăng ký chưa
    $check = $conn->query("SELECT * FROM student_courses WHERE course_id = '$course_id'");
    
    if ($check->num_rows == 0) {
        $conn->query("DELETE FROM courses WHERE course_id = '$course_id'");
        $_SESSION['message'] = "Đã xóa khóa học!";
    } else {
        $_SESSION['error'] = "Không thể xóa - Đã có sinh viên đăng ký khóa học này!";
    }
    header("Location: course.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Management</title>
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
        .form-inline {
            display: flex;
            align-items: center;
        }
    </style>
</head>

<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">

    <h1>Course Management</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <form method="GET" action="course.php" class="form-inline">
                <input type="text" name="search" class="form-control mr-2 mb-2" placeholder="Search by course name or ID..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary mb-2" type="submit"><i class="fas fa-search"></i></button>
            </form>
            <a href="#" class="btn btn-add mb-2" data-toggle="modal" data-target="#addCourseModal">
                <i class="fas fa-plus"></i> Add Course
            </a>
        </div>

        <!-- Modal Thêm Khóa học -->
        <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Course</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Course ID</label>
                                <input type="text" name="course_id" class="form-control" 
                                       value="<?= generateCourseCode($conn) ?>" readonly required>
                            </div>
                            <div class="form-group">
                                <label>Course Name</label>
                                <input type="text" name="course_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Credits</label>
                                <input type="number" name="credits" class="form-control" min="1" required>
                            </div>
                            <!-- <div class="form-group">
                                <label>Lecturer</label>
                                <input type="text" name="lecturer" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Schedule</label>
                                <input type="text" name="schedule" class="form-control" placeholder="E.g: T2-4, 7:30-9:30">
                            </div> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_course" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php
        $searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
        
        if ($searchTerm) {
            $sql = "SELECT * FROM courses 
                    WHERE course_name LIKE '%$searchTerm%' 
                    OR course_id LIKE '%$searchTerm%'";
        } else {
            $sql = "SELECT * FROM courses";
        }

        $result = $conn->query($sql);

        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-hover">';
        echo "<thead class='thead-dark'><tr>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Actions</th>
              </tr></thead><tbody>";

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['credits']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['lecturer']) . "</td>";
                // echo "<td>" . htmlspecialchars($row['schedule']) . "</td>";
                echo "<td class='text-center'>
                    <a href='edit_course.php?id=".$row['course_id']."' class='btn btn-sm btn-edit mb-1'><i class='fas fa-edit'></i></a>
                    <a href='course.php?delete=".$row['course_id']."' class='btn btn-sm btn-delete mb-1' onclick=\"return confirm('Are you sure you want to delete this course?');\"><i class='fas fa-trash'></i></a>
                    <a href='student_course.php?course_id=".$row['course_id']."' class='btn btn-sm btn-view mb-1'><i class='fas fa-users'></i></a>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center'>No courses found</td></tr>";
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