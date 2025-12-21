USE school_management;

CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (role_name) VALUES
('Admin'),
('Faculty'),
('Student')
ON DUPLICATE KEY UPDATE role_name=role_name;

INSERT INTO users (role_id, username, password, first_name, last_name, email)
SELECT role_id, 'admin', 
'$2y$10$E1y7D8vG7N8lL5yFh1x6Vu5o0OHLcHbcvQ8b7x3L5PzYxk3a7wZlK', -- hashed 'admin123'
'Super', 'Admin', 'admin@example.com'
FROM roles
WHERE role_name='Admin'
LIMIT 1
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO users (role_id, username, password, first_name, last_name, email)
SELECT role_id, 'faculty1', 
'$2y$10$kVvJhDdK1y2Lt1vE6yxVse4lYhKrI6pZxkG3rT1cP6ZVfUjv8c7a6', -- hashed 'faculty123'
'John', 'Doe', 'faculty1@example.com'
FROM roles
WHERE role_name='Faculty'
LIMIT 1
ON DUPLICATE KEY UPDATE username=username;

INSERT INTO users (role_id, username, password, first_name, last_name, email)
SELECT role_id, 'student1', 
'$2y$10$yJ9xKj7LhF3aW8bT6QzEeO8sK4hR6uPzN5vB1xZtQ3gY8wL7v0rG', -- hashed 'student123'
'Jane', 'Smith', 'student1@example.com'
FROM roles
WHERE role_name='Student'
LIMIT 1
ON DUPLICATE KEY UPDATE username=username;


CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS colleges (
    college_id INT AUTO_INCREMENT PRIMARY KEY,
    college_name VARCHAR(255) NOT NULL
);

INSERT INTO colleges (college_name) VALUES
('College of Engineering'),
('College of Agriculture'),
('College of Business and Accountancy'),
('College of Information and Computing Sciences'),
('College of Education')
ON DUPLICATE KEY UPDATE college_name = college_name;

CREATE TABLE IF NOT EXISTS faculties (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    faculty_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    college_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (college_id) REFERENCES colleges(college_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    description TEXT,
    college VARCHAR(255),
    faculty_id INT NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES faculties(faculty_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    middle_name VARCHAR(50),
    contact VARCHAR(20),
    address VARCHAR(255),
    birthday DATE,
    gender ENUM('Male','Female'),
    year_level VARCHAR(20),
    guardian_name VARCHAR(100),
    guardian_contact VARCHAR(20),
    enrollment_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(100) NOT NULL,
    course_id INT NOT NULL,
    faculty_id INT,
    units INT DEFAULT 3,
    description VARCHAR(255),
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES faculties(faculty_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE (student_id, subject_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grade VARCHAR(5) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (student_id, subject_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS subject_files (
    file_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type ENUM('ASSIGNMENT','LECTURE','EXAM') DEFAULT 'LECTURE',
    deadline DATE DEFAULT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS billings (
    billing_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Paid','Unpaid') DEFAULT 'Unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    posted_by INT NOT NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS document_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    document_type ENUM('COR','GRADE_CARD') NOT NULL,
    status ENUM('PENDING','RELEASED') DEFAULT 'PENDING',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enrollment_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    total_units INT NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enrollment_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    subject_id INT NOT NULL,
    units INT NOT NULL,
    UNIQUE (request_id, subject_id),
    FOREIGN KEY (request_id) REFERENCES enrollment_requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present','Absent','Late') DEFAULT 'Present',
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);
