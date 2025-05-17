<?php
// sidebar.php - Reusable sidebar component
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-heading mb-4">
        <a href="home.php">
            <img src="https://www.is.vnu.edu.vn/wp-content/uploads/2022/04/icon_negative_yellow_text-08-539x600.png" alt="School Logo" style="width: 80px; height: auto;">
        </a>
    </div>

    <a href="home.php"><i class="fas fa-home"></i> Home</a>

    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin')): ?>
        <a href="view.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="course.php"><i class="fas fa-book"></i> Manage Courses</a>
        <a href="admin.php"><i class="fas fa-users-cog"></i> Manage Users</a>
        <a href="department_list.php"><i class="fas fa-building"></i> Manage Department</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'department'): ?>
        <a href="view_department.php?id=<?= $_SESSION['username'] ?>"><i class="fas fa-building"></i> My Department</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
        <a href="my_course.php"><i class="fas fa-book-reader"></i> My Courses</a>
        <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
    <?php endif; ?>

    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
</div>