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
    profile_photo VARCHAR(255) NULL,
    last_login DATETIME NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    password_updated BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    form_teacher_id INT,
    academic_year_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (form_teacher_id) REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id) ON DELETE CASCADE,
    UNIQUE (name, academic_year_id)
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL UNIQUE,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    contact_phone VARCHAR(30),
    motto VARCHAR(255),

    hero_heading VARCHAR(255) NULL,
    hero_subtext TEXT NULL,
    hero_image_path VARCHAR(255) NULL,
    about_text TEXT NULL,
    vision_text TEXT NULL,
    about_image VARCHAR(255) NULL,

    facebook_url VARCHAR(255) NULL,
    instagram_url VARCHAR(255) NULL,
    twitter_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,

    opening_date DATE,
    founded_year YEAR NULL,
    closing_date DATE,

    head_teacher_name VARCHAR(255),

    fee_boarding INT,
    fee_self_boarding INT,
    fee_uniform INT,
    fee_registration INT,
    include_fees BOOLEAN DEFAULT TRUE,

    whatsapp_number VARCHAR(20),
    sms_sender_id VARCHAR(20),

    mission_text TEXT NULL,
    values_text TEXT NULL,
    head_teacher_message TEXT NULL,
    head_teacher_photo VARCHAR(255) NULL,

    admissions_open TINYINT(1) NOT NULL DEFAULT 1,
    admissions_text TEXT NULL,
    map_query VARCHAR(255) NULL,
    admissions_link VARCHAR(255) NULL,

    notification_email VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE class_subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    UNIQUE KEY (class_id, subject_id, academic_year_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(academic_year_id)
);

CREATE TABLE class_subject_teacher (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_subject_id INT NOT NULL,
    teacher_id INT,
    FOREIGN KEY (class_subject_id) REFERENCES class_subject(id),
    FOREIGN KEY (teacher_id) REFERENCES users(user_id)
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
    'admin@123',
    1,
    0
);

CREATE TABLE IF NOT EXISTS school_gallery (
    gallery_id  INT AUTO_INCREMENT PRIMARY KEY,
    image_path  VARCHAR(255) NOT NULL,
    caption     VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    body            TEXT NOT NULL,
    is_published    TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS why_choose_us (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    body        TEXT NOT NULL,
    image_path  VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO why_choose_us (title, body, sort_order, is_active) VALUES
('Strong Academics', 'Excellent MSCE and JCE performance with committed teachers.', 1, 1),
('Science Laboratories', 'Hands-on practical learning in Biology, Chemistry and Physics.', 2, 1),
('ICT Education', 'Computer studies and digital skills for modern careers.', 3, 1),
('Discipline & Safety', 'A safe, respectful and well-supervised learning environment.', 4, 1);

