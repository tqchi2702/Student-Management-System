<?php
include 'auth.php';
include 'db.php';

// Kiểm tra login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Lấy thông tin user
$sql = "SELECT u.*, s.student_name, s.email, s.phone_number, s.address, s.dep_id, d.dep_name, s.dob
        FROM users u
        LEFT JOIN student s ON u.student_id = s.student_id
        LEFT JOIN d d ON s.dep_id = d.dep_id
        WHERE u.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $_SESSION['error'] = "User not found";
    header('Location: home.php');
    exit();
}

$user = $result->fetch_assoc();

// Get enrolled courses - using prepared statement
if ($user['student_id']) {
    $courses_sql = "SELECT c.course_id, c.course_name, c.credits, c.lecturer, c.schedule, sc.grade 
                   FROM student_courses sc
                   JOIN courses c ON sc.course_id = c.course_id
                   WHERE sc.student_id = ?
                   ORDER BY c.course_name";
    $stmt = $conn->prepare($courses_sql);
    $stmt->bind_param('s', $user['student_id']);
    $stmt->execute();
    $courses_result = $stmt->get_result();
    $enrolled_courses = $courses_result->fetch_all(MYSQLI_ASSOC);
} else {
    $enrolled_courses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fc;
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
            border-radius: 0.35rem;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 0.35rem 0.35rem 0 0;
        }
        .profile-item {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .profile-item:last-child {
            border-bottom: none;
        }
        .profile-label {
            font-weight: bold;
            color: #5a5c69;
            width: 200px;
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
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Your Profile</h5>
        </div>
        <div class="card-body">
            <div class="profile-item d-flex">
                <div class="profile-label">Student ID:</div>
                <div><?php echo htmlspecialchars($user['student_id']); ?></div>
            </div>

            <?php if ($user['student_id']): ?>
                <div class="profile-item d-flex">
                    <div class="profile-label">Full Name:</div>
                    <div><?php echo htmlspecialchars($user['student_name']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Email:</div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Phone:</div>
                    <div><?php echo htmlspecialchars($user['phone_number']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Address:</div>
                    <div><?php echo htmlspecialchars($user['address']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Major:</div>
                    <div><?php echo htmlspecialchars($user['dep_id'] . ' - ' . $user['dep_name']); ?></div>
                </div>
                <div class="profile-item d-flex">
                    <div class="profile-label">Date of Birth:</div>
                    <div><?php echo htmlspecialchars($user['dob']); ?></div>
                </div>

                <div class="mt-4 text-right">
                    <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-user-edit"></i> Edit Profile</a>
                </div>
                
                <!-- Enrolled Courses Section -->
                <h4 class="section-title">Enrolled Courses</h4>
                
                <?php if (!empty($enrolled_courses)): ?>
                    <div class="table-responsive">
                        <table class="course-table">
                            <thead>
                                <tr>
                                    <th>Course ID</th>
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
                        <i class="fas fa-book-open"></i> You are not enrolled in any courses yet.
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-warning">
                    No student profile linked to this account.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>