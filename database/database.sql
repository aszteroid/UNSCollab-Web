-- Create Database
CREATE DATABASE IF NOT EXISTS unscollab;
USE unscollab;

-- Table: companies (Perusahaan)
CREATE TABLE IF NOT EXISTS companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    industry VARCHAR(100),
    description TEXT,
    logo_url VARCHAR(255),
    website VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    reset_token VARCHAR(64),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: admins
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'admin',
    reset_token VARCHAR(64),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: job_postings (Lowongan Kerja)
CREATE TABLE IF NOT EXISTS job_postings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    position_type VARCHAR(50),
    department VARCHAR(100),
    location VARCHAR(255),
    salary_range VARCHAR(50),
    education VARCHAR(100),
    major VARCHAR(100),
    requirements TEXT,
    deadline DATE,
    status ENUM('active', 'pending', 'closed') DEFAULT 'pending',
    applicants_count INT DEFAULT 0,
    icon VARCHAR(50),
    icon_bg VARCHAR(20),
    icon_color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Table: applicants (Pelamar)
CREATE TABLE IF NOT EXISTS applicants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_posting_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    major VARCHAR(100),
    year INT,
    ipk DECIMAL(3,2),
    cover_letter TEXT,
    skills TEXT,
    documents TEXT,
    avatar_bg VARCHAR(20),
    avatar_color VARCHAR(20),
    status ENUM('pending', 'review', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE CASCADE
);

-- Table: company_documents (Dokumen Perusahaan)
CREATE TABLE IF NOT EXISTS company_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_type ENUM('mou', 'proposal', 'agreement', 'other') DEFAULT 'other',
    file_path VARCHAR(500),
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_company_email ON companies(email);
CREATE INDEX idx_admin_email ON admins(email);
CREATE INDEX idx_job_company ON job_postings(company_id);
CREATE INDEX idx_applicant_job ON applicants(job_posting_id);
CREATE INDEX idx_applicant_status ON applicants(status);
CREATE INDEX idx_company_reset_token ON companies(reset_token);
CREATE INDEX idx_admin_reset_token ON admins(reset_token);
CREATE INDEX idx_company_documents ON company_documents(company_id);