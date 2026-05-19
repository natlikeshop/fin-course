<?php
// logout.php
require_once __DIR__ . '/../config.php';

$_SESSION = [];
session_destroy();

session_start();
$_SESSION['flash_message'] = [
    'type' => 'success',
    'text' => 'ออกจากระบบเรียบร้อยแล้ว พบกันใหม่โอกาสหน้า!'
];

header("Location: index.php?page=login");
exit;
?>
