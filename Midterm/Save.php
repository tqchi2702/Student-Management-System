<!-- 

<!-- Code mới -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
</head>
<body>
    <?php
    if (isset($_POST['Register'])) {
        // Connection Info
        $servername = "localhost:3307";
        $username = "root";
        $pass = "";
        $dbname = "dbstudent";

        // Collect & sanitize form data
        $student_id = $_POST['student_id'];
        $student_name = $_POST['student_name'];
        $major = $_POST['major'];
        $dob = $_POST['dob'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone_number'];

        // Create connection
        $mysql = new mysqli($servername, $username, $pass, $dbname);

        if ($mysql->connect_error) {
            echo "Connection Failed: " . $mysql->connect_error;
        } else {
            // Safe SQL using prepared statement 🛡️
            $stmt = $mysql->prepare("INSERT INTO student (student_id, student_name, major, dob, address, email, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $student_id, $student_name, $major, $dob, $address, $email, $phone_number);

            if ($stmt->execute()) {
                echo "✅ Add success... <a href='view.php'>View Students</a>";
            } else {
                echo "❌ Error: " . $stmt->error;
            }

            $stmt->close();
            $mysql->close();
        }
    }
    ?>
</body>
</html> -->
