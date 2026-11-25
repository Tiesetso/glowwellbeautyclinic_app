DROP DATABASE IF EXISTS glowwell_db;
CREATE DATABASE glowwell_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'glowwell_user'@'localhost' IDENTIFIED BY 'gl0#w3llk';
GRANT ALL PRIVILEGES ON glowwell_db.* TO 'glowwell_user'@'localhost';
FLUSH PRIVILEGES;
USE glowwell_db;

-- Users Table --
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table --
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL UNIQUE,
    category ENUM('Facials', 'Massages', 'Hair Treatments', 'Wellness', 'Speciality') NOT NULL,
    duration_minutes INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

-- Staff Table --
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    specialty VARCHAR(100) NOT NULL,
    shift_start TIME NOT NULL DEFAULT '09:00:00',
    shift_end TIME NOT NULL DEFAULT '17:00:00',
    status ENUM('Active', 'On Leave') NOT NULL DEFAULT 'Active'
);

-- Appointments Table --
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    staff_id INT NOT NULL,
    service_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Prevent double-booking the same staff member at the exact same date and time --
    CONSTRAINT uq_staff_slot UNIQUE (staff_id, appointment_date, appointment_time),
    CONSTRAINT fk_appt_customer FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_appt_staff FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
    CONSTRAINT fk_appt_service FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
);

-- Sample Data for Testing --
-- Password for all sample accounts is: password
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('Admin', 'admin@glowwell.co.za', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Neo Mokohna', 'Neo@example.com', '0825551020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Charalene Mashiba', 'charalene@example.com', '0734449876', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

INSERT INTO services (service_name, category, duration_minutes, price) VALUES
('HydraGlow Deep Facial', 'Facials', 60, 630.00),
('Swedish Full Body Massage', 'Massages', 60, 850.00),
('Japanese Silk Hair Treatment', 'Hair Treatments', 90, 1200.00),
('Clay Wrap', 'Wellness', 75, 1290.00),
('Hot Stone & Scalp Speciality', 'Speciality', 90, 1000.00);

INSERT INTO staff (full_name, email, specialty, shift_start, shift_end, status) VALUES
('Kamogelo Samwell', 'kamogelo@glowwell.co.za', 'Facials & Skincare', '09:00:00', '16:30:00', 'Active'),
('David van Dyk', 'david@glowwell.co.za', 'Massages & Wellness', '09:00:00', '17:30:00', 'Active'),
('Shai Verstappenalla', 'Verstappenalla@glowwell.co.za', 'Hair Treatments & Speciality', '09:30:00', '17:00:00', 'Active');

INSERT INTO appointments (customer_id, staff_id, service_id, appointment_date, appointment_time, status, notes) VALUES
(2, 1, 1, '2026-11-15', '10:00:00', 'Confirmed', 'Sensitive skin, prefer fragrance-free toner.'),
(3, 2, 2, '2026-11-15', '14:00:00', 'Pending', 'Focus on upper back tension.'),
(2, 3, 3, '2026-11-16', '11:00:00', 'Completed', 'Standard japanse treatment.');