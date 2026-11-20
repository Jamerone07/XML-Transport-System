-- =============================================================================
-- SCSMS – Campus Transport System (Group 5)
-- Database: scsms_transport
-- Table: transport_trips
-- Import this file in phpMyAdmin or: mysql -u root < scsms_transport.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS scsms_transport
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE scsms_transport;

DROP TABLE IF EXISTS transport_trips;

CREATE TABLE transport_trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  route_name VARCHAR(255) NOT NULL,
  driver_name VARCHAR(150) NOT NULL,
  departure_time TIME NOT NULL,
  arrival_time TIME NOT NULL,
  capacity SMALLINT UNSIGNED NOT NULL,
  available_seats SMALLINT UNSIGNED NOT NULL,
  shuttle_status ENUM('Available', 'Full', 'Maintenance') NOT NULL DEFAULT 'Available',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample campus shuttle trips (12 records)
INSERT INTO transport_trips
  (route_name, driver_name, departure_time, arrival_time, capacity, available_seats, shuttle_status)
VALUES
  ('Gate 1 to Main Campus', 'Juan Dela Cruz', '07:00:00', '07:30:00', 30, 12, 'Available'),
  ('Main Campus to Library', 'Maria Santos', '07:45:00', '08:00:00', 25, 25, 'Full'),
  ('Dormitory to Science Building', 'Pedro Reyes', '08:15:00', '08:35:00', 40, 18, 'Available'),
  ('Gate 2 to Sports Complex', 'Ana Garcia', '09:00:00', '09:20:00', 20, 5, 'Available'),
  ('Library to Cafeteria', 'Juan Dela Cruz', '10:00:00', '10:15:00', 30, 0, 'Full'),
  ('Main Campus to Gate 1', 'Luis Ramos', '11:30:00', '12:00:00', 30, 22, 'Available'),
  ('Science Building to Dormitory', 'Maria Santos', '12:15:00', '12:40:00', 40, 30, 'Available'),
  ('Sports Complex to Main Campus', 'Carlos Mendoza', '14:00:00', '14:25:00', 22, 22, 'Full'),
  ('Gate 1 to Engineering Hall', 'Elena Torres', '15:00:00', '15:20:00', 28, 14, 'Available'),
  ('Engineering Hall to Gate 2', 'Pedro Reyes', '16:00:00', '16:25:00', 28, 20, 'Available'),
  ('Cafeteria to Library', 'Ana Garcia', '17:00:00', '17:12:00', 25, 8, 'Available'),
  ('Campus Loop Shuttle', 'Maintenance Team', '08:00:00', '18:00:00', 50, 50, 'Maintenance');
