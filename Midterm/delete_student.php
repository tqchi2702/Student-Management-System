<?php
include 'auth.php';
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "dbstudent";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_GET['id'];

$sql = "DELETE FROM student WHERE student_id = '$student_id'";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Student deleted successfully'); window.location.href='view.php';</script>";
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>
