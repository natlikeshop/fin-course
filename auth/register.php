<?php
// register.php
require_once __DIR__ . '/../config.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header("Location: index.php?page=home");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    // Validate role is either 'student' or 'teacher' (admins cannot register this way)
    if ($role !== 'student' && $role !== 'teacher') {
        $role = 'student';
    }
    
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'กรุณากรอกอีเมลที่ถูกต้อง';
    } elseif ($password !== $confirm_password) {
        $error = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
    } elseif (strlen($password) < 6) {
        $error = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'อีเมลนี้ถูกใช้งานแล้ว';
            } else {
                // Register User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$fullname, $username, $email, $hashed_password, $role])) {
                    // Auto Login
                    $new_id = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $_SESSION['fullname'] = $fullname;
                    
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'text' => 'สมัครสมาชิกและเข้าสู่ระบบเรียบร้อยแล้ว ยินดีต้อนรับ!'
                    ];
                    
                    if ($role === 'teacher') {
                        header("Location: index.php?page=teacher_courses");
                    } else {                        header("Location: index.php?page=home");
                    }
                    exit;
                } else {
                    $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="glass-card auth-card animate-slide-in" style="max-width: 540px;">
            <div class="auth-header">
                <h2 class="auth-title">สมัครสมาชิกใหม่</h2>
                <p class="auth-subtitle">เข้าร่วมเป็นส่วนหนึ่งของคอมมูนิตี้ Fin-Course เพื่อยกระดับความรู้คุณวันนี้</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="index.php?page=register" method="POST">
                <div class="form-group">
                    <label for="fullname" class="form-label">ชื่อ - นามสกุล (Fullname)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-id-card"></i></span>
                        <input type="text" id="fullname" name="fullname" class="form-input" placeholder="ภาษาไทยหรืออังกฤษ" style="padding-left: 45px;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="username" name="username" class="form-input" placeholder="สำหรับใช้ในการ Login เข้าระบบ" style="padding-left: 45px;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">อีเมล (Email)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-input" placeholder="example@domain.com" style="padding-left: 45px;" required>
                    </div>
                </div>

                <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="ขั้นต่ำ 6 ตัวอักษร" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="role" class="form-label">ประเภทผู้ใช้งาน</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-users-gear"></i></span>
                        <select id="role" name="role" class="form-input form-select" style="padding-left: 45px; cursor: pointer;">
                            <option value="student" selected>ผู้เรียน (Student) - ศึกษาเนื้อหาและทำข้อสอบ</option>
                            <option value="teacher">ผู้สอน (Teacher) - จัดการเนื้อหาหลักสูตรของตนเอง</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: 12px;">
                    <i class="fa-solid fa-user-plus mr-1"></i> สมัครสมาชิกบัญชีใหม่
                </button>
            </form>
            
            <div style="margin-top: 25px; text-align: center; font-size: 0.9rem; color: var(--text-secondary);">
                มีบัญชีอยู่แล้ว? <a href="index.php?page=login" style="color: var(--accent-purple); text-decoration: none; font-weight: 600;">เข้าสู่ระบบที่นี่</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
