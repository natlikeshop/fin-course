<?php
// quiz.php - Interactive Course Evaluation
require_once __DIR__ . '/../config.php';

requireLogin();

$course_id = (int)($_GET['course_id'] ?? 0);
if ($course_id <= 0) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่ระบุรายวิชาสำหรับการทำข้อสอบ'];
    header("Location: index.php?page=home");
    exit;
}

// Fetch Course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลหลักสูตรที่คุณระบุ'];
    header("Location: index.php?page=home");
    exit;
}

// Fetch Quiz Questions
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY id ASC");
$stmt->execute([$course_id]);
$questions = $stmt->fetchAll();

$showResult = false;
$score = 0;
$totalQuestions = count($questions);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $totalQuestions > 0) {
    $student_id = $_SESSION['user_id'];
    
    // Evaluate answers
    foreach ($questions as $q) {
        $q_id = $q['id'];
        $user_answer = $_POST["question_$q_id"] ?? '';
        
        if (strtoupper($user_answer) === strtoupper($q['correct_option'])) {
            $score++;
        }
    }
    
    // Save to Database
    $stmt = $pdo->prepare("INSERT INTO quiz_results (student_id, course_id, score, total_questions) VALUES (?, ?, ?, ?)");
    $stmt->execute([$student_id, $course_id, $score, $totalQuestions]);
    
    $showResult = true;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <?php if ($showResult): ?>
        <!-- Animated Result Screen -->
        <div class="glass-card animate-slide-in" style="max-width: 580px; margin: 0 auto; text-align: center; padding: 50px 30px;">
            <?php 
                $percentage = ($score / $totalQuestions) * 100;
                $passed = $percentage >= 50;
            ?>
            
            <div style="width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 3rem; 
                background: <?= $passed ? 'var(--success-grad)' : 'var(--danger-grad)' ?>;
                box-shadow: 0 0 25px <?= $passed ? 'rgba(56, 239, 125, 0.4)' : 'rgba(255, 94, 98, 0.4)' ?>;">
                <i class="fa-solid <?= $passed ? 'fa-award' : 'fa-triangle-exclamation' ?>" style="color: #fff;"></i>
            </div>
            
            <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 10px;">
                <?= $passed ? 'ยินดีด้วย! คุณสอบผ่าน' : 'พยายามอีกนิด! คุณยังไม่ผ่านเกณฑ์' ?>
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 25px;">
                เกณฑ์การผ่านของแบบทดสอบคือ 50% ของข้อสอบทั้งหมด
            </p>
            
            <!-- Score numbers -->
            <div class="glass-card" style="background: rgba(255,255,255,0.6); border-radius: 16px; padding: 20px; margin-bottom: 35px; border-color: rgba(0,0,0,0.05);">
                <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">คะแนนที่คุณสอบได้</div>
                <div style="font-size: 3.5rem; font-weight: 800; color: <?= $passed ? '#38ef7d' : '#ff9966' ?>; margin: 5px 0;">
                    <?= $score ?> <span style="font-size: 1.5rem; color: var(--text-secondary); font-weight: 500;">/ <?= $totalQuestions ?> คะแนน</span>
                </div>
                <div style="font-weight: 700; font-size: 1.1rem;">คิดเป็นความถูกต้อง <?= round($percentage, 1) ?>%</div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="index.php?page=course_detail&id=<?= $course_id ?>" class="btn btn-outline" style="border-radius: 12px; padding: 12px 25px;">
                    <i class="fa-solid fa-arrow-left"></i> กลับสู่หน้าบทเรียน
                </a>
                <a href="index.php?page=quiz&course_id=<?= $course_id ?>" class="btn btn-primary" style="border-radius: 12px; padding: 12px 25px;">
                    <i class="fa-solid fa-rotate-right"></i> ทำแบบทดสอบอีกครั้ง
                </a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Quiz Form Screen -->
        <div class="glass-card animate-slide-in" style="max-width: 800px; margin: 0 auto;">
            <div class="quiz-header">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--accent); text-transform: uppercase;">แบบทดสอบเพื่อการประเมินผล</span>
                    <h1 style="font-size: 1.8rem; font-weight: 800; margin-top: 5px;"><?= htmlspecialchars($course['title']) ?></h1>
                </div>
                <div style="text-align: right; color: var(--text-secondary); font-size: 0.9rem;">
                    <div>จำนวนข้อสอบทั้งหมด</div>
                    <div style="font-size: 1.4rem; font-weight: 800; color: var(--text-primary);"><?= $totalQuestions ?> ข้อ</div>
                </div>
            </div>
            
            <?php if ($totalQuestions === 0): ?>
                <div style="text-align: center; padding: 50px 0;">
                    <i class="fa-solid fa-hourglass-empty" style="font-size: 3.5rem; color: var(--text-secondary); margin-bottom: 20px;"></i>
                    <h3>ยังไม่มีคำถามในวิชานี้</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 25px;">ผู้สอนหรือผู้ดูแลระบบยังไม่ได้อัปโหลดชุดข้อสอบ โปรดกลับมาใหม่อีกครั้งภายหลัง</p>
                    <a href="index.php?page=course_detail&id=<?= $course_id ?>" class="btn btn-primary">กลับสู่บทเรียน</a>
                </div>
            <?php else: ?>
                <form action="index.php?page=quiz&course_id=<?= $course_id ?>" method="POST">
                    
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="quiz-q-card" style="margin-bottom: 40px; <?= $index < $totalQuestions - 1 ? 'border-bottom: 1px solid var(--border-color); padding-bottom: 30px;' : '' ?>">
                            <h3 style="font-size: 1.15rem; font-weight: 600; line-height: 1.5; margin-bottom: 20px; display: flex; gap: 10px;">
                                <span style="background: var(--primary-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800;">ข้อที่ <?= $index + 1 ?>.</span>
                                <span><?= htmlspecialchars($q['question']) ?></span>
                            </h3>
                            
                            <div class="quiz-options">
                                <!-- Option A -->
                                <input type="radio" id="q_<?= $q['id'] ?>_A" name="question_<?= $q['id'] ?>" value="A" class="quiz-option-input" required>
                                <label for="q_<?= $q['id'] ?>_A" class="quiz-option-label">
                                    <span class="quiz-option-letter">ก</span>
                                    <span><?= htmlspecialchars($q['option_a']) ?></span>
                                </label>
                                
                                <!-- Option B -->
                                <input type="radio" id="q_<?= $q['id'] ?>_B" name="question_<?= $q['id'] ?>" value="B" class="quiz-option-input">
                                <label for="q_<?= $q['id'] ?>_B" class="quiz-option-label">
                                    <span class="quiz-option-letter">ข</span>
                                    <span><?= htmlspecialchars($q['option_b']) ?></span>
                                </label>
                                
                                <!-- Option C -->
                                <input type="radio" id="q_<?= $q['id'] ?>_C" name="question_<?= $q['id'] ?>" value="C" class="quiz-option-input">
                                <label for="q_<?= $q['id'] ?>_C" class="quiz-option-label">
                                    <span class="quiz-option-letter">ค</span>
                                    <span><?= htmlspecialchars($q['option_c']) ?></span>
                                </label>
                                
                                <!-- Option D -->
                                <input type="radio" id="q_<?= $q['id'] ?>_D" name="question_<?= $q['id'] ?>" value="D" class="quiz-option-input">
                                <label for="q_<?= $q['id'] ?>_D" class="quiz-option-label">
                                    <span class="quiz-option-letter">ง</span>
                                    <span><?= htmlspecialchars($q['option_d']) ?></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
                        <a href="index.php?page=course_detail&id=<?= $course_id ?>" class="btn btn-outline" style="border-radius: 12px;">ยกเลิก</a>
                        <button type="submit" class="btn btn-secondary" style="border-radius: 12px; padding: 12px 35px; box-shadow: var(--shadow-purple);">
                            <i class="fa-solid fa-paper-plane"></i> ส่งคำตอบและส่งข้อสอบ
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
