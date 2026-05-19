<?php
require_once __DIR__ . '/header.php';
$action = $_GET['action'] ?? '';
$error = '';

if ($action === 'edit_role' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'] ?? 'student';
    
    if ($user_id === (int)$admin['id']) {
        $error = 'คุณไม่สามารถเปลี่ยนสิทธิ์ระดับการใช้งานของบัญชีตัวเองได้';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($stmt->execute([$new_role, $user_id])) {
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'เปลี่ยนสิทธิ์ผู้ใช้เสร็จเรียบร้อย'];
            header("Location: index.php?page=admin_users");
            exit;
        } else {
            $error = 'เกิดข้อผิดพลาดในการเปลี่ยนสิทธิ์';
        }
    }
}

if ($action === 'delete_user') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    if ($user_id === (int)$admin['id']) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'คุณไม่สามารถลบบัญชีผู้ใช้ของคุณเองได้'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'ลบผู้ใช้รายดังกล่าวออกจากระบบเรียบร้อย'];
    }
    header("Location: index.php?page=admin_users");
    exit;
}
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                <h2 style="font-size: 1.8rem; font-weight: 800;"><i class="fa-solid fa-users"></i> การจัดการรายชื่อสมาชิก</h2>
                <a href="index.php?page=admin_user_form" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> เพิ่มสมาชิกใหม่เข้าระบบ</a>
            </div>

            <!-- Users List Table -->
            <div class="glass-card" style="margin-bottom: 40px;">
                <?php
                    $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>ชื่อผู้ใช้ (Username)</th>
                                <th>อีเมล</th>
                                <th style="text-align: center;">บทบาท</th>
                                <th style="text-align: center; width: 320px;">ปรับแก้สิทธิ์ / จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($u['fullname']) ?></td>
                                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td style="text-align: center;">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge badge-admin"><i class="fa-solid fa-user-shield"></i> Admin</span>
                                        <?php elseif ($u['role'] === 'teacher'): ?>
                                            <span class="badge badge-teacher"><i class="fa-solid fa-chalkboard-user"></i> Teacher</span>
                                        <?php else: ?>
                                            <span class="badge badge-student"><i class="fa-solid fa-user"></i> Student</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                            <!-- Edit Role Inline Form -->
                                            <form action="index.php?page=admin_users&action=edit_role" method="POST" style="display: flex; gap: 5px;">
                                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                <select name="role" class="form-input btn-sm" style="width: 100px; padding: 4px 8px; margin-bottom: 0; background: rgba(0,0,0,0.03); cursor: pointer;" onchange="this.form.submit()">
                                                    <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                                    <option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </form>

                                            <!-- Edit Link -->
                                            <a href="index.php?page=admin_user_form&id=<?= $u['id'] ?>" class="btn btn-sm" style="padding: 4px 10px; background: #f39c12; color: #fff; border-radius: 8px; font-size: 0.85rem;">
                                                <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                                            </a>
                                            
                                            <!-- Delete Link -->
                                            <?php if ($u['id'] !== $admin['id']): ?>
                                                <a href="index.php?page=admin_users&action=delete_user&user_id=<?= $u['id'] ?>" 
                                                   onclick="return confirm('คุณแน่ใจว่าต้องการลบสมาชิกรายนี้ออกจากระบบ? ข้อมูลการลงทะเบียนเรียนและประวัติคะแนนของรายนี้จะสูญหายถาวร')" 
                                                   class="btn btn-danger btn-sm" style="padding: 4px 10px; background: #e74c3c; border-radius: 8px; font-size: 0.85rem;">
                                                    <i class="fa-solid fa-user-minus"></i> ลบ
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<?php
require_once __DIR__ . '/footer.php';
?>