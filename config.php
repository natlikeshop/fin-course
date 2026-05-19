<?php
// config.php - System Configurations & Database Connection

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'fin_course';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper: Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Get logged in user data
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Helper: Require authentication
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'กรุณาเข้าสู่ระบบก่อนใช้งาน'];
        header("Location: index.php?page=login");
        exit;
    }
}

// Helper: Require specific role
function requireRole($roles) {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], (array)$roles)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'];
        header("Location: index.php?page=home");
        exit;
    }
}

// Helper: Format Thai Date
function formatThaiDate($dateStr) {
    $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $time = strtotime($dateStr);
    $day = date('j', $time);
    $month = $months[(int)date('m', $time)];
    $year = date('Y', $time) + 543;
    $hourMin = date('H:i', $time);
    return "$day $month $year ($hourMin น.)";
}

// Helper: Render Flash Message
function renderFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        $type = $msg['type'];
        $text = addslashes($msg['text']);
        unset($_SESSION['flash_message']);
        
        $icon = ($type === 'error') ? 'error' : 'success';
        
        echo "
        <script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: '$icon',
                    title: '$text',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#1e293b',
                    customClass: {
                        popup: 'glass-card'
                    },
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
            });
        </script>
        ";
    }
}
?>
