-- Database initialization for Fin-Course
CREATE DATABASE IF NOT EXISTS fin_course CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fin_course;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    teacher_id INT NOT NULL,
    thumbnail VARCHAR(255) DEFAULT 'default_course.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lessons Table
CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT,
    video_url VARCHAR(255) DEFAULT NULL,
    document_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Quizzes Table (Questions per Course)
CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL, -- 'A', 'B', 'C', 'D'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Quiz Results Table
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert Seed Data (Passwords are hashed using PHP password_hash('password123', PASSWORD_DEFAULT))
-- Hashed password for 'password123': $2y$10$wE99J.mDMsQyRz8qjA/eEepv69fA26bMepxJcEqb1dDfsM8L72fB.
INSERT INTO users (id, fullname, username, email, password, role) VALUES
(1, 'ผู้ดูแลระบบ ฟินคอร์ส', 'admin', 'admin@fincourse.com', '$2y$10$wE99J.mDMsQyRz8qjA/eEepv69fA26bMepxJcEqb1dDfsM8L72fB.', 'admin'),
(2, 'อาจารย์ สมชาย ใจดี', 'teacher', 'somchai@fincourse.com', '$2y$10$wE99J.mDMsQyRz8qjA/eEepv69fA26bMepxJcEqb1dDfsM8L72fB.', 'teacher'),
(3, 'สมศรี เรียนดี', 'student', 'somsri@fincourse.com', '$2y$10$wE99J.mDMsQyRz8qjA/eEepv69fA26bMepxJcEqb1dDfsM8L72fB.', 'student')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Seed Courses
INSERT INTO courses (id, title, description, teacher_id, thumbnail) VALUES
(1, 'เริ่มต้นการบริหารการเงินส่วนบุคคล (Personal Finance 101)', 'เรียนรู้วิธีการจัดการรายรับ-รายจ่าย การออมเงิน และการวางแผนทางการเงินเพื่อเป้าหมายในชีวิตอย่างเป็นระบบและปลอดภัยสูงสุด', 2, 'finance101.jpg'),
(2, 'การลงทุนพื้นฐานสำหรับมือใหม่ (Introduction to Investing)', 'เจาะลึกตลาดหุ้น กองทุนรวม สินทรัพย์ดิจิทัล และหลักการจัดพอร์ตการลงทุน (Asset Allocation) เพื่อให้คุณเริ่มต้นลงทุนได้อย่างมั่นใจ', 2, 'investing101.jpg')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Seed Lessons
INSERT INTO lessons (id, course_id, title, content, video_url, document_path) VALUES
(1, 1, 'ความสำคัญของการวางแผนการเงิน', 'การวางแผนการเงินส่วนบุคคลคือพื้นฐานของการสร้างความมั่นคงในชีวิต การออมเงินอย่างมีระบบจะช่วยให้เรามีเงินสำรองในยามฉุกเฉินและสามารถบรรลุเป้าหมายทางการเงินในอนาคตได้\n\nหัวข้อการเรียนรู้:\n1. สมการความมั่งคั่ง (รายได้ - เงินออม = รายจ่าย)\n2. การสำรองเงินฉุกเฉิน 3-6 เท่าของค่าใช้จ่ายรายเดือน\n3. เงินเฟ้อและการลดลงของอำนาจซื้อ', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'finance_l1.pdf'),
(2, 1, 'การจัดการรายรับ-รายจ่าย และการจัดทำงบประมาณ', 'เรียนรู้วิธีการทำบัญชีรายรับ-รายจ่ายอย่างง่าย โดยใช้หลักการ 50-30-20 (Needs 50%, Wants 30%, Savings 20%) เพื่อช่วยควบคุมค่าใช้จ่ายและเพิ่มเงินออมในแต่ละเดือน', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'finance_l2.pdf'),
(3, 2, 'ทำความเข้าใจกับสินทรัพย์ทางการเงินประเภทต่าง ๆ', 'ก่อนการลงทุน คุณจำเป็นต้องรู้จักตราสารหนี้ ตราสารทุน (หุ้น) และกองทุนรวม รวมถึงความเสี่ยงและผลตอบแทนที่คาดหวังของแต่ละประเภทสินทรัพย์ เพื่อการเลือกสัดส่วนที่เหมาะสม', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'invest_l1.pdf'),
(4, 2, 'ความรู้เบื้องต้นเกี่ยวกับกองทุนรวม (Mutual Funds)', 'กองทุนรวมเป็นเครื่องมือทางการเงินที่ยอดเยี่ยมสำหรับมือใหม่ เนื่องจากมีผู้จัดการกองทุนมืออาชีพคอยดูแลการลงทุนให้ และยังสามารถช่วยกระจายความเสี่ยงได้อย่างมีประสิทธิภาพ', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'invest_l2.pdf')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Seed Quizzes (Questions for Course 1)
INSERT INTO quizzes (id, course_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(1, 1, 'สูตรการออมเงินที่ดีและเหมาะสมที่สุดเพื่อความมั่งคั่งในอนาคตคือข้อใด?', 'รายได้ - รายจ่าย = เงินออม', 'รายได้ - เงินออม = รายจ่าย', 'รายได้ + รายจ่าย = เงินออม', 'รายจ่าย - รายได้ = เงินออม', 'B'),
(2, 1, 'เงินสำรองฉุกเฉินควรมีปริมาณเท่าใดของค่าใช้จ่ายรายเดือน?', '1 เท่า', '2 เท่า', '3 ถึง 6 เท่า', '12 เท่าขึ้นไปเท่านั้น', 'C'),
(3, 1, 'กฎการแบ่งเงิน 50-30-20 กำหนดให้ 20% ของรายได้นำไปใช้ทำอะไร?', 'ค่าใช้จ่ายจำเป็น (Needs)', 'ความต้องการส่วนตัว (Wants)', 'การลงทุนและการออม (Savings)', 'ค่าความบันเทิงและการท่องเที่ยว', 'C')
ON DUPLICATE KEY UPDATE id=id;

-- Insert Seed Quizzes (Questions for Course 2)
INSERT INTO quizzes (id, course_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES
(4, 2, 'ข้อใดอธิบายเกี่ยวกับกองทุนรวม (Mutual Funds) ได้ถูกต้องที่สุด?', 'การลงทุนในสินทรัพย์เสี่ยงสูงโดยไม่มีผู้ดูแล', 'การระดมทุนจากผู้ลงทุนรายย่อยเพื่อนำไปลงทุนโดยผู้จัดการกองทุนมืออาชีพ', 'การเก็งกำไรในตลาดหุ้นรายวันด้วยโปรแกรมอัตโนมัติ', 'การกู้เงินมาซื้อตราสารหนี้ภาครัฐ', 'B'),
(5, 2, 'การจัดพอร์ตการลงทุน (Asset Allocation) มีวัตถุประสงค์หลักเพื่ออะไร?', 'เพื่อตัดขาดทุนทันทีเมื่อราคาหุ้นลดลง', 'เพื่อกระจายความเสี่ยงและเพิ่มประสิทธิภาพของผลตอบแทน', 'เพื่อเลี่ยงการเสียภาษีเงินได้จากการลงทุน', 'เพื่อให้ได้รับปันผลรายสัปดาห์', 'B')
ON DUPLICATE KEY UPDATE id=id;
