<?php
// Seeder for Core Users and Lessees
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding Core Users & Lessees...\n";

// Ensure Users table exists (usually created during setup, but for completeness)
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    position VARCHAR(150) NULL,
    department VARCHAR(150) NULL,
    role ENUM('Admin','Manager','Staff','Viewer') NOT NULL DEFAULT 'Staff',
    status ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
    last_login DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    login_attempts TINYINT(3) NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    two_factor TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_secret VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed Users if empty
$countUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($countUsers == 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $users = [
        ['Maria Santos', 'msantos', 'maria@leasepro.ph', $pass, 'System Administrator', 'IT', 'Admin'],
        ['Juan dela Cruz', 'jdelacruz', 'juan@leasepro.ph', $pass, 'Leasing Manager', 'Operations', 'Manager'],
        ['Ana Reyes', 'areyes', 'ana@leasepro.ph', $pass, 'Leasing Officer', 'Operations', 'Staff'],
        ['Pedro Penduko', 'ppenduko', 'pedro@leasepro.ph', $pass, 'Internal Auditor', 'Finance', 'Viewer'],
        ['Sarah Geronimo', 'sgeronimo', 'sarah@leasepro.ph', $pass, 'Customer Support', 'Support', 'Staff'],
        ['Jose Rizal', 'jrizal', 'jose@leasepro.ph', $pass, 'Compliance Officer', 'Legal', 'Staff']
    ];
    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password_hash, position, department, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) $stmt->execute($u);
    echo "Inserted " . count($users) . " users.\n";
}

// Ensure Lessees table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS lessees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    trade_name VARCHAR(255),
    nature_of_business VARCHAR(255),
    owner_lessee_name VARCHAR(255),
    owner_address TEXT,
    business_address TEXT,
    contact_nos VARCHAR(100),
    email_address VARCHAR(255),
    space_code VARCHAR(50),
    total_area DECIMAL(10,2),
    rate_per_sqm DECIMAL(10,2),
    basic_rent DECIMAL(10,2),
    cusa DECIMAL(10,2),
    aircon_charges VARCHAR(100),
    electricity_water_charges VARCHAR(255),
    security_deposit VARCHAR(255),
    utility_deposit VARCHAR(255),
    construction_bond VARCHAR(255),
    lease_period_start DATE,
    lease_period_end DATE,
    lease_period_notes TEXT,
    valid_ids_presented TEXT,
    requirements_submitted TEXT,
    status ENUM('Active','Inactive','Terminated') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed Lessees if empty
$countLessees = $pdo->query("SELECT COUNT(*) FROM lessees")->fetchColumn();
if ($countLessees == 0) {
    $lessees = [
        ['CHRISTIAN PADILLA', 'CHRISTIAN PADILLA', 'GOWNS RENTAL', 'CHRISTIAN PADILLA', '2B010', 9.75, 275.00, 2681.25, 9.75, 'Active'],
        ['MARY JANE MERCADO', 'MARY JANE MERCADO', 'TAILORING', 'MARY JANE MERCADO', '2B025', 3.44, 330.00, 1135.20, 3.44, 'Active'],
        ['GLORIA DIMACULANGAN', 'GLORIA BOUTIQUE', 'RETAIL', 'GLORIA DIMACULANGAN', '1A005', 12.50, 450.00, 5625.00, 12.50, 'Active'],
        ['BENJAMIN SANTOS', 'BEN RESTO', 'FOOD & BEVERAGE', 'BENJAMIN SANTOS', 'FC001', 45.00, 500.00, 22500.00, 45.00, 'Active'],
        ['LIZA SOBERANO', 'HOPE WELLNESS', 'SPA', 'LIZA SOBERANO', '3C012', 25.50, 400.00, 10200.00, 25.50, 'Active'],
        ['ENRIQUE GIL', 'QUEN TECH', 'ELECTRONICS', 'ENRIQUE GIL', '4D009', 15.00, 350.00, 5250.00, 15.00, 'Inactive']
    ];
    $stmt = $pdo->prepare("INSERT INTO lessees (company_name, trade_name, nature_of_business, owner_lessee_name, space_code, total_area, rate_per_sqm, basic_rent, cusa, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($lessees as $l) $stmt->execute($l);
    echo "Inserted " . count($lessees) . " lessees.\n";
}
