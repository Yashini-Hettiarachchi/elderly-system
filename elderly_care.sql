-- Elderly Care System Database Schema
-- Upload this file in phpMyAdmin (XAMPP) to create the database and tables

CREATE DATABASE IF NOT EXISTS elderly_care;
USE elderly_care;

-- Table for users (caregivers, admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'caregiver', 'elder') NOT NULL DEFAULT 'caregiver',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for elderly profiles
CREATE TABLE IF NOT EXISTS elderly (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    address VARCHAR(255),
    contact_number VARCHAR(20),
    emergency_contact VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for care records
CREATE TABLE IF NOT EXISTS care_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    elderly_id INT NOT NULL,
    caregiver_id INT NULL,
    care_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (elderly_id) REFERENCES elderly(id) ON DELETE CASCADE,
    FOREIGN KEY (caregiver_id) REFERENCES users(id) ON DELETE SET NULL
);
