<?php
// index.php - Homepage & Course Listing
require_once __DIR__ . '/../config.php';

$search = trim($_GET['search'] ?? '');

// Handle Enrollment Request
if (isset($_GET['enroll_id'])) {
    requireLogin();
    $course_id = (int)$_GET['enroll_id'];
    $student_id = $_SESSION['user_id'];
    
    // Check if user is a student (only students can enroll in courses, though admins/teachers can view them anyway)
    if ($_SESSION['role'] !== 'student') {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'เฉพาะผู้ใช้งานระดับผู้เรียนเท่านั้นที่สามารถสมัครเรียนได้'];
        header("Location: index.php?page=home");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'สมัครเข้าเรียนวิชานี้สำเร็จแล้ว! ยินดีต้อนรับสู่บทเรียน'];
    } catch (PDOException $e) {
        // If already enrolled, ignore or handle gracefully
    }
    
    header("Location: index.php?page=course_detail&id=" . $course_id);
    exit;
}

// Fetch Courses with Teacher Name
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT c.*, u.fullname as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.title LIKE ? OR c.description LIKE ? ORDER BY c.id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT c.*, u.fullname as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.id DESC");
}
$courses = $stmt->fetchAll();

// Get enrolled course IDs for current user to show correct buttons
$enrolledCourseIds = [];
if (isLoggedIn() && $_SESSION['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT course_id FROM enrollments WHERE student_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $enrolledCourseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Header Section -->
<header class="hero">
    <div class="container animate-slide-in">
        <h1 class="hero-title">ยกระดับความรู้ทางการเงินของคุณกับ <span>Fin-Course</span></h1>
        <p class="hero-subtitle">เรียนรู้ออนไลน์อย่างมีระบบ ค้นพบสไตล์การลงทุนที่เหมาะสม และทำแบบทดสอบแบบทันทีเพื่อวัดผลความรู้ของคุณได้อย่างแม่นยำ</p>
        
        <!-- Search bar with glow -->
        <div style="max-width: 600px; margin: 0 auto;">
            <form action="index.php" method="GET" style="display: flex; gap: 10px; background: rgba(255,255,255,0.6); border: 1px solid var(--border-color); padding: 6px; border-radius: 16px; backdrop-filter: blur(10px);">
                <input type="hidden" name="page" value="home">
                <input type="text" name="search" class="form-input" placeholder="ค้นหาชื่อรายวิชาหรือรายละเอียดบทเรียน..." style="border: none; background: transparent; margin-bottom: 0;" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary" style="border-radius: 12px; padding: 10px 24px;">
                    <i class="fa-solid fa-magnifying-glass"></i> ค้นหา
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Main Course Grid Section -->
<main class="container" style="margin-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 1.8rem; font-weight: 700;">
            <?= !empty($search) ? 'ผลการค้นหาสำหรับ "' . htmlspecialchars($search) . '"' : 'หลักสูตรทั้งหมด' ?>
        </h2>
        <span style="color: var(--text-secondary); font-size: 0.95rem;">มีทั้งหมด <?= count($courses) ?> หลักสูตร</span>
    </div>

    <?php if (count($courses) === 0): ?>
        <div class="glass-card text-center" style="padding: 60px 20px; text-align: center;">
            <i class="fa-regular fa-folder-open" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
            <h3 style="font-size: 1.4rem; margin-bottom: 10px;">ไม่พบรายวิชาที่ตรงกับคำค้นหาของคุณ</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">ลองเปลี่ยนคำค้นหาใหม่ หรือเปิดดูรายวิชาทั้งหมด</p>
            <a href="index.php?page=home" class="btn btn-outline">ดูรายวิชาทั้งหมด</a>
        </div>
    <?php else: ?>
        <div class="grid-3">
            <?php foreach ($courses as $index => $course): ?>
                <div class="glass-card course-card animate-slide-in" style="animation-delay: <?= $index * 100 ?>ms;">
                    <div class="course-img-wrapper">
                        <!-- Icon representing finance or investment -->
                        <span class="course-img-placeholder">
                            <?php if ($course['id'] == 1): ?>
                                <i class="fa-solid fa-wallet"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-chart-line"></i>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="course-info">
                        <span class="course-tag">
                            <?= $course['id'] == 1 ? 'การเงินส่วนบุคคล' : 'การลงทุน' ?>
                        </span>
                        
                        <h3 class="course-title"><?= htmlspecialchars($course['title']) ?></h3>
                        <p class="course-desc"><?= htmlspecialchars($course['description']) ?></p>
                        
                        <div class="course-footer">
                            <div class="course-teacher">
                                <span class="teacher-avatar"><?= mb_substr($course['teacher_name'], 8, 1) ?: 'T' ?></span>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($course['teacher_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">ผู้สอน</div>
                                </div>
                            </div>
                            
                            <!-- Dynamically check action buttons -->
                            <?php if (!isLoggedIn()): ?>
                                <a href="index.php?page=home&enroll_id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-plus"></i> สมัครเรียน
                                </a>
                            <?php else: ?>
                                <?php if ($_SESSION['role'] === 'student'): ?>
                                    <?php if (in_array($course['id'], $enrolledCourseIds)): ?>
                                        <a href="index.php?page=course_detail&id=<?= $course['id'] ?>" class="btn btn-success btn-sm">
                                            <i class="fa-solid fa-play"></i> เรียนต่อ
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?page=home&enroll_id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-plus"></i> สมัครเรียน
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Admins / Teachers can view everything directly -->
                                    <a href="index.php?page=course_detail&id=<?= $course['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-eye"></i> ดูเนื้อหา
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
