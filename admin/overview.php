<?php
require_once __DIR__ . '/header.php';
?>
<?php
                // Fetch stats counts
                $numUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $numCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
                $numEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
                $numQuizzesTaken = $pdo->query("SELECT COUNT(*) FROM quiz_results")->fetchColumn();
                
                // Fetch user counts by role
                $numAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                $numTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
                $numStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
            ?>
            <div style="margin-bottom: 30px;">
                <h2 style="font-size: 1.8rem; font-weight: 800;"><i class="fa-solid fa-chart-pie"></i> ภาพรวมของระบบ Fin-Course</h2>
                <p style="color: var(--text-secondary);">สถิติการใช้งาน ข้อมูลรายวิชา และผลรวมข้อมูลการสอบวิเคราะห์ประสิทธิภาพ</p>
            </div>

            <!-- Stats grid -->
            <div class="stat-grid">
                <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(0, 242, 254, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
                    <div class="stat-icon" style="background: rgba(0, 242, 254, 0.15); color: var(--accent);"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="stat-num"><?= $numUsers ?> คน</div>
                        <div class="stat-label">จำนวนผู้สมัครทั้งหมด</div>
                    </div>
                </div>
                <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(177, 86, 255, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
                    <div class="stat-icon" style="background: rgba(177, 86, 255, 0.15); color: var(--accent-purple);"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div>
                        <div class="stat-num"><?= $numCourses ?> วิชา</div>
                        <div class="stat-label">จำนวนหลักสูตรทั้งหมด</div>
                    </div>
                </div>
                <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(56, 239, 125, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
                    <div class="stat-icon" style="background: rgba(56, 239, 125, 0.15); color: #38ef7d;"><i class="fa-solid fa-book-open-reader"></i></div>
                    <div>
                        <div class="stat-num"><?= $numEnrollments ?> ครั้ง</div>
                        <div class="stat-label">สถิติการสมัครเรียนสะสม</div>
                    </div>
                </div>
                <div class="glass-card stat-card" style="background: linear-gradient(135deg, rgba(255, 159, 67, 0.05) 0%, rgba(255, 255, 255, 0.8) 100%);">
                    <div class="stat-icon" style="background: rgba(255, 159, 67, 0.15); color: #ff9f43;"><i class="fa-solid fa-file-signature"></i></div>
                    <div>
                        <div class="stat-num"><?= $numQuizzesTaken ?> ครั้ง</div>
                        <div class="stat-label">แบบทดสอบที่ทำไปแล้ว</div>
                    </div>
                </div>
            </div>

            <!-- Role and Activity lists -->
            <div class="grid-2" style="margin-top: 30px;">
                <!-- Left: user type analysis -->
                <div class="glass-card">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; color: var(--accent);"><i class="fa-solid fa-users-gear"></i> สัดส่วนประเภทสมาชิก</h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 12px; border-radius: 12px;">
                            <span>👨‍💻 ผู้เรียน (Student)</span>
                            <span class="badge badge-student" style="font-size: 0.9rem;"><?= $numStudents ?> คน</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 12px; border-radius: 12px;">
                            <span>🧑‍🏫 ผู้สอน (Teacher)</span>
                            <span class="badge badge-teacher" style="font-size: 0.9rem;"><?= $numTeachers ?> คน</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02); padding: 12px; border-radius: 12px;">
                            <span>🛡️ ผู้ดูแลระบบ (Admin)</span>
                            <span class="badge badge-admin" style="font-size: 0.9rem;"><?= $numAdmins ?> คน</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Latest Quiz Attempts -->
                <div class="glass-card">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; color: var(--accent-purple);"><i class="fa-solid fa-ranking-star"></i> กิจกรรมการทำแบบทดสอบล่าสุด</h3>
                    <?php
                        $stmt = $pdo->query("SELECT r.*, u.fullname as student_name, c.title as course_title FROM quiz_results r JOIN users u ON r.student_id = u.id JOIN courses c ON r.course_id = c.id ORDER BY r.id DESC LIMIT 5");
                        $latestQuizzes = $stmt->fetchAll();
                    ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ชื่อนักเรียน</th>
                                    <th>รายวิชา</th>
                                    <th>วันที่/เวลาสอบ</th>
                                    <th style="text-align: center;">คะแนนสอบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($latestQuizzes) === 0): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 20px;">ยังไม่มีผลการสอบส่งเข้ามาร่วมประเมินในขณะนี้</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($latestQuizzes as $q): ?>
                                        <tr>
                                            <td style="font-weight: 600;"><?= htmlspecialchars($q['student_name']) ?></td>
                                            <td><?= htmlspecialchars($q['course_title']) ?></td>
                                            <td><?= formatThaiDate($q['created_at']) ?></td>
                                            <td style="text-align: center; font-weight: 700; color: <?= ($q['score']/$q['total_questions'] >= 0.5) ? '#38ef7d' : '#ff9966' ?>;">
                                                <?= $q['score'] ?> / <?= $q['total_questions'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
<?php
require_once __DIR__ . '/footer.php';
?>