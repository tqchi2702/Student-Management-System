<?php
session_start();
include 'db.php';

$background_image = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
$logo_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSAuiyN0qW4c8HFCUTnlWbjw3HlVOXdesNHLg&s';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);

    if (empty($username)) {
        $message = "Vui lòng nhập tên đăng nhập.";
    } else {
        $stmt = $conn->prepare("SELECT email FROM student WHERE student_id = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email = $row['email'];

            $new_password = substr(md5(rand()), 0, 8);
            $hashed_password = md5($new_password);

            $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
            $stmt_update->bind_param("ss", $hashed_password, $username);
            $stmt_update->execute();

            // Gửi email
            $subject = "Khôi phục mật khẩu";
            $message_body = "Mật khẩu mới của bạn là: $new_password";
            $headers = "From: noreply@yourdomain.com";

            if (mail($email, $subject, $message_body, $headers)) {
                $message = "Mật khẩu mới đã được gửi đến email của bạn.";
            } else {
                $message = "Gửi email thất bại. Vui lòng thử lại sau.";
            }
        } else {
            $message = "Không tìm thấy tài khoản tương ứng.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('<?php echo $background_image; ?>');
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            width: 350px;
            padding: 20px;
            margin: 80px auto;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 10px #999;
        }

        .login-container img {
            width: 80px;
            margin-bottom: 20px;
        }

        .login-container input[type="text"],
        .login-container button {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .login-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #45a049;
        }

        .message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="<?php echo $logo_image; ?>" alt="Logo">
        <h2>Quên mật khẩu</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <button type="submit">Gửi mật khẩu mới</button>
        </form>
        <?php if (!empty($message)) echo '<div class="message">' . $message . '</div>'; ?>
        <p><a href="login.php">← Quay lại đăng nhập</a></p>
    </div>
</body>
</html>
