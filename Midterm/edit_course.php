<?php
include 'auth.php';
include 'db.php';

if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: home.php");
    exit;
}

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    $_SESSION['error'] = "Không tìm thấy khóa học";
    header("Location: course.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->bind_param("s", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    $_SESSION['error'] = "Khóa học không tồn tại";
    header("Location: course.php");
    exit;
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $lecturer = $_POST['lecturer'];
    $schedule = $_POST['schedule'];
    
    // Sửa lại câu lệnh SQL và bind_param
    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, credits = ?, lecturer = ?, schedule = ? WHERE course_id = ?");
    if ($stmt) {
        $stmt->bind_param("sisss", $course_name, $credits, $lecturer, $schedule, $course_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Cập nhật khóa học thành công!";
            header("Location: course.php");
            exit;
        } else {
            $_SESSION['error'] = "Lỗi khi cập nhật: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "Lỗi chuẩn bị câu lệnh: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Course</title>
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
        .content {
            margin-left: 240px;
            padding: 40px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .form-group label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-edit"></i> Edit Course: <?= htmlspecialchars($course['course_id']) ?></h3>
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Course ID</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($course['course_id']) ?>" readonly>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Course Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="course_name" class="form-control" 
                               value="<?= htmlspecialchars($course['course_name']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Credits</label>
                    <div class="col-sm-10">
                        <input type="number" name="credits" class="form-control" min="1" 
                               value="<?= htmlspecialchars($course['credits']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Lecturer</label>
                    <div class="col-sm-10">
                        <input type="text" name="lecturer" class="form-control" 
                               value="<?= htmlspecialchars($course['lecturer']) ?>">
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Schedule</label>
                    <div class="col-sm-10">
                        <input type="text" name="schedule" class="form-control" 
                               value="<?= htmlspecialchars($course['schedule']) ?>" 
                               placeholder="E.g: T2-4, 7:30-9:30">
                    </div>
                </div>
                
                <div class="form-group row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" name="update_course" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Course
                        </button>
                        <a href="course.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>