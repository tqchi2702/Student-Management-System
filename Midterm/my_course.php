<?php
include 'auth.php';
include 'db.php';

// Only students can access
if ($_SESSION['role'] !== 'student') {
    header("Location: home.php");
    exit;
}

// Get student ID from user session
$username = $_SESSION['username'];
$student = $conn->query("SELECT student_id FROM users WHERE username = '$username'")->fetch_assoc();

if (!$student || !$student['student_id']) {
    $_SESSION['error'] = "Student profile not found";
    header("Location: profile.php");
    exit;
}

$student_id = $student['student_id'];

// Get enrolled courses
$courses_sql = "SELECT c.course_id, c.course_name, c.credits, c.lecturer, c.schedule, sc.grade 
               FROM student_courses sc
               JOIN courses c ON sc.course_id = c.course_id
               WHERE sc.student_id = '$student_id'
               ORDER BY c.course_name";
$courses_result = $conn->query($courses_sql);
$enrolled_courses = $courses_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #2e59d9;
            --background-color: #f8f9fc;
            --card-shadow: 0 0 15px rgba(0,0,0,0.1);
            --border-radius: 0.35rem;
        }
        
        body {
            background-color: var(--background-color);
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            box-shadow: var(--card-shadow);
            border-radius: var(--border-radius);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }
        
        .detail-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            width: 200px;
            color: #5a5c69;
        }
        
        .account-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .account-active {
            background-color: #1cc88a;
            color: white;
        }
        
        .account-inactive {
            background-color: #e74a3b;
            color: white;
        }
        .course-table {
            width: 100%;
            margin-top: 20px;
        }
        
        .course-table th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
        }
        
        .course-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .grade-cell {
            font-weight: bold;
            color: #2e59d9;
        }
        
        .no-courses {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        
        .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #4e73df;
            font-weight: 600;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 5px;
        }
        .schedule-cell {
            font-family: monospace;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3><i class="fas fa-book-open"></i> My Courses</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($enrolled_courses)): ?>
                <div class="table-responsive">
                    <table class="course-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                                <!-- <th>Lecturer</th>
                                <th>Schedule</th>
                                <th>Grade</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrolled_courses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['course_id']) ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= htmlspecialchars($course['credits']) ?></td>
                                <!-- <td><?= htmlspecialchars($course['lecturer']) ?></td>
                                <td class="schedule-cell"><?= htmlspecialchars($course['schedule']) ?></td>
                                <td class="grade-cell">
                                    <?= $course['grade'] ? htmlspecialchars($course['grade']) : 'Not graded' ?>
                                </td> -->
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-book-open fa-2x mb-3"></i>
                    <h4>You are not enrolled in any courses yet</h4>
                    <p class="text-muted">Please contact your department for course registration</p>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>