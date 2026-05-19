<?php
// includes/header.php
require_once __DIR__ . '/../config.php';
$currentUser = getCurrentUser();
$activePage = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fin-Course | ระบบการเรียนออนไลน์พร้อมแบบทดสอบและประเมินผล</title>
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Render Flash Toast Message if available -->
    <?php renderFlashMessage(); ?>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php?page=home" class="logo">
                <span class="logo-icon"><i class="fa-solid fa-graduation-cap"></i></span>
                Fin-Course
            </a>
            
            <ul class="nav-links">
                <li><a href="index.php?page=home" class="<?= $activePage == 'home' ? 'active' : '' ?>">รายวิชาเรียน</a></li>
                
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li><a href="index.php?page=admin_overview" class="<?= strpos($activePage, 'admin_') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-user-shield mr-1"></i> ผู้ดูแลระบบ</a></li>
                    <?php elseif ($currentUser['role'] === 'teacher'): ?>
                        <li><a href="index.php?page=teacher_courses" class="<?= strpos($activePage, 'teacher_') === 0 ? 'active' : '' ?>"><i class="fa-solid fa-chalkboard-user mr-1"></i> ระบบผู้สอน</a></li>
                    <?php endif; ?>
                    
                    <li><a href="index.php?page=profile" class="<?= $activePage == 'profile' ? 'active' : '' ?>"><i class="fa-solid fa-user mr-1"></i> โปรไฟล์ของฉัน</a></li>
                    
                    <li>
                        <a href="index.php?page=logout" class="btn btn-outline btn-sm" style="margin-left: 10px; border-color: rgba(239, 68, 68, 0.4); color: #ef4444;"><i class="fa-solid fa-arrow-right-from-bracket"></i> ออกจากระบบ</a>
                    </li>
                <?php else: ?>
                    <li><a href="index.php?page=login" class="btn btn-outline btn-sm">เข้าสู่ระบบ</a></li>
                    <li><a href="index.php?page=register" class="btn btn-primary btn-sm">สมัครสมาชิก</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
