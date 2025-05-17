<?php
include("db.php");
include("auth.php");
$result = mysqli_query($conn, "SELECT * FROM d");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách khoa</title>
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
        h2 { text-align: center; color: #343a40; margin-bottom: 30px; }
        .card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 20px; }
        .btn-primary { background-color: #28a745; border-color: #28a745; }
        .table { margin-top: 20px; }
        .table th { background-color: #343a40; color: white; }
        .action-links a { margin: 0 5px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <div class="card">
        <h2>Department List</h2>
        
        <a href="department.php" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Add New Department
        </a>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['dep_id'] ?></td>
                        <td><?= $row['dep_name'] ?></td>
                        <td><?= $row['dep_email'] ?></td>
                        <td><?= $row['location'] ?></td>
                        <td>
                            <span class="badge badge-<?= $row['status'] == 'active' ? 'success' : 'secondary' ?>">
                                <?= $row['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động' ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="view_department.php?id=<?= $row['dep_id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="edit_department.php?id=<?= $row['dep_id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_department.php?id=<?= $row['dep_id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa khoa này?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>