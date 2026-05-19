<?php
require_once __DIR__ . '/header.php';

$action = $_GET['action'] ?? '';

// Handle Delete Course
if ($action === 'delete_course') {
    $course_id = (int)($_GET['course_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
    if ($stmt->execute([$course_id, $teacher['id']])) {
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ลบรายวิชาและข้อมูลในระบบเรียบร้อยแล้ว'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบวิชา'];
    }
    echo "<script>window.location.href='index.php?page=teacher_courses';</script>";
    exit;
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <h2 style="font-size: 1.8rem; font-weight: 800; margin: 0;"><i class="fa-solid fa-graduation-cap"></i> รายวิชาที่คุณดูแล</h2>
    <a href="index.php?page=teacher_create_course" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i> สร้างวิชาเรียนใหม่</a>
</div>

<!-- Teacher Courses -->
<div class="glass-card" style="margin-bottom: 40px;">
    <?php 
        $stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as num_lessons, (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) as num_questions FROM courses c WHERE c.teacher_id = ? ORDER BY c.id DESC");
        $stmt->execute([$teacher['id']]);
        $teacherCourses = $stmt->fetchAll();
    ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อหลักสูตร</th>
                    <th>รายละเอียดโดยย่อ</th>
                    <th style="text-align: center;">จำนวนบทเรียน</th>
                    <th style="text-align: center;">จำนวนข้อสอบ</th>
                    <th style="text-align: center; width: 320px;">การจัดการข้อมูล</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($teacherCourses) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">คุณยังไม่ได้สร้างหลักสูตรเรียนออนไลน์ใด ๆ</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teacherCourses as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($c['title']) ?></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary);"><?= htmlspecialchars($c['description']) ?></td>
                            <td style="text-align: center;"><span class="badge badge-student"><?= $c['num_lessons'] ?> บท</span></td>
                            <td style="text-align: center;"><span class="badge badge-teacher"><?= $c['num_questions'] ?> ข้อ</span></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                    <!-- Action Button Group -->
                                    <div style="display: inline-flex; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                        <a href="index.php?page=teacher_lessons&course_id=<?= $c['id'] ?>" class="btn btn-success btn-sm" style="border-radius: 0; margin: 0; padding: 6px 14px; font-size: 0.85rem; border: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-list-ol"></i> บทเรียน
                                        </a>
                                        <div style="width: 1px; background: rgba(255, 255, 255, 0.25);"></div>
                                        <a href="index.php?page=teacher_quizzes&course_id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm" style="border-radius: 0; margin: 0; padding: 6px 14px; font-size: 0.85rem; border: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-clipboard-question"></i> แบบทดสอบ
                                        </a>
                                    </div>
                                    
                                    <!-- Delete Button -->
                                    <a href="index.php?page=teacher_courses&action=delete_course&course_id=<?= $c['id'] ?>" 
                                       onclick="return confirm('การลบวิชานี้จะทำการลบบทเรียน คำถาม และประวัติคะแนนของเด็กทั้งหมด คุณต้องการลบแน่นอน?')" 
                                       class="btn btn-outline btn-sm" style="padding: 6px 12px; color: #ef4444; border-color: rgba(239, 68, 68, 0.2); white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; border-radius: 8px; background: transparent;">
                                        <i class="fa-solid fa-trash"></i> ลบ
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
