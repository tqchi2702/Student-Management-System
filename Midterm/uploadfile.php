<?php
function handleFileUpload($student_id) {
    global $conn;

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        return;
    }

    $uploadDir = 'uploads/';
    $filename = basename($_FILES['avatar']['name']);
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $newFilename = 'student_' . $student_id . '.' . $ext;
    $targetFile = $uploadDir . $newFilename;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
        // Update DB
        $conn->query("UPDATE student SET image = '$newFilename' WHERE student_id = '$student_id'");
    }
}
?>
