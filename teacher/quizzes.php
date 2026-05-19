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

// Handle Add Question
if ($action === 'add_question' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_option = trim($_POST['correct_option'] ?? 'A');
    
    if (empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = 'กรุณากรอกคำถามและตัวเลือกทั้งหมดให้ครบถ้วน';
    } else {
        $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$course_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'เพิ่มคำถามในแบบทดสอบเรียบร้อยแล้ว!'];
            echo "<script>window.location.href='index.php?page=teacher_quizzes&course_id=$course_id';</script>";
            exit;
        } else {
            $error = 'เกิดข้อผิดพลาดในการบันทึกคำถาม';
        }
    }
}

// Handle Delete Question
if ($action === 'delete_question') {
    $question_id = (int)($_GET['question_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? AND course_id = ?");
    if ($stmt->execute([$question_id, $course_id])) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ลบคำถามแบบทดสอบเรียบร้อยแล้ว'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบ'];
    }
    echo "<script>window.location.href='index.php?page=teacher_quizzes&course_id=$course_id';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$courseQuizzes = $stmt->fetchAll();
?>

<div class="glass-card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <a href="index.php?page=teacher_courses" style="color: var(--accent); text-decoration: none; font-size: 0.9rem;"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 style="font-size: 1.6rem; font-weight: 800; margin-top: 5px;">จัดการข้อสอบแบบประเมิน: <?= htmlspecialchars($course['title']) ?></h2>
        </div>
    </div>

    <!-- Quiz Questions -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">ข้อที่</th>
                    <th>โจทย์คำถาม</th>
                    <th>ก / ข / ค / ง</th>
                    <th style="text-align: center; width: 80px;">เฉลย</th>
                    <th style="text-align: center; width: 100px;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($courseQuizzes) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">วิชานี้ยังไม่มีชุดคำถามในระบบ โปรดใช้แบบฟอร์มด้านล่างเพื่อเพิ่มคำถามอย่างน้อย 1 ข้อ</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courseQuizzes as $i => $q): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td style="font-weight: 600; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($q['question']) ?></td>
                            <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                ก: <?= htmlspecialchars($q['option_a']) ?> | ข: <?= htmlspecialchars($q['option_b']) ?><br>
                                ค: <?= htmlspecialchars($q['option_c']) ?> | ง: <?= htmlspecialchars($q['option_d']) ?>
                            </td>
                            <td style="text-align: center;"><span class="badge badge-student"><?= $q['correct_option'] ?></span></td>
                            <td style="text-align: center;">
                                <a href="index.php?page=teacher_quizzes&action=delete_question&question_id=<?= $q['id'] ?>&course_id=<?= $course_id ?>" 
                                   onclick="return confirm('ยืนยันลบคำถามนี้?')" 
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

<!-- Form: Add Question -->
<div class="glass-card">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--accent-purple);"><i class="fa-solid fa-plus-circle"></i> เพิ่มโจทย์คำถามแบบปรนัย</h3>
    <form action="index.php?page=teacher_quizzes&action=add_question&course_id=<?= $course_id ?>" method="POST">
        <div class="form-group">
            <label class="form-label">โจทย์คำถาม (Question)</label>
            <input type="text" name="question" class="form-input" placeholder="ตัวอย่าง: กฎของการสำรองเงินสดฉุกเฉินควรออมเท่าใดของค่าใช้จ่ายรายเดือน?" required>
        </div>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 10px;">
            <div class="form-group">
                <label class="form-label">ตัวเลือก ก (Option A)</label>
                <input type="text" name="option_a" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">ตัวเลือก ข (Option B)</label>
                <input type="text" name="option_b" class="form-input" required>
            </div>
        </div>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ตัวเลือก ค (Option C)</label>
                <input type="text" name="option_c" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">ตัวเลือก ง (Option D)</label>
                <input type="text" name="option_d" class="form-input" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label">เฉลยข้อที่ถูกต้อง (Correct Option)</label>
            <select name="correct_option" class="form-input form-select" required>
                <option value="A">ก (Option A)</option>
                <option value="B">ข (Option B)</option>
                <option value="C">ค (Option C)</option>
                <option value="D">ง (Option D)</option>
            </select>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-secondary" style="padding: 12px 30px;">
                <i class="fa-solid fa-save"></i> บันทึกโจทย์คำถาม
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
