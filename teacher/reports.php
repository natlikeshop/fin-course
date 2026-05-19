<?php
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 30px;">
    <h2 style="font-size: 1.8rem; font-weight: 800;"><i class="fa-solid fa-chart-line"></i> คะแนนสอบของนักเรียน</h2>
    <p style="color: var(--text-secondary);">รายงานสรุปคะแนนประเมินผลของนักเรียนที่ลงทะเบียนเรียนและสอบวิชาเรียนของคุณ</p>
</div>

<div class="glass-card">
    <?php 
        $stmt = $pdo->prepare("SELECT r.*, u.fullname as student_name, u.email as student_email, c.title as course_title FROM quiz_results r JOIN users u ON r.student_id = u.id JOIN courses c ON r.course_id = c.id WHERE c.teacher_id = ? ORDER BY r.created_at DESC");
        $stmt->execute([$teacher['id']]);
        $reports = $stmt->fetchAll();
    ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ชื่อนักเรียน</th>
                    <th>อีเมล</th>
                    <th>วิชาเรียน</th>
                    <th>วันที่ทำการสอบ</th>
                    <th style="text-align: center;">คะแนนสอบ</th>
                    <th style="text-align: center;">สถานะประเมิน</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($reports) === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">ยังไม่มีรายงานผลคะแนนสอบของนักเรียนในวิชาของคุณ</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $rep): ?>
                        <?php 
                            $percent = ($rep['total_questions'] > 0) ? ($rep['score'] / $rep['total_questions']) * 100 : 0;
                            $passed = $percent >= 50;
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($rep['student_name']) ?></td>
                            <td><?= htmlspecialchars($rep['student_email']) ?></td>
                            <td><?= htmlspecialchars($rep['course_title']) ?></td>
                            <td><?= formatThaiDate($rep['created_at']) ?></td>
                            <td style="text-align: center; font-weight: 700; color: <?= $passed ? '#38ef7d' : '#ff9966' ?>;">
                                <?= $rep['score'] ?> / <?= $rep['total_questions'] ?> (<?= round($percent, 1) ?>%)
                            </td>
                            <td style="text-align: center;">
                                <?php if ($passed): ?>
                                    <span class="badge" style="background: rgba(56, 239, 125, 0.15); color: #38ef7d;"><i class="fa-solid fa-check"></i> ผ่านเกณฑ์</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(255, 94, 98, 0.15); color: #ff7675;"><i class="fa-solid fa-xmark"></i> ตกเกณฑ์</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
