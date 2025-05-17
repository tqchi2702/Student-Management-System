<?php
include 'auth.php';
include 'db.php';

// Only admin and department roles can access
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'department') {
    header("Location: home.php");
    exit;
}

// Get course ID from URL
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    $_SESSION['error'] = "Course not specified";
    header("Location: ".($_SESSION['role'] === 'department' ? 'department_courses.php' : 'course.php'));
    exit;
}

// Verify course exists and belongs to department (for department role)
$course = $conn->query("SELECT * FROM courses WHERE course_id = '$course_id'")->fetch_assoc();
if (!$course) {
    $_SESSION['error'] = "Course not found";
    header("Location: ".($_SESSION['role'] === 'department' ? 'department_courses.php' : 'course.php'));
    exit;
}

// For department users, verify they manage this course
if ($_SESSION['role'] === 'department') {
    $dept_course = $conn->query("SELECT 1 FROM department_courses 
                                WHERE course_id = '$course_id' 
                                AND dep_id = '{$_SESSION['username']}'");
    if (!$dept_course->num_rows) {
        $_SESSION['error'] = "You don't manage this course";
        header("Location: department_courses.php");
        exit;
    }
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grades'])) {
    foreach ($_POST['grades'] as $student_id => $grade) {
        $student_id = $conn->real_escape_string($student_id);
        $grade = $grade !== '' ? floatval($grade) : null;
        
        $stmt = $conn->prepare("UPDATE student_courses 
                               SET grade = ? 
                               WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("dss", $grade, $student_id, $course_id);
        $stmt->execute();
    }
    
    $_SESSION['message'] = "Grades updated successfully!";
    header("Location: grade.php?course_id=$course_id");
    exit;
}

// Get enrolled students with grades
$students = $conn->query("
    SELECT s.student_id, s.student_name, sc.grade
    FROM student_courses sc
    JOIN student s ON sc.student_id = s.student_id
    WHERE sc.course_id = '$course_id'
    ORDER BY s.student_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Grades - <?= htmlspecialchars($course['course_name']) ?></title>
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
        .content {
            margin-left: 240px;
            padding: 40px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .grade-input {
            width: 80px;
            text-align: center;
        }
        .table-responsive {
            overflow-x: auto;
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
                <i class="fas fa-graduation-cap"></i> Manage Grades - <?= htmlspecialchars($course['course_name']) ?>
                <span class="badge badge-light"><?= htmlspecialchars($course['course_id']) ?></span>
            </h3>
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students->num_rows > 0): ?>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                                    <td><?= htmlspecialchars($student['student_name']) ?></td>
                                    <td>
                                        <input type="number" name="grades[<?= htmlspecialchars($student['student_id']) ?>]" 
                                               class="form-control grade-input" min="0" max="10" step="0.1"
                                               value="<?= $student['grade'] !== null ? htmlspecialchars($student['grade']) : '' ?>">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No students enrolled in this course</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($students->num_rows > 0): ?>
                <div class="text-right mt-3">
                    <button type="submit" name="update_grades" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Grades
                    </button>
                </div>
                <?php endif; ?>
            </form>
            
            <div class="mt-4">
                <a href="<?= $_SESSION['role'] === 'department' ? 'department_courses.php' : 'course.php' ?>" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>