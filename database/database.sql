-- 1. SCHOOLS
CREATE TABLE IF NOT EXISTS schools (
    school_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. ROLES
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. PERMISSIONS
CREATE TABLE IF NOT EXISTS permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. ROLE_PERMISSIONS (many-to-many)
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
);

-- 5. USERS
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT,
    full_name VARCHAR(255),
    country_code VARCHAR(5),
    phone_number VARCHAR(15),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password_updated BOOLEAN NOT NULL DEFAULT 0,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL,

    INDEX idx_school_user (school_id),
    INDEX idx_role_user (role_id)
);

-- 6. CLASSES
CREATE TABLE IF NOT EXISTS classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,

    UNIQUE KEY uniq_class_per_school (school_id, name),
    INDEX idx_school_class (school_id)
);

-- 7. SUBJECTS
CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    teacher_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,

    UNIQUE KEY uq_school_subject_name (school_id, subject_name),
    UNIQUE KEY uq_school_subject_code (school_id, subject_code),

    INDEX idx_school_subject (school_id)
);


-- 8. STUDENTS
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    class_id INT,
    date_of_birth DATE,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0
    contact_number VARCHAR(15),
    address TEXT,
    username VARCHAR(255),
    password VARCHAR (255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_school_student (school_id),
    INDEX idx_class_student (class_id)
);

-- 9. EXAMS
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    exam_name VARCHAR(100) NOT NULL,
    term VARCHAR(50) NOT NULL,
    academic_year VARCHAR(9) NOT NULL CHECK (academic_year LIKE '____-____'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    UNIQUE KEY uq_exam_per_school (school_id, exam_name, term, academic_year),
    INDEX idx_school_exam (school_id)
);


-- 10. GRADES
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    exam_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    student_id INT NOT NULL,
    marks VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,

    UNIQUE (exam_id, class_id, subject_id, student_id),
    INDEX idx_school_grades (school_id),
    INDEX idx_exam_class_subject (exam_id, class_id, subject_id),
    INDEX idx_student_grades (student_id)
);

-- 11. GRADING CRITERIA
CREATE TABLE IF NOT EXISTS grading_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    level VARCHAR(50) NOT NULL,
    grade VARCHAR(10) NOT NULL,
    min_marks INT NOT NULL,
    max_marks INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    form_teacher_remark VARCHAR(255),
    head_teacher_remark VARCHAR(255),

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    INDEX idx_school_level (school_id, level)
);

-- 12. PARENT-STUDENT RELATIONSHIP
CREATE TABLE IF NOT EXISTS parent_student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    relationship VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (parent_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,

    UNIQUE (parent_id, student_id)
);

-- 13. SCHOOL SETTINGS
CREATE TABLE IF NOT EXISTS school_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL UNIQUE,
    school_name VARCHAR(255),
    logo_path VARCHAR(255),
    address TEXT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    motto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS report_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL UNIQUE,
    opening_date DATE,
    closing_date DATE,
    head_teacher_name VARCHAR(255),
    fee_boarding INT,
    fee_self_boarding INT,
    fee_uniform INT,
    fee_registration INT,
    include_fees BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS communication_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL UNIQUE,
    whatsapp_number VARCHAR(20),
    sms_sender_id VARCHAR(20),
    notification_email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE
);

CREATE TABLE class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    UNIQUE (class_id, subject_id)
);

-- 18. CLASS-SUBJECT-TEACHER LINK
CREATE TABLE class_subject_teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id)
);
-- ROLES
INSERT INTO roles (name, description) VALUES
('superadmin', 'System owner with full access to everything'),
('schooladmin', 'Manages a single school – students, classes, users, etc.'),
('teacher', 'Manages assigned subjects, students, and grades'),
('student', 'Can view their own grades and reports');
-- PERMISSIONS
INSERT INTO permissions (name, description) VALUES
('manage_schools', 'Can add or remove schools'),
('manage_users', 'Can add or manage user accounts'),
('manage_classes', 'Can create or edit classes'),
('manage_subjects', 'Can assign or manage subjects'),
('manage_students', 'Can enroll or update students'),
('manage_exams', 'Can set exams and terms'),
('enter_grades', 'Can enter student grades'),
('view_reports', 'Can view report cards'),
('view_own_child', 'Can view own child’s progress only');

-- Superadmin: all permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM roles r, permissions p
WHERE r.name = 'superadmin';

-- School Admin: most permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM roles r, permissions p
WHERE r.name = 'schooladmin'
  AND p.name IN (
    'manage_users', 'manage_classes', 'manage_subjects',
    'manage_students', 'manage_exams', 'enter_grades', 'view_reports'
);

-- Teacher: limited permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM roles r, permissions p
WHERE r.name = 'teacher'
  AND p.name IN ('manage_exams', 'enter_grades', 'view_reports');

-- Parent: only view their child
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM roles r, permissions p
WHERE r.name = 'parent'
  AND p.name = 'view_own_child';

  INSERT INTO users (
    school_id,
    full_name,
    country_code,
    phone_number,
    username,
    email,
    password,
    role_id,
    password_updated
) VALUES (
    NULL,
    'System SuperAdmin',
    '+265',
    '0999000000',
    'superadmin',
    'superadmin@example.com',
    '12345678',
    1,
    0
);

