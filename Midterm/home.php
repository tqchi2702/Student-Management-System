<?php
include 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XYZ University Portal</title>

    <!-- Bootstrap + FontAwesome -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fc;
        }
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
            font-weight: bold;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .logout {
            color: #ff4c4c;
        }
        .content {
            margin-left: 240px;
            padding: 40px;
        }
        .news-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .news-title {
            font-weight: bold;
            font-size: 20px;
            margin-top: 10px;
        }
        .announcement-item {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .announcement-item:last-child {
            border-bottom: none;
        }
        .new-badge {
            background-color: red;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 5px;
            margin-left: 5px;
        }
    </style>
</head>

<body>

<!-- Sidebar -->
 <?php include 'sidebar.php'; ?>
<!-- Content -->
<div class="content">
    <div class="container-fluid">

        <!-- Introduction -->
        <div class="mb-5">
            <h2 class="text-gray-800 mb-3">WELCOME TO INTERNATIONAL SCHOOL </h2>
            <img src="https://www.is.vnu.edu.vn/wp-content/uploads/2022/04/ghep-anh.png" alt="International School" style="max-width: 1000px; width: 500%; height: auto; margin-bottom: 20px; border-radius: 8px;">
            <p class="lead">
                XYZ University is one of the leading institutions, offering quality education, groundbreaking research, and a vibrant campus life. 
                Our mission is to nurture talents and contribute to the advancement of knowledge and society.
            </p>
        </div>

        <div class="row">

            <!-- News Section -->
            <div class="col-md-8">
                <h3 class="mb-4">Latest News</h3>

                <img src="https://www.educationnext.org/wp-content/uploads/2022/09/ednext_XXIII_1_forum_img01.png" alt="Featured News" class="news-image">

                <div class="news-title">Professors from XYZ University participate in National Economic Forum 2025</div>
                <div class="text-muted mb-4">22/04/2025 15:37</div>

                <ul class="list-unstyled">
                    <li class="mb-3">
                        <a href="#" class="text-dark">📜 Reading culture promotion activities for students</a> 
                        <div class="text-muted" style="font-size: 12px;">22/04/2025 15:39</div>
                    </li>
                    <li class="mb-3">
                        <a href="#" class="text-dark">📜 Seminar: Human resource management in digital economy</a> 
                        <div class="text-muted" style="font-size: 12px;">22/04/2025 15:37</div>
                    </li>
                    <li class="mb-3">
                        <a href="#" class="text-dark">📜 Successful organizational change strategies</a> 
                        <div class="text-muted" style="font-size: 12px;">19/04/2025 21:09</div>
                    </li>
                </ul>
            </div>

            <!-- Announcements Section -->
            <div class="col-md-4">
                <h3 class="mb-4">Announcements</h3>
                <div class="card p-3">

                    <div class="announcement-item">
                        <i class="fas fa-arrow-right text-primary"></i> Free English courses for high school students 
                        <span class="new-badge">NEW</span>
                        <div class="text-muted" style="font-size: 12px;">15/04/2025 10:36</div>
                    </div>

                    <div class="announcement-item">
                        <i class="fas fa-arrow-right text-primary"></i> National Reading Day activities 
                        <span class="new-badge">NEW</span>
                        <div class="text-muted" style="font-size: 12px;">13/04/2025 22:46</div>
                    </div>

                    <div class="announcement-item">
                        <i class="fas fa-arrow-right text-primary"></i> Graduate admission notice 2025 
                        <span class="new-badge">NEW</span>
                        <div class="text-muted" style="font-size: 12px;">13/04/2025 16:18</div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
