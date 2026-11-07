-- =============================================================================
-- SKRIP PEMBUATAN DATABASE DAN SKEMA SAFEASET v1.1 (BERSIH)
-- Digunakan untuk MySQL 8.0+ / MariaDB di Laragon
-- =============================================================================

-- 1. PEMBUATAN DATABASE DAN PEMILIHAN AKSES
CREATE DATABASE IF NOT EXISTS safeaset_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE safeaset_db;

-- -----------------------------------------------------------------------------
-- KELOMPOK 1: IDENTITAS DAN AUTENTIKASI
-- -----------------------------------------------------------------------------

-- Tabel Master Peran Sistem
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nama peran: BUYER, SELLER, AGENT, ADMIN'
) ENGINE=InnoDB;

-- Seed data awal untuk peran
INSERT INTO roles (role_name) VALUES 
('BUYER'), 
('SELLER'), 
('AGENT'), 
('ADMIN');

-- Tabel Utama Pengguna
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hash password menggunakan Argon2id atau bcrypt',
    status ENUM('PENDING_EMAIL', 'ACTIVE', 'SUSPENDED') DEFAULT 'PENDING_EMAIL',
    failed_login_attempts INT DEFAULT 0 COMMENT 'Jumlah percobaan login gagal',
    lockout_until DATETIME NULL COMMENT 'Batas waktu akun terkunci sementara',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Relasi Multi-Role Pengguna
CREATE TABLE user_roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    role_id INT NOT NULL,
    status ENUM('PENDING', 'ACTIVE', 'REVOKED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    UNIQUE (user_id, role_id)
) ENGINE=InnoDB;

-- Tabel Verifikasi Email
CREATE TABLE email_verifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    token_hash VARCHAR(255) NOT NULL COMMENT 'Hash token verifikasi email',
    expires_at DATETIME NOT NULL COMMENT 'Waktu kedaluwarsa token (30 menit)',
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel Rekening Bank Pengguna
CREATE TABLE bank_accounts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    account_number_encrypted VARCHAR(255) NOT NULL COMMENT 'Nomor rekening terenkripsi (NFR-S06)',
    masked_number VARCHAR(50) NOT NULL COMMENT 'Format tampilan aman, contoh: ****1234',
    bank_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- KELOMPOK 2: DOKUMEN DAN IDENTITAS TERVERIFIKASI
-- -----------------------------------------------------------------------------

-- Tabel Manajemen Berkas Terunggah
CREATE TABLE uploaded_files (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL CHECK (file_size > 0 AND file_size <= 5242880) COMMENT 'Maksimal 5MB',
    file_path VARCHAR(255) NOT NULL COMMENT 'Path di luar web root (NFR-S05)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Identitas KTP Penjual
CREATE TABLE user_identities (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    nik_encrypted VARCHAR(255) NOT NULL COMMENT 'NIK terenkripsi aplikasi (NFR-S06)',
    nik_blind_index VARCHAR(255) UNIQUE NOT NULL COMMENT 'Blind index HMAC NIK (FR-B05)',
    ktp_file_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ktp_file_id) REFERENCES uploaded_files(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- KELOMPOK 3: PROPERTI DAN PENAWARAN
-- -----------------------------------------------------------------------------

-- Tabel Listing Properti
CREATE TABLE properties (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    seller_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(15,2) NOT NULL CHECK (price > 0),
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    full_address_encrypted TEXT NOT NULL COMMENT 'Alamat presisi terenkripsi (NFR-S06)',
    description TEXT,
    certificate_number_blind_index VARCHAR(255) UNIQUE NOT NULL COMMENT 'Blind index nomor sertifikat BPN (FR-C04)',
    certificate_file_id BIGINT NOT NULL,
    agent_id BIGINT NULL COMMENT 'Agen penanggung jawab listing',
    status ENUM('PENDING_REVIEW', 'AVAILABLE', 'RESERVED', 'SOLD', 'REMOVED') DEFAULT 'PENDING_REVIEW',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (certificate_file_id) REFERENCES uploaded_files(id) ON DELETE RESTRICT,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabel Penawaran Harga (Offers)
CREATE TABLE offers (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT NOT NULL,
    buyer_id BIGINT NOT NULL,
    offer_price DECIMAL(15,2) NOT NULL CHECK (offer_price > 0),
    status ENUM('SUBMITTED', 'COUNTERED', 'ACCEPTED', 'REJECTED', 'WITHDRAWN', 'EXPIRED') DEFAULT 'SUBMITTED',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- KELOMPOK 4: TRANSAKSI DAN ESCROW
-- -----------------------------------------------------------------------------

-- Tabel Transaksi Utama
CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT NOT NULL,
    buyer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    agent_id BIGINT NOT NULL,
    agreed_price DECIMAL(15,2) NOT NULL CHECK (agreed_price > 0),
    status ENUM('AWAITING_ESCROW', 'ESCROW_FUNDED', 'IN_VERIFICATION', 'COMPLETED', 'CANCELLED', 'DISPUTED') DEFAULT 'AWAITING_ESCROW',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE RESTRICT,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT chk_party_isolation CHECK (buyer_id <> seller_id AND buyer_id <> agent_id AND seller_id <> agent_id)
) ENGINE=InnoDB;

-- Tabel Rekening Bersama (Escrow Account)
CREATE TABLE escrow_accounts (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT NOT NULL UNIQUE,
    virtual_account_number VARCHAR(100) NOT NULL,
    status ENUM('AWAITING_FUNDS', 'FUNDED', 'RELEASED', 'REFUNDED', 'DISPUTED') DEFAULT 'AWAITING_FUNDS',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabel Buku Kas Escrow (Append-Only Ledger)
CREATE TABLE escrow_ledger (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    escrow_account_id BIGINT NOT NULL,
    amount DECIMAL(15,2) NOT NULL CHECK (amount > 0),
    entry_type ENUM('IN', 'OUT_SELLER', 'OUT_AGENT', 'OUT_PLATFORM', 'REFUND') NOT NULL,
    reference_key VARCHAR(255) UNIQUE NOT NULL COMMENT 'Kunci referensi unik untuk idempotensi callback (NFR-D04)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escrow_account_id) REFERENCES escrow_accounts(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- KELOMPOK 5: AUDIT LOG
-- -----------------------------------------------------------------------------

-- Tabel Log Audit Akses & Transaksi
CREATE TABLE transaction_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT NOT NULL,
    actor_id BIGINT NOT NULL,
    actor_role VARCHAR(50) NOT NULL COMMENT 'Peran yang dipakai saat aksi dilakukan',
    action_name VARCHAR(100) NOT NULL COMMENT 'Nama operasi, contoh: APPROVE_RELEASE',
    old_value TEXT NULL,
    new_value TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(255) NOT NULL,
    `user_id` bigint UNSIGNED DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `payload` longtext NOT NULL,
    `last_activity` int NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;