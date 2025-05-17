<?php
include 'auth.php';

include 'db.php';

// 1. KIỂM TRA VÀ VALIDATE ID
if (!isset($_GET['id'])) {
    die("<script>alert('Missing student ID'); window.location.href='view.php';</script>");
}

$student_id = $conn->real_escape_string($_GET['id']);

// 2. BẮT ĐẦU TRANSACTION
$conn->begin_transaction();

try {
    // 3. XÓA TÀI KHOẢN USER LIÊN QUAN (NẾU CÓ)
    $delete_user_sql = "DELETE FROM users WHERE student_id = '$student_id'";
    if (!$conn->query($delete_user_sql)) {
        throw new Exception("Error deleting user account: " . $conn->error);
    }
    
    // 4. XÓA STUDENT
    $delete_student_sql = "DELETE FROM student WHERE student_id = '$student_id'";
    if (!$conn->query($delete_student_sql)) {
        throw new Exception("Error deleting student: " . $conn->error);
    }
    
    // 5. COMMIT NẾU THÀNH CÔNG
    $conn->commit();
    
    echo "<script>alert('Student and associated account deleted successfully'); window.location.href='view.php';</script>";
} catch (Exception $e) {
    // 6. ROLLBACK NẾU CÓ LỖI
    $conn->rollback();
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='view.php';</script>";
}

$conn->close();
?>