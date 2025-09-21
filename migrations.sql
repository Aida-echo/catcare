-- Buat database
CREATE DATABASE IF NOT EXISTS cat_care_diary
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Pakai database ini
USE cat_care_diary;

-- 1) Tabel Cats
CREATE TABLE IF NOT EXISTS cats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  birth_date DATE NULL,
  breed VARCHAR(100) NULL,
  sex ENUM('Male','Female','Unknown') DEFAULT 'Unknown',
  color VARCHAR(100) NULL,
  allergies TEXT NULL,
  photo_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Tabel Care Routines
CREATE TABLE IF NOT EXISTS care_routines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cat_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  category ENUM('Feeding','Grooming','Litter','Medication','Play','Other') DEFAULT 'Other',
  frequency ENUM('Daily','Weekly','Monthly','Custom') DEFAULT 'Daily',
  interval_days INT DEFAULT 1,
  preferred_time TIME NULL,
  dosage VARCHAR(100) NULL,
  notes TEXT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_routine_cat FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE
);

-- 3) Tabel Care Logs
CREATE TABLE IF NOT EXISTS care_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cat_id INT NOT NULL,
  routine_id INT NULL,
  planned_at DATETIME NULL,
  actual_at DATETIME NULL,
  status ENUM('Planned','Done','Skipped','Rescheduled') DEFAULT 'Planned',
  quantity VARCHAR(100) NULL,
  notes TEXT NULL,
  photo_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_cat FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE,
  CONSTRAINT fk_log_routine FOREIGN KEY (routine_id) REFERENCES care_routines(id) ON DELETE SET NULL,
  INDEX idx_cat_planned (cat_id, planned_at),
  INDEX idx_status_planned (status, planned_at)
);

-- 4) Tabel Vet Visits
CREATE TABLE IF NOT EXISTS vet_visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cat_id INT NOT NULL,
  visit_date DATE NOT NULL,
  clinic VARCHAR(150) NULL,
  reason VARCHAR(200) NULL,
  diagnosis TEXT NULL,
  treatment TEXT NULL,
  prescription TEXT NULL,
  attachment_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_vet_cat FOREIGN KEY (cat_id) REFERENCES cats(id) ON DELETE CASCADE
);

-- Seed contoh data
INSERT INTO cats (name, birth_date, breed, sex, color, allergies) VALUES
('Mochi', '2023-06-01', 'Domestic Shorthair', 'Female', 'Calico', 'Ayam rebus');

INSERT INTO care_routines (cat_id, title, category, frequency, preferred_time, dosage, notes) VALUES
(1, 'Makan pagi', 'Feeding', 'Daily', '07:00:00', '60g kibble', 'Ganti air minum'),
(1, 'Sikat bulu', 'Grooming', 'Weekly', '18:00:00', NULL, 'Pakai sikat halus');
