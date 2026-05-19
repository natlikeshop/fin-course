<?php
// profile.php - Student Personal Dashboard
require_once __DIR__ . '/../config.php';

requireLogin();

$user = getCurrentUser();

// Fetch enrollments
$stmt = $pdo->prepare("SELECT e.*, c.title as course_title, c.description as course_desc, u.fullname as teacher_name FROM enrollments e JOIN courses c ON e.course_id = c.id JOIN users u ON c.teacher_id = u.id WHERE e.student_id = ? ORDER BY e.enrolled_at DESC");
$stmt->execute([$user['id']]);
$enrollments = $stmt->fetchAll();

// Fetch quiz results
$stmt = $pdo->prepare("SELECT r.*, c.title as course_title FROM quiz_results r JOIN courses c ON r.course_id = c.id WHERE r.student_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user['id']]);
$results = $stmt->fetchAll();

// Calculations for stats
$numEnrollments = count($enrollments);
$numQuizzes = count($results);
$avgScorePercent = 0;

if ($numQuizzes > 0) {
    $totalPercent = 0;
    foreach ($results as $res) {
        $totalPercent += ($res['score'] / $res['total_questions']) * 100;
    }
    $avgScorePercent = round($totalPercent / $numQuizzes, 1);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <!-- Profile Welcome Cover Card -->
    <div class="glass-card animate-slide-in" style="margin-bottom: 30px; padding: 40px; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 244, 255, 0.95) 100%); box-shadow: 0 10px 30px rgba(14, 165, 233, 0.05); border-color: rgba(14, 165, 233, 0.1);">
        <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: center;">
            <div style="width: 90px; height: 90px; border-radius: 50%; background: var(--secondary-grad); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #fff; box-shadow: var(--shadow-purple); font-weight: 700;">
                <?= mb_substr($user['fullname'], 8, 1) ?: 'U' ?>
            </div>
            
            <div style="flex-grow: 1;">
                <span class="badge badge-student" style="font-size: 0.8rem; padding: 4px 12px; margin-bottom: 8px;">
                    <i class="fa-solid fa-circle-check mr-1"></i> ผู้เรียนระดับบัญชีทั่วไป
                </span>
                <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.2; margin-bottom: 5px;"><?= htmlspecialchars($user['fullname']) ?></h1>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; color: var(--text-secondary); font-size: 0.95rem;">
                    <span><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></span>
                    <span>|</span>
                    <span><i class="fa-solid fa-user-tag"></i> Username: <?= htmlspecialchars($user['username']) ?></span>
                    <span>|</span>
                    <span><i class="fa-solid fa-calendar-days"></i> ร่วมเรียนกับเราตั้งแต่วันที่: <?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stat-grid animate-slide-in" style="animation-delay: 100ms;">
        <!-- Stat 1 -->
        <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(0, 242, 254, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
            <div class="stat-icon" style="background: rgba(0, 242, 254, 0.15); color: var(--accent);"><i class="fa-solid fa-book"></i></div>
            <div>
                <div class="stat-num"><?= $numEnrollments ?></div>
                <div class="stat-label">วิชาที่ลงทะเบียนเรียน</div>
            </div>
        </div>
        <!-- Stat 2 -->
        <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
            <div class="stat-icon" style="background: rgba(177, 86, 255, 0.15); color: var(--accent-purple);"><i class="fa-solid fa-file-invoice"></i></div>
            <div>
                <div class="stat-num"><?= $numQuizzes ?></div>
                <div class="stat-label">แบบทดสอบที่ทำแล้ว</div>
            </div>
        </div>
        <!-- Stat 3 -->
        <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
            <div class="stat-icon" style="background: rgba(56, 239, 125, 0.15); color: #38ef7d;"><i class="fa-solid fa-chart-pie"></i></div>
            <div>
                <div class="stat-num"><?= $avgScorePercent ?>%</div>
                <div class="stat-label">คะแนนเฉลี่ยรวม</div>
            </div>
        </div>
    </div>

    <div class="grid-2 animate-slide-in" style="margin-top: 30px; animation-delay: 200ms; grid-template-columns: 1fr;">
        <!-- Left / Enrolled Courses -->
        <div class="glass-card">
            <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 20px; color: var(--accent); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-graduation-cap"></i> วิชาที่ฉันลงทะเบียนเรียน
            </h2>
            
            <?php if ($numEnrollments === 0): ?>
                <div style="text-align: center; padding: 40px 0;">
                    <i class="fa-solid fa-pencil" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">คุณยังไม่ได้ลงทะเบียนเรียนวิชาใด ๆ เลยในขณะนี้</p>
                    <a href="index.php?page=home" class="btn btn-primary">ค้นหาและลงทะเบียนวิชาที่น่าสนใจ</a>
                </div>
            <?php else: ?>
                <div class="grid-3" style="margin-top: 15px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php foreach ($enrollments as $enroll): ?>
                        <div class="glass-card" style="padding: 20px; background: rgba(0,0,0,0.02);">
                            <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 3rem;">
                                <?= htmlspecialchars($enroll['course_title']) ?>
                            </h4>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 15px;">ผู้สอน: <?= htmlspecialchars($enroll['teacher_name']) ?></div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 15px;">
                                <span style="font-size: 0.75rem; color: var(--text-secondary);"><i class="fa-regular fa-clock"></i> <?= date('d M Y', strtotime($enroll['enrolled_at'])) ?></span>
                                <a href="index.php?page=course_detail&id=<?= $enroll['course_id'] ?>" class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-play"></i> เรียนต่อ
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right / Quiz Attempt History -->
        <div class="glass-card" style="margin-top: 30px;">
            <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 20px; color: var(--accent-purple); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-file-invoice"></i> ประวัติผลการทำแบบทดสอบ (Quiz Results)
            </h2>
            
            <?php if ($numQuizzes === 0): ?>
                <div style="text-align: center; padding: 40px 0;">
                    <i class="fa-solid fa-file-signature" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                    <p style="color: var(--text-secondary);">คุณยังไม่มีประวัติการส่งแบบทดสอบ</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ชื่อรายวิชา</th>
                                <th>วันที่และเวลาส่งข้อสอบ</th>
                                <th style="text-align: center;">คะแนนที่ได้</th>
                                <th style="text-align: center;">ร้อยละ</th>
                                <th style="text-align: center;">ผลประเมิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $res): ?>
                                <?php 
                                    $percent = ($res['score'] / $res['total_questions']) * 100;
                                    $isPassed = $percent >= 50;
                                ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($res['course_title']) ?></td>
                                    <td><?= formatThaiDate($res['created_at']) ?></td>
                                    <td style="text-align: center; font-weight: 700; font-size: 1.05rem;">
                                        <?= $res['score'] ?> / <?= $res['total_questions'] ?>
                                    </td>
                                    <td style="text-align: center; font-weight: 700; color: <?= $isPassed ? '#10b981' : '#ef4444' ?>;">
                                        <?= round($percent, 1) ?>%
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($isPassed): ?>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669;">
                                                <i class="fa-solid fa-circle-check mr-1"></i> ผ่านเกณฑ์
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626;">
                                                <i class="fa-solid fa-circle-xmark mr-1"></i> ไม่ผ่าน
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
