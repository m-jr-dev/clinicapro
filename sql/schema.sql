
CREATE DATABASE IF NOT EXISTS clinicapro;
USE clinicapro;

CREATE TABLE users(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(120),email VARCHAR(120),
	password VARCHAR(255),
	role ENUM('staff','patient')
);

CREATE TABLE patients(
	id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,
	birthdate DATE,sex ENUM('M','F'),
	height_cm DECIMAL(5,2)
);

CREATE TABLE weight_history(
	id INT AUTO_INCREMENT PRIMARY KEY,
	patient_id INT,weight_kg DECIMAL(5,2),
	measured_at DATE
);

CREATE TABLE bioimpedance(
	id INT AUTO_INCREMENT PRIMARY KEY,
	patient_id INT,fat_percent DECIMAL(5,2),
	muscle_kg DECIMAL(5,2),visceral_level INT,
	water_percent DECIMAL(5,2),
	bmr_kcal INT,measured_at DATE);

CREATE TABLE monjaro_applications(
	id INT AUTO_INCREMENT PRIMARY KEY,
	patient_id INT,dose_mg DECIMAL(4,1),
	applied_at DATE
);

CREATE TABLE procedures(
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(120),
	price DECIMAL(10,2));

CREATE TABLE appointments(
	id INT AUTO_INCREMENT PRIMARY KEY,
	patient_id INT,procedure_id INT,
	scheduled_at DATETIME,status VARCHAR(20),
	payment_method VARCHAR(50)
);

CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dark_mode TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
