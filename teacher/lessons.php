<?php
require_once __DIR__ . '/header.php';

$course_id = (int)($_GET['course_id'] ?? 0);
$action = $_GET['action'] ?? '';

// Check course ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher['id']]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลวิชานี้ หรือคุณไม่มีสิทธิ์เข้าถึง'];
    echo "<script>window.location.href='index.php?page=teacher_courses';</script>";
    exit;
}

// Handle Add Lesson
if ($action === 'add_lesson' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $document_path = trim($_POST['document_path'] ?? '');
    
    if (empty($title) || empty($content)) {
        $error = 'กรุณาระบุชื่อบทเรียน และเนื้อหาหลักสูตร';
    } else {
        // Standardize video URL to embed form if possible
        if (!empty($video_url) && strpos($video_url, 'youtube.com/watch?v=') !== false) {
            $parts = parse_url($video_url);
            parse_str($parts['query'] ?? '', $query);
            if (isset($query['v'])) {
                $video_url = "https://www.youtube.com/embed/" . $query['v'];
            }
        } elseif (!empty($video_url) && strpos($video_url, 'youtu.be/') !== false) {
            $path = parse_url($video_url, PHP_URL_PATH);
            $video_url = "https://www.youtube.com/embed" . $path;
        }

        $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, content, video_url, document_path) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $title, $content, $video_url ?: null, $document_path ?: null])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'เพิ่มบทเรียนย่อยเรียบร้อยแล้ว!'];
            echo "<script>window.location.href='index.php?page=teacher_lessons&course_id=$course_id';</script>";
            exit;
        } else {
            $error = 'เกิดข้อผิดพลาดในการบันทึกบทเรียน';
        }
    }
}

// Handle Delete Lesson
if ($action === 'delete_lesson') {
    $lesson_id = (int)($_GET['lesson_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ? AND course_id = ?");
    if ($stmt->execute([$lesson_id, $course_id])) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ลบบทเรียนเรียบร้อยแล้ว'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบ'];
    }
    echo "<script>window.location.href='index.php?page=teacher_lessons&course_id=$course_id';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$courseLessons = $stmt->fetchAll();
?>

<div class="glass-card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <a href="index.php?page=teacher_courses" style="color: var(--accent); text-decoration: none; font-size: 0.9rem;"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 style="font-size: 1.6rem; font-weight: 800; margin-top: 5px;">จัดการเนื้อหาบทเรียนของ: <?= htmlspecialchars($course['title']) ?></h2>
        </div>
    </div>

    <!-- Lessons Listing -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ลำดับ</th>
                    <th>ชื่อบทเรียนย่อย</th>
                    <th>ลิงก์วิดีโอ YouTube</th>
                    <th>ชื่อไฟล์เอกสาร</th>
                    <th style="text-align: center; width: 150px;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($courseLessons) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">ยังไม่มีบทเรียนในวิชานี้ โปรดระบุหัวข้อด้านล่างเพื่อเพิ่มบทเรียนย่อย</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courseLessons as $i => $lesson): ?>
                        <tr>
                            <td>บทที่ <?= $i + 1 ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($lesson['title']) ?></td>
                            <td><code><?= htmlspecialchars($lesson['video_url'] ?: 'ไม่มี') ?></code></td>
                            <td><?= htmlspecialchars($lesson['document_path'] ?: 'ไม่มี') ?></td>
                            <td style="text-align: center;">
                                <a href="index.php?page=teacher_lessons&action=delete_lesson&lesson_id=<?= $lesson['id'] ?>&course_id=<?= $course_id ?>" 
                                   onclick="return confirm('ยืนยันที่จะลบบทเรียนนี้อย่างถาวร?')" 
                                   class="btn btn-danger btn-sm" style="padding: 4px 10px;">
                                    <i class="fa-solid fa-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Form: Add Lesson -->
<div class="glass-card">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--accent);"><i class="fa-solid fa-plus-circle"></i> เพิ่มหัวข้อบทเรียนย่อยใหม่</h3>
    <form action="index.php?page=teacher_lessons&action=add_lesson&course_id=<?= $course_id ?>" method="POST">
        <div class="form-group">
            <label class="form-label">หัวข้อบทเรียน (Lesson Title)</label>
            <input type="text" name="title" class="form-input" placeholder="ตัวอย่าง: บทที่ 1 เริ่มต้นวางแผนรายรับรายจ่าย" required>
        </div>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ลิงก์วิดีโอ YouTube (รองรับลิงก์ธรรมดาและฝัง)</label>
                <input type="url" name="video_url" class="form-input" placeholder="https://www.youtube.com/watch?v=...">
            </div>
            <div class="form-group">
                <label class="form-label">ชื่อไฟล์เอกสารสำหรับดาวน์โหลด (PDF/Doc)</label>
                <input type="text" name="document_path" class="form-input" placeholder="เช่น finance_l1.pdf">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">เนื้อหาบทเรียน (Text Content / Markdown)</label>
            <textarea name="content" class="form-input" rows="8" placeholder="เขียนรายละเอียดเนื้อหาที่นี่ สามารถแบ่งย่อหน้าเพื่อความสะดวกของผู้เรียน..." required></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <button type="submit" class="btn btn-success" style="padding: 12px 30px;">
                <i class="fa-solid fa-save"></i> บันทึกข้อมูลและเพิ่มบทเรียน
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
