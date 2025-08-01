-- Database creation
CREATE DATABASE jobconnectuganda;
USE jobconnectuganda;

-- Users table (common for all user types)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('job_seeker', 'employer', 'admin') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Job seekers profile
CREATE TABLE job_seeker_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    headline VARCHAR(100),
    bio TEXT,
    skills TEXT,
    education TEXT,
    experience TEXT,
    resume_file VARCHAR(255),
    location VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Employers/companies
CREATE TABLE employers (
    employer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    company_description TEXT,
    industry VARCHAR(50),
    website VARCHAR(100),
    logo VARCHAR(255),
    location VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Job listings
CREATE TABLE jobs (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    job_type ENUM('full_time', 'part_time', 'contract', 'internship', 'temporary') NOT NULL,
    salary_range VARCHAR(50),
    experience_level VARCHAR(50),
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deadline DATE,
    is_active BOOLEAN DEFAULT TRUE,
    views INT DEFAULT 0,
    FOREIGN KEY (employer_id) REFERENCES employers(employer_id) ON DELETE CASCADE
);

-- Job applications
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume_file VARCHAR(255),
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Saved jobs
CREATE TABLE saved_jobs (
    saved_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY (job_id, user_id)
);

-- Audit logs
CREATE TABLE audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert sample admin
INSERT INTO users (email, password_hash, user_type, first_name, last_name, phone)
VALUES ('admin@jobconnect.ug', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Admin', '+256700000000');

-- Insert sample job seekers (3)
INSERT INTO users (email, password_hash, user_type, first_name, last_name, phone) VALUES
('jane.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker', 'Jane', 'Doe', '+256701234567'),
('john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker', 'John', 'Smith', '+256702345678'),
('mary.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'job_seeker', 'Mary', 'Johnson', '+256703456789');

-- Insert job seeker profiles
INSERT INTO job_seeker_profiles (user_id, headline, bio, skills, education, experience, resume_file, location) VALUES
(2, 'Experienced Software Developer', 'Passionate about building scalable web applications with PHP and JavaScript.', 'PHP, JavaScript, MySQL, Laravel, Vue.js', 'BSc in Computer Science, Makerere University', '5 years as a full-stack developer at Tech Solutions UG', 'jane_doe_resume.pdf', 'Kampala, Uganda'),
(3, 'Marketing Specialist', 'Creative marketer with expertise in digital marketing and brand management.', 'Digital Marketing, Social Media, SEO, Content Creation', 'BA in Marketing, Uganda Christian University', '3 years at Brand Africa as Digital Marketing Executive', 'john_smith_resume.pdf', 'Entebbe, Uganda'),
(4, 'Recent IT Graduate', 'Eager to start my career in IT support and systems administration.', 'Network Administration, Hardware Troubleshooting, Windows Server', 'Diploma in IT, Kyambogo University', '6-month internship at Uganda Telecom', 'mary_johnson_resume.pdf', 'Jinja, Uganda');

-- Insert sample employers (2)
INSERT INTO users (email, password_hash, user_type, first_name, last_name, phone) VALUES
('hr@techug.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'David', 'Kato', '+256704567890'),
('info@africabrands.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employer', 'Sarah', 'Nalwoga', '+256705678901');

-- Insert employer/company data
INSERT INTO employers (user_id, company_name, company_description, industry, website, logo, location) VALUES
(5, 'Tech Solutions UG', 'Leading software development company in Uganda specializing in custom business solutions.', 'Information Technology', 'https://techug.com', 'techug_logo.png', 'Kampala, Uganda'),
(6, 'Brand Africa', 'Full-service marketing agency helping African brands reach their target audiences.', 'Marketing & Advertising', 'https://brandafrica.ug', 'brand_africa_logo.png', 'Kampala, Uganda');

-- Insert sample job listings (3 per employer)
INSERT INTO jobs (employer_id, title, description, requirements, location, job_type, salary_range, experience_level, deadline, is_active) VALUES
-- Tech Solutions UG jobs
(1, 'Senior PHP Developer', 'We are looking for an experienced PHP developer to join our team.', '5+ years PHP experience, Laravel framework, MySQL, REST APIs', 'Kampala', 'full_time', 'UGX 5,000,000 - 7,000,000', '5+ years', '2023-12-31', TRUE),
(1, 'Junior Frontend Developer', 'Entry-level position for a frontend developer with Vue.js experience.', '1+ years JavaScript, Vue.js or React, HTML/CSS', 'Kampala', 'full_time', 'UGX 2,500,000 - 3,500,000', '1-2 years', '2023-11-30', TRUE),
(1, 'IT Support Intern', '6-month internship for IT graduates to gain hands-on experience.', 'Diploma in IT, basic networking knowledge, troubleshooting skills', 'Kampala', 'internship', 'UGX 800,000 stipend', 'Entry level', '2023-10-15', TRUE),

-- Brand Africa jobs
(2, 'Digital Marketing Manager', 'Lead our digital marketing team and develop strategies for clients.', '3+ years digital marketing, social media management, analytics', 'Kampala', 'full_time', 'UGX 4,500,000 - 6,000,000', '3-5 years', '2023-12-15', TRUE),
(2, 'Content Writer', 'Create engaging content for websites, blogs, and social media.', 'Excellent writing skills, SEO knowledge, 2+ years experience', 'Remote', 'part_time', 'UGX 1,500,000 - 2,000,000', '2+ years', '2023-11-20', TRUE),
(2, 'Graphic Design Intern', '3-month internship for graphic design students.', 'Basic Photoshop/Illustrator skills, creative portfolio', 'Kampala', 'internship', 'UGX 600,000 stipend', 'Entry level', '2023-10-10', TRUE);

-- Insert sample applications
INSERT INTO applications (job_id, user_id, cover_letter, resume_file, status) VALUES
(1, 2, 'Dear Hiring Manager, I am excited to apply for the Senior PHP Developer position...', 'jane_doe_resume.pdf', 'pending'),
(4, 2, 'I would like to apply for the Digital Marketing Manager role...', 'jane_doe_resume.pdf', 'reviewed'),
(2, 3, 'As a recent graduate with frontend development experience...', 'john_smith_resume.pdf', 'pending'),
(5, 3, 'I have been writing content for various blogs and would love...', 'john_smith_resume.pdf', 'accepted'),
(3, 4, 'I am an IT graduate looking for an internship opportunity...', 'mary_johnson_resume.pdf', 'pending'),
(6, 4, 'I have attached my portfolio of graphic design work...', 'mary_johnson_resume.pdf', 'rejected');

-- Insert saved jobs
INSERT INTO saved_jobs (job_id, user_id) VALUES
(1, 3),
(4, 2),
(2, 4),
(5, 3);

-- Insert audit logs
INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES
(1, 'login', 'Admin logged in', '192.168.1.1'),
(2, 'register', 'New job seeker registered', '192.168.1.2'),
(5, 'job_post', 'Posted new job: Senior PHP Developer', '192.168.1.3'),
(6, 'job_post', 'Posted new job: Digital Marketing Manager', '192.168.1.4'),
(2, 'job_apply', 'Applied for job: Senior PHP Developer', '192.168.1.2');