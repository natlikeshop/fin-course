<?php
require_once __DIR__ . '/header.php';
$action = $_GET['action'] ?? '';
$error = '';

if ($action === 'delete_course') {
    $course_id = (int)($_GET['course_id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ลบวิชาและบทเรียนทั้งหมดออกเรียบร้อย'];
    header("Location: index.php?page=admin_courses");
    exit;
}
?>
<style>
    /* Responsive Table Styles */
    @media (max-width: 768px) {
        .admin-courses-table, 
        .admin-courses-table thead, 
        .admin-courses-table tbody, 
        .admin-courses-table th, 
        .admin-courses-table td, 
        .admin-courses-table tr { 
            display: block; 
        }
        
        /* Hide table headers (but keep for screen readers) */
        .admin-courses-table thead tr { 
            position: absolute;
            top: -9999px;
            left: -9999px;
        }
        
        .admin-courses-table tr {
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background: #fff;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        
        .admin-courses-table td { 
            border: none;
            position: relative;
            padding-left: 50% !important; 
            text-align: right !important;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }
        
        .admin-courses-table td:last-child {
            border-bottom: none;
            text-align: center !important;
            padding-left: 16px !important;
            margin-top: 15px;
        }
        
        .admin-courses-table td:before { 
            position: absolute;
            top: 14px;
            left: 16px;
            width: 45%; 
            padding-right: 10px; 
            white-space: nowrap;
            content: attr(data-label);
            font-weight: 700;
            text-align: left;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
    }

    /* Action Buttons Enhancement */
    .admin-actions {
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-action {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 600;
        white-space: nowrap;
        padding: 6px 10px !important;
        border-radius: 10px;
        font-size: 0.85rem;
    }

    .btn-view-course {
        background: rgba(14, 165, 233, 0.1);
        color: var(--accent);
        border: 1px solid rgba(14, 165, 233, 0.2);
    }

    .btn-view-course:hover {
        background: var(--accent);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
    }

    .btn-delete-course {
        background: var(--danger-grad);
        color: #fff;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
    }

    .btn-delete-course:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(239, 68, 68, 0.25);
        filter: brightness(1.1);
    }

    @media (max-width: 768px) {
        .admin-actions {
            flex-direction: column;
            gap: 10px;
        }
        .btn-action {
            width: 100%;
            padding: 12px !important;
            font-size: 0.9rem;
        }
    }
</style>

<div style="margin-bottom: 30px;">
    <h2 style="font-size: 1.8rem; font-weight: 800;"><i class="fa-solid fa-graduation-cap"></i> การกำกับดูแลวิชาเรียนและหลักสูตรทั้งหมด</h2>
    <p style="color: var(--text-secondary);">ในฐานะ Admin คุณสามารถลบหลักสูตรใด ๆ ก็ได้หากเนื้อหานั้นละเมิดความปลอดภัยของแพลตฟอร์ม</p>
</div>

<div class="glass-card">
    <?php
        $courses = $pdo->query("SELECT c.*, u.fullname as teacher_name, (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as num_lessons FROM courses c JOIN users u ON c.teacher_id = u.id ORDER BY c.id DESC")->fetchAll();
    ?>
    <div class="table-responsive">
        <table class="table admin-courses-table">
            <thead>
                <tr>
                    <th>รหัสหลักสูตร</th>
                    <th>ชื่อรายวิชา</th>
                    <th>ผู้สร้างบทเรียน (Teacher)</th>
                    <th style="text-align: center;">จำนวนบทเรียน</th>
                    <th style="text-align: center; width: 280px;">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($courses) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">ระบบยังไม่มีข้อมูลรายวิชาเรียนใด ๆ</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td data-label="รหัสหลักสูตร">#<?= $c['id'] ?></td>
                            <td data-label="ชื่อรายวิชา" style="font-weight: 600;"><?= htmlspecialchars($c['title']) ?></td>
                            <td data-label="ผู้สร้างบทเรียน"><?= htmlspecialchars($c['teacher_name']) ?></td>
                            <td data-label="จำนวนบทเรียน" style="text-align: center;"><span class="badge badge-student"><?= $c['num_lessons'] ?> บทเรียน</span></td>
                            <td data-label="การจัดการ" style="text-align: center;">
                                <div class="admin-actions">
                                    <a href="index.php?page=course_detail&id=<?= $c['id'] ?>" class="btn-action btn-view-course">
                                        <i class="fa-solid fa-eye"></i> ดูรายละเอียด
                                    </a>
                                    <a href="index.php?page=admin_courses&action=delete_course&course_id=<?= $c['id'] ?>" 
                                       onclick="return confirm('การดำเนินการนี้จะทำการลบวิชาเรียน บทเรียนย่อย และแบบทดสอบทั้งหมดภายใต้รหัสดังกล่าวออกถาวรทันที ยืนยันลบ?')" 
                                       class="btn-action btn-delete-course">
                                        <i class="fa-solid fa-trash-can"></i> ลบหลักสูตร
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
<?php
require_once __DIR__ . '/footer.php';
?>