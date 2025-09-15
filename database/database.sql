-- ===========================================
-- NEXTGEN SCHOOL MANAGEMENT - CLEAN STRUCTURE
-- ===========================================
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE academic_years (
    academic_year_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL UNIQUE, -- e.g. '2025-2026'
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current TINYINT(1) DEFAULT 0
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    country_code VARCHAR(5),
    phone_number VARCHAR(15),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password_updated BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL
);

CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    teacher_id INT,
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id) ON DELETE CASCADE,
    UNIQUE (name, academic_year_id)
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL UNIQUE,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    teacher_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    class_id INT,
    date_of_birth DATE,
    contact_number VARCHAR(15),
    address TEXT,
    username VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100) NOT NULL,
    term VARCHAR(50) NOT NULL,
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_exam (exam_name, term, academic_year_id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id) ON DELETE CASCADE
);


CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    student_id INT NOT NULL,
    marks VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    UNIQUE (exam_id, class_id, subject_id, student_id)
);

CREATE TABLE grading_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(50) NOT NULL,
    grade VARCHAR(10) NOT NULL,
    min_marks INT NOT NULL,
    max_marks INT NOT NULL,
    description VARCHAR(255) NULL,
    form_teacher_remark VARCHAR(255),
    head_teacher_remark VARCHAR(255)
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(255),
    logo_path VARCHAR(255),
    address TEXT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    motto VARCHAR(255),
    opening_date DATE,
    closing_date DATE,
    head_teacher_name VARCHAR(255),
    fee_boarding INT,
    fee_self_boarding INT,
    fee_uniform INT,
    fee_registration INT,
    include_fees BOOLEAN DEFAULT TRUE,
    whatsapp_number VARCHAR(20),
    sms_sender_id VARCHAR(20),
    notification_email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE class_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id)
);

CREATE TABLE class_subject_teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (class_id, subject_id)
);

CREATE TABLE student_enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    promoted BOOLEAN DEFAULT 0,
    status ENUM('active','withdrawn','completed') DEFAULT 'active',
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id) ON DELETE CASCADE,
    UNIQUE (student_id, academic_year_id)
);

-- ===================
-- SEED DATA
-- ===================
INSERT INTO roles (name, description) VALUES
('admin', 'Manages the school – students, classes, users, etc.'),
('teacher', 'Manages assigned subjects, students, and grades'),
('student', 'Can view their own grades and reports');

INSERT INTO users (full_name, country_code, phone_number, username, email, password, role_id, password_updated)
VALUES (
    'System Administrator',
    '+265',
    '999000000',
    'admin',
    'admin@nextgenschools.mw',
    'admin123',
    1,
    0
);
