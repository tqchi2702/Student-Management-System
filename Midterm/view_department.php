<?php
include("db.php");
include("auth.php");

// Kiểm tra quyền truy cập
if ($_SESSION['role'] == 'department') {
    // Nếu là tài khoản khoa, chỉ được xem thông tin khoa của mình
    $dep_id = $_SESSION['username'];
} else {
    // Nếu là admin, lấy department ID từ URL
    $dep_id = $_GET['id'] ?? '';
    
    // Nếu không có ID và không phải admin, chuyển hướng
    if (empty($dep_id) && $_SESSION['role'] != 'admin') {
        header("Location: home.php");
        exit();
    }
}

// Escape the department ID to prevent SQL injection
$dep_id = mysqli_real_escape_string($conn, $dep_id);

$dep_query = mysqli_query($conn, "SELECT * FROM d WHERE dep_id = '$dep_id'");
if (!$dep_query) {
    die("Database query failed: " . mysqli_error($conn));
}
$department = mysqli_fetch_assoc($dep_query);

if (!$department) {
    die("Department not found.");
}

// Kiểm tra quyền xem khoa này
if ($_SESSION['role'] == 'department' && $_SESSION['username'] != $department['dep_id']) {
    die("You don't have permission to view this department");
}

// Get students in this department
$students_query = mysqli_query($conn, "SELECT * FROM student WHERE dep_id = '$dep_id' ORDER BY student_name");
if (!$students_query) {
    die("Database query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Department Details - <?= htmlspecialchars($department['dep_name'] ?? '') ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fc; }
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
        .sidebar a:hover { background-color: #495057; }
        .logout { color: #ff4c4c; }
        .content { margin-left: 240px; padding: 40px; }
        h2, h3 { text-align: center; color: #343a40; margin-bottom: 30px; }
        .card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px; }
        .btn-primary { background-color: #28a745; border-color: #28a745; }
        .table { margin-top: 20px; }
        .table th { background-color: #343a40; color: white; }
        .action-links a { margin: 0 5px; }
        .department-info { margin-bottom: 30px; }
        .info-label { font-weight: bold; color: #495057; }
        .back-btn { margin-bottom: 20px; }
        .badge-success { background-color: #28a745; }
        .badge-secondary { background-color: #6c757d; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>


<!-- Content -->
<div class="content">
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="department_list.php" class="btn btn-secondary back-btn">
            <i class="fas fa-arrow-left"></i> Back to Department List
        </a>
    <?php endif; ?>
    
    <!-- Department Information Card -->
    <div class="card department-info">
        <h3>Thông tin Khoa</h3>
        <div class="row">
            <div class="col-md-6">
                <p><span class="info-label">Mã khoa:</span> <?= htmlspecialchars($department['dep_id']) ?></p>
                <p><span class="info-label">Tên khoa:</span> <?= htmlspecialchars($department['dep_name']) ?></p>
                <p><span class="info-label">Email:</span> <?= htmlspecialchars($department['dep_email']) ?></p>
            </div>
            <div class="col-md-6">
                <p><span class="info-label">Địa điểm:</span> <?= htmlspecialchars($department['location']) ?></p>
                <p><span class="info-label">Trạng thái:</span> 
                    <span class="badge badge-<?= $department['status'] == 'active' ? 'success' : 'secondary' ?>">
                        <?= $department['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                    </span>
                </p>
            </div>
        </div>
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'department'): ?>
            <div class="text-right">
                <a href="edit_department.php?id=<?= htmlspecialchars($department['dep_id']) ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Students List Card -->
    <div class="card">
        <h3>Sinh viên thuộc khoa</h3>
        
        <?php if (mysqli_num_rows($students_query) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Mã SV</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = mysqli_fetch_assoc($students_query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['student_name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['phone_number']) ?></td>
                            <td class="action-links">
                                <a href="view_student.php?id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="edit_student.php?id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Không có sinh viên nào trong khoa này.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>