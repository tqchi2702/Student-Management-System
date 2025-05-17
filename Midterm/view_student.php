<?php
include 'auth.php';
include 'db.php';

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No student ID provided!";
    header("Location: view.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

$sql = "SELECT s.*, d.dep_name, u.username as account_username 
        FROM student s 
        LEFT JOIN d ON s.dep_id = d.dep_id
        LEFT JOIN users u ON s.student_id = u.student_id 
        WHERE s.student_id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    $_SESSION['error'] = "Student not found!";
    header("Location: view.php");
    exit();
}

$student = $result->fetch_assoc();

// Get profile picture path
$imgimg = null;
$image_dir = "image/";
$image_pattern = $image_dir . $student['student_id'] . "_*.*";
$images = glob($image_pattern);
if (!empty($images)) {
    $img = $images[0]; // Get the first matching image
}

// Get student's enrolled courses
$courses_sql = "SELECT c.course_id, c.course_name, c.credits, c.lecturer, c.schedule, sc.grade 
               FROM student_courses sc
               JOIN courses c ON sc.course_id = c.course_id
               WHERE sc.student_id = '$id'
               ORDER BY c.course_name";
$courses_result = $conn->query($courses_sql);
$enrolled_courses = [];
if ($courses_result) {
    $enrolled_courses = $courses_result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Details</title>
    
    <!-- Bootstrap + FontAwesome -->
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
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4e73df;
            margin-bottom: 20px;
        }
        
        .profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-info {
            margin-left: 30px;
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
            
            .profile-section {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-info {
                margin-left: 0;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Student Details</h5>
        </div>
        <div class="card-body">
            <!-- Profile Picture Section -->
            <!-- <div class="profile-section">
                <?php if ($img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Profile Picture" class="img">
                <?php else: ?>
                    <div class="profile-picture" style="background-color: #ddd; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user" style="font-size: 60px; color: #777;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($student['student_name']); ?></h3>
                    <h5 class="text-muted"><?php echo htmlspecialchars($student['student_id']); ?></h5>
                    <?php if ($student['dep_name']): ?>
                        <p class="text-primary"><?php echo htmlspecialchars($student['dep_name']); ?></p>
                    <?php endif; ?>
                </div>
            </div> -->
            
            <!-- Student Details -->
            <div class="detail-row">
                <div class="detail-label">Student ID</div>
                <div><?php echo htmlspecialchars($student['student_id']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Full Name</div>
                <div><?php echo htmlspecialchars($student['student_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Email</div>
                <div><?php echo htmlspecialchars($student['email']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Phone Number</div>
                <div><?php echo htmlspecialchars($student['phone_number']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Address</div>
                <div><?php echo htmlspecialchars($student['address']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Department</div>
                <div>
                    <?php if ($student['dep_name']): ?>
                        <?php echo htmlspecialchars($student['dep_name']); ?>
                        (<?php echo htmlspecialchars($student['dep_id']); ?>)
                    <?php else: ?>
                        Not assigned
                    <?php endif; ?>
                </div>
            </div>
    
            
            <div class="detail-row">
                <div class="detail-label">Date of Birth</div>
                <div><?php echo htmlspecialchars($student['dob']); ?></div>
            </div>

            <!-- <div class="student-image">
                <?php
                if ($student['img'] && file_exists($student['img'])) {
                    echo "<img src='" . htmlspecialchars($student['img']) . "' alt='Student Image'>";
                } else {
                    echo "<img src='images/default_avatar.png' alt='Default Avatar'>";
                }
                ?>
            </div> -->
            
            <div class="d-flex justify-content-end mt-4">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="edit_student.php?id=<?php echo htmlspecialchars($student['student_id']); ?>" class="btn btn-primary mr-2">
                        <i class="fas fa-edit"></i> Edit Student
                    </a>
                    <a href="view.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                <?php elseif ($_SESSION['role'] === 'department'): ?>
                    <a href="view_department.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to My Department
                    </a>
                <?php endif; ?>
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
                                <th>Schedule</th> -->
                                <!-- <th>Grade</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrolled_courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_id']) ?></td>
                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                    <td><?= htmlspecialchars($course['credits']) ?></td>
                                    <!-- <td><?= htmlspecialchars($course['lecturer']) ?></td>
                                    <<td class="schedule-cell"><?= htmlspecialchars($course['schedule']) ?></td>
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
                    <i class="fas fa-book-open"></i> This student is not enrolled in any courses yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>