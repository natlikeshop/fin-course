<?php
// login.php
require_once __DIR__ . '/../config.php';

// If already logged in, redirect
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'admin') {
        header("Location: index.php?page=admin_overview");
    } elseif ($user['role'] === 'teacher') {
        header("Location: index.php?page=teacher_courses");
    } else {
        header("Location: index.php?page=home");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับคุณ ' . $user['fullname']
            ];
            
            if ($user['role'] === 'admin') {
                header("Location: index.php?page=admin_overview");
            } elseif ($user['role'] === 'teacher') {
                header("Location: index.php?page=teacher_courses");
            } else {
                header("Location: index.php?page=home");
            }
            exit;
        } else {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="auth-wrapper">
        <div class="glass-card auth-card animate-slide-in">
            <div class="auth-header">
                <h2 class="auth-title">เข้าสู่ระบบ <span>Fin-Course</span></h2>
                <p class="auth-subtitle">ระบบเรียนรู้ออนไลน์ทางการเงินที่มีประสิทธิภาพสูง</p>
            </div>
            
            <?php if ($error): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เข้าสู่ระบบไม่สำเร็จ',
                            text: '<?= addslashes($error) ?>',
                            confirmButtonColor: '#0ea5e9',
                            confirmButtonText: 'ตกลง',
                            background: '#ffffff',
                            color: '#1e293b',
                            backdrop: `rgba(0, 0, 0, 0.4)`,
                            customClass: {
                                popup: 'glass-card'
                            }
                        });
                    });
                </script>
            <?php endif; ?>
            
            <form action="index.php?page=login" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-user"></i></span>
                        <input type="text" id="username" name="username" class="form-input" placeholder="กรอกชื่อผู้ใช้ของคุณ" style="padding-left: 45px;" required>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" class="form-label">รหัสผ่าน (Password)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-input" placeholder="กรอกรหัสผ่านของคุณ" style="padding-left: 45px;" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; border-radius: 12px;">
                    <i class="fa-solid fa-right-to-bracket mr-1"></i> ยืนยันเข้าสู่ระบบ
                </button>
            </form>
            
            <div style="margin-top: 25px; text-align: center; font-size: 0.9rem; color: var(--text-secondary);">
                ยังไม่มีบัญชีผู้ใช้งาน? <a href="index.php?page=register" style="color: var(--accent); text-decoration: none; font-weight: 600;">สมัครสมาชิกใหม่ที่นี่</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
