<?php
include("db.php");
include("auth.php");

if(isset($_GET['id'])) {
    $dep_id = $_GET['id'];
    
    // Kiểm tra xem khoa có tồn tại không
    $check = $conn->prepare("SELECT dep_id FROM d WHERE dep_id = ?");
    $check->bind_param("s", $dep_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        echo "<script>
                alert('Khoa không tồn tại');
                window.location.href='department_list.php';
              </script>";
        exit();
    }
    
    // Sử dụng Prepared Statement để tránh SQL Injection
    $stmt = $conn->prepare("DELETE FROM d WHERE dep_id = ?");
    $stmt->bind_param("s", $dep_id);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('Khoa đã được xóa thành công');
                window.location.href='department_list.php';
              </script>";
    } else {
        echo "<script>
                alert('Lỗi khi xóa khoa: Có thể khoa đang được sử dụng bởi các bản ghi khác');
                window.location.href='department_list.php';
              </script>";
    }
    
    $stmt->close();
    $check->close();
} else {
    echo "<script>
            alert('Không có ID khoa được cung cấp');
            window.location.href='department_list.php';
          </script>";
}

$conn->close();
?>