<?php
// course_detail.php - Lesson Player and Course Details
require_once __DIR__ . '/../config.php';

$course_id = (int)($_GET['id'] ?? 0);
if ($course_id <= 0) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลหลักสูตรที่คุณเลือก'];
    header("Location: index.php?page=home");
    exit;
}

// Fetch Course Details
$stmt = $pdo->prepare("SELECT c.*, u.fullname as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลหลักสูตรที่คุณเลือก'];
    header("Location: index.php?page=home");
    exit;
}

// Fetch Lessons for this course
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

// Check if student is enrolled
$isEnrolled = false;
$isStudent = false;

if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'student') {
        $isStudent = true;
        $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$_SESSION['user_id'], $course_id]);
        if ($stmt->fetch()) {
            $isEnrolled = true;
        }
    } else {
        // Teachers and Admins have master access
        $isEnrolled = true;
    }
}

// Determine active lesson
$active_lesson_id = (int)($_GET['lesson_id'] ?? 0);
$active_lesson = null;

if ($isEnrolled && count($lessons) > 0) {
    if ($active_lesson_id > 0) {
        foreach ($lessons as $l) {
            if ($l['id'] === $active_lesson_id) {
                $active_lesson = $l;
                break;
            }
        }
    }
    // Default to first lesson if not selected or invalid
    if (!$active_lesson) {
        $active_lesson = $lessons[0];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.course-banner {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(240, 244, 255, 0.85) 100%) !important;
    border: 1px solid var(--border-color);
}
.lesson-item {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
}
.lesson-item:hover {
    background: #fff !important;
    transform: translateX(6px);
    border-color: var(--accent) !important;
}
.lesson-item.active {
    border-left: 4px solid var(--accent) !important;
    background: #fff !important;
    box-shadow: 0 8px 20px rgba(14, 165, 233, 0.08) !important;
    transform: translateX(6px);
}
.lesson-item.active .lesson-num {
    color: var(--accent) !important;
}
.video-container {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border-color);
}
</style>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <!-- Course Header Banner -->
    <div class="glass-card course-banner animate-slide-in" style="margin-bottom: 30px; padding: 40px;">
        <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: center;">
            <div style="width: 80px; height: 80px; border-radius: 20px; background: var(--primary-grad); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; box-shadow: var(--shadow-neon);">
                <?php if ($course['id'] == 1): ?>
                    <i class="fa-solid fa-wallet"></i>
                <?php else: ?>
                    <i class="fa-solid fa-chart-line"></i>
                <?php endif; ?>
            </div>
            
            <div style="flex-grow: 1;">
                <span style="font-size: 0.8rem; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">หลักสูตรเรียนออนไลน์</span>
                <h1 style="font-size: 2.2rem; font-weight: 800; margin-top: 5px; margin-bottom: 10px; line-height: 1.3; background: linear-gradient(135deg, #1e293b 50%, #475569 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    <?= htmlspecialchars($course['title']) ?>
                </h1>
                <p style="color: var(--text-secondary); max-width: 800px; font-size: 1rem;"><?= htmlspecialchars($course['description']) ?></p>
            </div>
        </div>
    </div>

    <!-- If not logged in or not enrolled, show Enrollment Page -->
    <?php if (!$isEnrolled): ?>
        <div class="grid-2 animate-slide-in" style="margin-top: 40px;">
            <div class="glass-card">
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 20px; color: var(--accent);"><i class="fa-solid fa-circle-info"></i> โครงสร้างการเรียนของหลักสูตร</h2>
                <p style="color: var(--text-secondary); margin-bottom: 25px;">วิชานี้ประกอบไปด้วยเนื้อหาทั้งหมด <strong><?= count($lessons) ?> บทเรียน</strong> ซึ่งคุณจำเป็นต้องศึกษาให้จบทุกบทเรียนเพื่อเข้าสู่การทำแบบทดสอบและประเมินผลหลังเรียน</p>
                
                <div class="lesson-list">
                    <?php foreach ($lessons as $i => $lesson): ?>
                        <div class="lesson-item" style="cursor: default;">
                            <span class="lesson-num">บทที่ <?= $i + 1 ?></span>
                            <span style="flex-grow: 1;"><?= htmlspecialchars($lesson['title']) ?></span>
                            <span style="color: #94a3b8; background: rgba(148, 163, 184, 0.1); width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.85rem;"><i class="fa-solid fa-lock"></i></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="glass-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-color: rgba(14, 165, 233, 0.2); background: radial-gradient(circle at center, rgba(14, 165, 233, 0.04) 0%, transparent 70%), var(--bg-card); box-shadow: 0 10px 30px rgba(14, 165, 233, 0.02);">
                <i class="fa-solid fa-graduation-cap" style="font-size: 5rem; color: var(--accent); margin-bottom: 25px; filter: drop-shadow(0 8px 15px rgba(14, 165, 233, 0.25));"></i>
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 15px;">เริ่มศึกษาบทเรียนเดี๋ยวนี้</h2>
                <p style="color: var(--text-secondary); max-width: 380px; margin-bottom: 30px;">สมัครเข้าเรียนหลักสูตรนี้ฟรีวันนี้ เพื่อเข้าถึงไฟล์วิดีโอ เอกสารดาวน์โหลดประกอบการสอน และระบบแบบทดสอบ</p>
                
                <?php if (!isLoggedIn()): ?>
                    <a href="index.php?page=login" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.05rem; border-radius: 12px;">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i> เข้าสู่ระบบเพื่อสมัครเรียน
                    </a>
                <?php else: ?>
                    <a href="index.php?page=home&enroll_id=<?= $course['id'] ?>" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.05rem; border-radius: 12px; box-shadow: var(--shadow-neon);">
                        <i class="fa-solid fa-plus-circle mr-1"></i> ยืนยันสมัครเรียนทันที
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Full interactive Course Player for Enrolled Users -->
        <div class="learn-grid animate-slide-in">
            <!-- Sidebar: Lesson Navigator -->
            <div class="glass-card" style="padding: 20px; align-self: flex-start;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; color: var(--accent);"><i class="fa-solid fa-list-ol"></i> สารบัญบทเรียน</h3>
                
                <div class="lesson-list">
                    <?php foreach ($lessons as $i => $lesson): ?>
                        <a href="index.php?page=course_detail&id=<?= $course_id ?>&lesson_id=<?= $lesson['id'] ?>" class="lesson-item <?= $active_lesson['id'] == $lesson['id'] ? 'active' : '' ?>">
                            <span class="lesson-num"><?= $i + 1 ?></span>
                            <span style="font-size: 0.9rem; flex-grow: 1;"><?= htmlspecialchars($lesson['title']) ?></span>
                            <span><i class="fa-regular fa-circle-play"></i></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Course Test Action -->
                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <a href="index.php?page=quiz&course_id=<?= $course_id ?>" class="btn btn-secondary" style="width: 100%; border-radius: 12px;">
                        <i class="fa-solid fa-clipboard-question"></i> ทำแบบทดสอบวัดผล
                    </a>
                </div>
            </div>
            
            <!-- Main: Lesson Viewer -->
            <div class="glass-card" style="padding: 30px;">
                <?php if ($active_lesson): ?>
                    <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <span style="color: var(--accent); font-weight: 800;">บทเรียน:</span> 
                        <?= htmlspecialchars($active_lesson['title']) ?>
                    </h2>
                    
                    <!-- Video Embed -->
                    <?php if (!empty($active_lesson['video_url'])): ?>
                        <div class="video-container">
                            <iframe src="<?= htmlspecialchars($active_lesson['video_url']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Lesson Written Content -->
                    <div style="font-size: 1.05rem; line-height: 1.8; color: var(--text-primary); background: rgba(0, 0, 0, 0.02); border-radius: 12px; padding: 25px; border: 1px solid var(--border-color); white-space: pre-line; margin-bottom: 30px;">
                        <?= htmlspecialchars($active_lesson['content']) ?>
                    </div>
                    
                    <!-- Document Download -->
                    <?php if (!empty($active_lesson['document_path'])): ?>
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; background: rgba(14, 165, 233, 0.04); border: 1px solid rgba(14, 165, 233, 0.12); padding: 20px; border-radius: 16px; gap: 20px; box-shadow: 0 5px 15px rgba(14, 165, 233, 0.02);">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <i class="fa-regular fa-file-pdf" style="font-size: 2.2rem; color: #ef4444;"></i>
                                <div>
                                    <h4 style="font-weight: 600;">เอกสารประกอบการเรียนประกอบบทนี้</h4>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem;">ไฟล์เอกสารสรุปเนื้อหา PDF (ดาวน์โหลดเพื่อทบทวนย้อนหลัง)</p>
                                </div>
                            </div>
                            <!-- Mock Download Trigger -->
                            <a href="#" onclick="alert('ดาวน์โหลดเอกสาร: <?= htmlspecialchars($active_lesson['document_path']) ?> สำเร็จ!'); return false;" class="btn btn-outline" style="border-color: var(--accent); color: var(--accent); background: #fff; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.05);">
                                <i class="fa-solid fa-download"></i> ดาวน์โหลด PDF
                            </a>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 0;">
                        <i class="fa-regular fa-face-smile" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                        <h3>ยินดีต้อนรับสู่หลักสูตร!</h3>
                        <p style="color: var(--text-secondary);">โปรดเลือกบทเรียนที่ต้องการในเมนูด้านซ้ายมือเพื่อเริ่มการเรียนรู้</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
