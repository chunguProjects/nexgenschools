

CREATE TABLE IF NOT EXISTS `users` (
    user_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255),
    country_code VARCHAR(5),
    phone_number VARCHAR(15),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'teacher', 'secretary', 'parent') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    class_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- Class name (e.g., "Class 5A")
    description TEXT, -- Optional description of the class
    teacher_id INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(teacher_id) REFERENCES users(user_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    teacher_id INT(11),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(teacher_id) REFERENCES users(user_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE 
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (class_id) REFERENCES classes(class_id) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE 
);


CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(100),
    term VARCHAR(50),
    academic_year VARCHAR(9) CHECK (academic_year LIKE '____-____'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    UNIQUE(exam_id, class_id, subject_id, student_id)
);


CREATE TABLE grading_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(50) NOT NULL,      
    grade VARCHAR(10) NOT NULL,       
    min_marks INT NOT NULL,            
    max_marks INT NOT NULL,            
    description VARCHAR(255) NOT NULL
);


INSERT INTO grading_criteria (level, grade, min_marks, max_marks, description)
VALUES 
('Junior', 'A', 80, 100, 'Excellent'),
('Junior', 'B', 70, 79, 'Very Good'),
('Junior', 'C', 60, 69, 'Good'),
('Junior', 'D', 50, 59, 'Satisfactory Pass'),
('Junior', 'E', 40, 49, 'Pass'),
('Junior', 'F', 0, 39, 'Fail');


INSERT INTO grading_criteria (level, grade, min_marks, max_marks, description)
VALUES 
('Senior', '1', 80, 100, 'Distinction'),
('Senior', '2', 75, 79, 'Distinction'),
('Senior', '3', 70, 74, 'Credit'),
('Senior', '4', 65, 69, 'Credit'),
('Senior', '5', 60, 64, 'Credit'),
('Senior', '6', 55, 59, 'Credit'),
('Senior', '7', 50, 54, 'Pass'),
('Senior', '8', 40, 49, 'Pass'),
('Senior', '9', 0, 39, 'Fail');
