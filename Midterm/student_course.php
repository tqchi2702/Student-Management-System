<?php
include 'auth.php';
include 'db.php';

// Only admin can access
if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: home.php");
    exit;
}

$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    $_SESSION['error'] = "Course not specified";
    header("Location: course.php");
    exit;
}

// Get course info
$course = $conn->query("SELECT * FROM courses WHERE course_id = '$course_id'")->fetch_assoc();
if (!$course) {
    $_SESSION['error'] = "Course not found";
    header("Location: course.php");
    exit;
}


// Handle add student
if (isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    
    // Kiểm tra sinh viên đã tồn tại chưa
    $student_exists = $conn->query("SELECT 1 FROM student WHERE student_id = '$student_id'");
    if (!$student_exists->num_rows) {
        $_SESSION['error'] = "Student does not exist";
        header("Location: student_course.php?course_id=$course_id");
        exit;
    }

    // Kiểm tra đã đăng ký chưa
    $already_enrolled = $conn->query("SELECT 1 FROM student_courses 
                                    WHERE student_id = '$student_id' 
                                    AND course_id = '$course_id'");
    
    if ($already_enrolled->num_rows) {
        $_SESSION['error'] = "Student already enrolled in this course";
        header("Location: student_course.php?course_id=$course_id");
        exit;
    }

    // Thêm vào khóa học (ID sẽ tự động được generate)
    $stmt = $conn->prepare("INSERT INTO student_courses (student_id, course_id, enrollment_date) 
                          VALUES (?, ?, CURDATE())");
    $stmt->bind_param("ss", $student_id, $course_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Student added to course successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    
    header("Location: student_course.php?course_id=$course_id");
    exit;
}

// Handle remove student
if (isset($_GET['remove'])) {
    $student_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM student_courses 
                          WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ss", $student_id, $course_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Student removed from course!";
    } else {
        $_SESSION['error'] = "Error removing student: " . $stmt->error;
    }
    header("Location: student_course.php?course_id=$course_id");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - <?= htmlspecialchars($course['course_name']) ?></title>
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
        .content {
            margin-left: 240px;
            padding: 40px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .btn-action {
            margin: 0 2px;
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
            <h3>
                <i class="fas fa-users"></i> Manage Students - <?= htmlspecialchars($course['course_name']) ?>
                <span class="badge badge-light"><?= htmlspecialchars($course['course_id']) ?></span>
            </h3>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="card-body">
            <!-- Form to add student -->
            <form method="POST" class="mb-4">
                <div class="form-row align-items-center">
                    <div class="col-md-8">
                        <label class="sr-only" for="student_id">Student</label>
                        <select name="student_id" class="form-control" required>
                            <option value="">Select Student...</option>
                            <?php
                            // Get students not enrolled in this course
                            $students = $conn->query("
                                SELECT s.* FROM student s
                                WHERE s.student_id NOT IN (
                                    SELECT sc.student_id FROM student_courses sc
                                    WHERE sc.course_id = '$course_id'
                                )
                                ORDER BY s.student_name
                            ");
                            while ($student = $students->fetch_assoc()):
                            ?>
                            <option value="<?= $student['student_id'] ?>">
                                <?= htmlspecialchars($student['student_id']) ?> - <?= htmlspecialchars($student['student_name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="add_student" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> Add Student
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Enrolled students list -->
            <h4>Enrolled Students</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Enrollment Date</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $enrolled = $conn->query("
                            SELECT s.student_id, s.student_name, sc.enrollment_date, sc.grade
                            FROM student_courses sc
                            JOIN student s ON sc.student_id = s.student_id
                            WHERE sc.course_id = '$course_id'
                            ORDER BY s.student_name
                        ");
                        
                        if ($enrolled->num_rows > 0):
                            while ($student = $enrolled->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                            <td><?= $student['enrollment_date'] ?></td>
                            <td>
                                <?= $student['grade'] ? htmlspecialchars($student['grade']) : 'Not graded' ?>
                            </td>
                            <td>
                                <a href="student_course.php?course_id=<?= $course_id ?>&remove=<?= $student['student_id'] ?>" 
                                   class="btn btn-danger btn-action"
                                   onclick="return confirm('Remove this student from course?')">
                                    <i class="fas fa-user-minus"></i> Remove
                                </a>
                                <a href="grade.php?course_id=<?= $course_id ?>&student_id=<?= $student['student_id'] ?>" 
                                   class="btn btn-warning btn-action">
                                    <i class="fas fa-edit"></i> Grade
                                </a>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No students enrolled yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <a href="course.php" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>