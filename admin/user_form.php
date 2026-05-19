<?php
require_once __DIR__ . '/header.php';

$id = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;
$user_data = null;
$error = '';

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user_data = $stmt->fetch();
    if (!$user_data) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลผู้ใช้งาน'];
        header("Location: index.php?page=admin_users");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (empty($fullname) || empty($username) || empty($email)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } elseif (!$is_edit && empty($password)) {
        $error = 'กรุณากำหนดรหัสผ่านสำหรับผู้ใช้งานใหม่';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } else {
        // Check for duplicate username or email (excluding current user if editing)
        $sql = "SELECT id FROM users WHERE (username = ? OR email = ?)";
        $params = [$username, $email];
        if ($is_edit) {
            $sql .= " AND id != ?";
            $params[] = $id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->fetch()) {
            $error = 'ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว';
        } else {
            if ($is_edit) {
                // Update
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $success = $stmt->execute([$fullname, $username, $email, $hashed, $role, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ? WHERE id = ?");
                    $success = $stmt->execute([$fullname, $username, $email, $role, $id]);
                }
                $message = 'ปรับปรุงข้อมูลผู้ใช้งานสำเร็จ';
            } else {
                // Create
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullname, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $success = $stmt->execute([$fullname, $username, $email, $hashed, $role]);
                $message = 'สร้างผู้ใช้งานใหม่สำเร็จ';
            }

            if ($success) {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => $message];
                echo "<script>window.location.href='index.php?page=admin_users';</script>";
                exit;
            } else {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
}

$title = $is_edit ? 'แก้ไขข้อมูลสมาชิก' : 'เพิ่มสมาชิกใหม่';
$icon = $is_edit ? 'fa-user-pen' : 'fa-user-plus';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h2 style="font-size: 1.8rem; font-weight: 800;"><i class="fa-solid <?= $icon ?>"></i> <?= $title ?></h2>
    <a href="index.php?page=admin_users" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ</a>
</div>

<?php if ($error): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 25px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="glass-card">
    <form action="" method="POST">
        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">ชื่อ - นามสกุล</label>
                <input type="text" name="fullname" class="form-input" value="<?= htmlspecialchars($user_data['fullname'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">ชื่อผู้ใช้ (Username)</label>
                <input type="text" name="username" class="form-input" value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" required>
            </div>
        </div>

        <div class="grid-2" style="grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">อีเมลติดต่อ (Email)</label>
                <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">รหัสผ่าน <?= $is_edit ? '(เว้นว่างหากไม่ต้องการเปลี่ยน)' : 'เริ่มต้น' ?></label>
                <input type="password" name="password" class="form-input" <?= $is_edit ? '' : 'required' ?>>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label">ระดับสิทธิ์ในการเข้าใช้งาน</label>
            <select name="role" class="form-input form-select" required>
                <?php
                $roles = [
                    'student' => 'Student - ผู้เรียนรู้',
                    'teacher' => 'Teacher - ผู้สอนในระบบ',
                    'admin' => 'Admin - ผู้ดูแลระบบความปลอดภัยสูงสุด'
                ];
                $current_role = $user_data['role'] ?? 'student';
                foreach ($roles as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= $current_role === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="index.php?page=admin_users" class="btn btn-outline" style="padding: 12px 30px;">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                <i class="fa-solid fa-save"></i> <?= $is_edit ? 'บันทึกการแก้ไข' : 'ยืนยันบันทึกสมาชิกใหม่' ?>
            </button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/footer.php';
?>
