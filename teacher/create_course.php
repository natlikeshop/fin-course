<?php
// teacher/create_course.php - Create Course Page
require_once __DIR__ . '/header.php';

$action = $_GET['action'] ?? '';
$error = '';

if ($action === 'create_course' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title) || empty($description)) {
        $error = 'กรุณากรอกชื่อวิชาและรายละเอียดวิชา';
    } else {
        $stmt = $pdo->prepare("INSERT INTO courses (title, description, teacher_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$title, $description, $teacher['id']])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'สร้างวิชาเรียนใหม่เรียบร้อยแล้ว!'];
            echo "<script>window.location.href='index.php?page=teacher_courses';</script>";
            exit;
        } else {
            $error = 'เกิดข้อผิดพลาดในการสร้างวิชาเรียน';
        }
    }
}
?>

<div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
    <a href="index.php?page=teacher_courses" class="btn btn-outline" style="padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; border-color: var(--border-color); color: var(--text-secondary); background: transparent;"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
    <h2 style="font-size: 1.8rem; font-weight: 800; margin: 0;"><i class="fa-solid fa-plus-circle"></i> สร้างวิชาหลักสูตรใหม่</h2>
</div>

<?php if (!empty($error)): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 12px; margin-bottom: 25px; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="glass-card">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 20px; color: var(--accent);"><i class="fa-solid fa-pencil"></i> รายละเอียดวิชาเรียน</h3>
    <form action="index.php?page=teacher_create_course&action=create_course" method="POST">
        <div class="form-group">
            <label class="form-label">ชื่อหลักสูตรวิชา (Course Title)</label>
            <input type="text" name="title" class="form-input" placeholder="เช่น การวางแผนเพื่อการเกษียณแบบก้าวหน้า" required>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label">คำอธิบายรายละเอียดหลักสูตรย่อ (Course Description)</label>
            <textarea name="description" class="form-input" rows="5" placeholder="เขียนอธิบายภาพรวมของทักษะการวางแผนการเงินที่ผู้เรียนจะได้รับจากวิชานี้..." required></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="index.php?page=teacher_courses" class="btn btn-outline" style="padding: 12px 30px; border-radius: 8px; border-color: var(--border-color); color: var(--text-secondary); background: transparent;">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 8px;">
                <i class="fa-solid fa-plus"></i> ยืนยันสร้างวิชาเรียน
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
