<?php
// index.php - Main Router
require_once 'config.php';

$page = $_GET['page'] ?? 'home';

$routes = [
    'home' => 'pages/home.php',
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
    'logout' => 'auth/logout.php',
    'profile' => 'student/profile.php',
    'course_detail' => 'pages/course_detail.php',
    'quiz' => 'student/quiz.php',
    'teacher_courses' => 'teacher/courses.php',
    'teacher_create_course' => 'teacher/create_course.php',
    'teacher_lessons' => 'teacher/lessons.php',
    'teacher_quizzes' => 'teacher/quizzes.php',
    'teacher_reports' => 'teacher/reports.php',
    'admin_overview' => 'admin/overview.php',
    'admin_users' => 'admin/users.php',
    'admin_user_form' => 'admin/user_form.php',
    'admin_courses' => 'admin/courses.php'
];

if (array_key_exists($page, $routes)) {
    require $routes[$page];
} else {
    // 404
    require_once 'includes/header.php';
    echo "<div class='container' style='padding: 100px 0; text-align: center; min-height: 60vh;'>
            <h1 style='font-size: 4rem; font-weight: 800; color: var(--accent);'>404</h1>
            <h2 style='font-size: 2rem; margin-bottom: 10px;'>ไม่พบหน้านี้</h2>
            <p style='color: var(--text-secondary); margin-bottom: 30px;'>หน้าที่คุณกำลังพยายามเข้าถึงอาจถูกลบหรือไม่มีอยู่จริง</p>
            <a href='index.php?page=home' class='btn btn-primary'>กลับสู่หน้าหลัก</a>
          </div>";
    require_once 'includes/footer.php';
}
