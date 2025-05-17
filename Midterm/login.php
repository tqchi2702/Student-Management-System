<?php
session_start();
include 'db.php';

// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. PHẦN KHAI BÁO ẢNH - BẠN CÓ THỂ THAY ĐỔI CÁC ĐƯỜNG DẪN ẢNH TẠI ĐÂY
$background_image = 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'; // Ảnh nền bên trái
$logo_image = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSAuiyN0qW4c8HFCUTnlWbjw3HlVOXdesNHLg&s'; // Logo trên form login

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ username và password";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role, student_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                if ($password === $user['password']) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $hashed, $user['id']);
                    $update_stmt->execute();
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'student' && !empty($user['student_id'])) {
                    $_SESSION['student_id'] = $user['student_id'];
                }
                
                header("Location: " . ($user['role'] == 'admin' ? 'home.php' : 'home.php'));
                exit();
            } else {
                $error = "Sai username hoặc password";
            }
        } else {
            $error = "Sai username hoặc password";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            display: flex;
            height: 100%;
        }
        
        .login-image {
            flex: 1;
            background-image: url('<?php echo $background_image; ?>');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
        }
        
        .login-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .image-content {
            z-index: 1;
            text-align: center;
            padding: 20px;
            max-width: 80%;
        }
        
        .image-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .image-content p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .login-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f8f9fa;
        }
        
        .form-container {
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo img {
            max-height: 60px;
            max-width: 100%;
        }
        
        .form-card {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #343a40;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            font-weight: 600;
            background-color: #0d6efd;
            border: none;
        }
        
        .btn-login:hover {
            background-color: #0b5ed7;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-image {
                flex: none;
                height: 200px;
            }
            
            .login-form {
                flex: 1;
                padding: 1.5rem;
            }
            
            .image-content h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Phần ảnh bên trái -->
        <div class="login-image">
            <div class="image-content">
                <h2>Hệ thống Quản lý Sinh viên</h2>
                <p>Nền tảng quản lý thông tin sinh viên toàn diện và hiệu quả</p>
            </div>
        </div>
        
        <!-- Phần form đăng nhập bên phải -->
        <div class="login-form">
            <div class="form-container">
                <div class="form-card">
                    <div class="logo">
                        <img src="<?php echo $logo_image; ?>" alt="Logo">
                    </div>
                    
                    <h3 class="form-title">Đăng nhập tài khoản</h3>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                            <label for="username"><i class="bi bi-person-fill"></i> Tên đăng nhập</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock-fill"></i> Mật khẩu</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </button>
                        
                        <div class="forgot-password mt-3">
                            <a href="forgot_password.php" class="text-decoration-none">Quên mật khẩu?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>