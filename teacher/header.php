<?php
// teacher/header.php - Teacher Layout Header
require_once __DIR__ . '/../config.php';
requireRole('teacher');
$teacher = getCurrentUser();
global $page;

$error = $error ?? '';
$success = $success ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผงควบคุมผู้สอน | Fin-Course</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        body { margin: 0; padding: 0; background: var(--bg-dark); }
        .dashboard-layout { min-height: 100vh; padding: 0; align-items: stretch; margin: 0; max-width: 100%; border-radius: 0; display: flex; }
        .dashboard-sidebar { border-radius: 0; min-height: 100vh; margin: 0; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; width: 280px; flex-shrink: 0; position: sticky; top: 0; background: #fff; z-index: 10; }
        .dashboard-content { padding: 40px; flex-grow: 1; width: calc(100% - 280px); }
        
        .mobile-actions { display: none; }
        
        @media (max-width: 992px) {
            .dashboard-layout { flex-direction: column; }
            .dashboard-sidebar { width: 100%; min-height: auto; position: sticky; top: 0; padding: 15px; border-right: none; border-bottom: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
            .dashboard-content { padding: 20px; width: 100%; }
            .sidebar-profile { display: flex; align-items: center; gap: 12px; padding-bottom: 12px; margin-bottom: 12px; text-align: left; }
            .sidebar-profile > div:first-child { margin: 0 !important; width: 45px !important; height: 45px !important; font-size: 1.1rem !important; }
            .sidebar-profile-info { flex-grow: 1; display: flex; justify-content: space-between; align-items: center; }
            .mobile-actions { display: flex !important; gap: 8px; }
            .sidebar-nav-links { display: none; flex-direction: column; gap: 8px; margin-top: 5px; border-top: 1px solid var(--border-color); padding-top: 15px; }
            .sidebar-nav-links.show { display: flex; animation: slideDown 0.3s ease forwards; }
            .sidebar-nav-links .sidebar-link { padding: 12px 18px; font-size: 0.95rem; justify-content: flex-start; margin-bottom: 0; border-radius: 12px; background: rgba(0,0,0,0.02); border: 1px solid transparent; width: 100%; }
            .sidebar-nav-links .sidebar-link:hover { border-color: rgba(0,0,0,0.1); background: rgba(0,0,0,0.06); }
            .sidebar-nav-links .sidebar-link.active { background: var(--primary-grad); color: #fff; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); border: none; }
            .sidebar-nav-links .sidebar-link.active i { color: #fff; }
            .sidebar-bottom { display: none; }
        }
    </style>
</head>
<body>
<?php renderFlashMessage(); ?>
<div class="dashboard-layout animate-slide-in">
    <div class="dashboard-sidebar">
        <div class="sidebar-profile" style="padding: 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 20px; text-align: center;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-grad); color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 10px; box-shadow: var(--shadow-blue);">
                <?= mb_substr($teacher['fullname'], 0, 1) ?: 'T' ?>
            </div>
            <div class="sidebar-profile-info">
                <div style="overflow: hidden;">
                    <div style="font-weight: 700; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($teacher['fullname']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--accent); font-weight: 600; text-transform: uppercase;">ผู้สอนในระบบ</div>
                </div>
                <div class="mobile-actions">
                    <button type="button" class="btn btn-outline btn-sm mobile-menu-toggle" style="padding: 6px 10px; border-radius: 8px; border-color: var(--border-color); color: var(--text-primary); cursor: pointer;" title="เปิด/ปิดเมนู"><i class="fa-solid fa-bars"></i></button>
                    <a href="index.php?page=home" class="btn btn-outline btn-sm" style="padding: 6px 10px; border-radius: 8px; border-color: var(--border-color); color: var(--text-secondary);" title="หน้าหลัก"><i class="fa-solid fa-house"></i></a>
                    <a href="index.php?page=logout" class="btn btn-danger btn-sm" style="padding: 6px 10px; border-radius: 8px; background: #ef4444; border: none;" title="ออกจากระบบ"><i class="fa-solid fa-power-off"></i></a>
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav-links">
            <a href="index.php?page=teacher_courses" class="sidebar-link <?= (strpos($page, 'teacher_courses') !== false || strpos($page, 'teacher_lessons') !== false || strpos($page, 'teacher_quizzes') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-chalkboard"></i> การจัดการวิชาเรียน
            </a>
            <a href="index.php?page=teacher_reports" class="sidebar-link <?= ($page === 'teacher_reports') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i> คะแนนสอบของนักเรียน
            </a>
        </div>
        
        <div class="sidebar-bottom" style="margin-top: auto; padding: 20px; border-top: 1px solid var(--border-color);">
            <a href="index.php?page=home" class="sidebar-link" style="margin-bottom: 5px;">
                <i class="fa-solid fa-house"></i> หน้าหลักเว็บไซต์
            </a>
            <a href="index.php?page=logout" class="sidebar-link" style="color: #ef4444;">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> ออกจากระบบ
            </a>
        </div>
    </div>
    
    <div class="dashboard-content">
        <?php if (!empty($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 12px; margin-bottom: 25px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
