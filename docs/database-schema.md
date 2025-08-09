# Database Schema

## Sannu-Sannu SaaS Platform

### Schema Creation Scripts

This document contains the complete SQL schema for the Sannu-Sannu platform database.

#### Database Configuration

```sql
-- Create database
CREATE DATABASE sannu_sannu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sannu_sannu;

-- Set SQL mode for strict data validation
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
```

#### Users Table

```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    theme_preference VARCHAR(50) DEFAULT 'default',
    custom_theme_colors JSON NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_theme (theme_preference),
    INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB;
```

#### Projects Table

```sql
CREATE TABLE projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_project_value DECIMAL(12,2) NOT NULL,
    status ENUM('draft', 'active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_projects_status (status),
    INDEX idx_projects_dates (start_date, end_date),
    INDEX idx_projects_created_by (created_by),

    CONSTRAINT chk_projects_dates CHECK (end_date > start_date),
    CONSTRAINT chk_projects_amounts CHECK (monthly_amount > 0 AND total_project_value > 0)
) ENGINE=InnoDB;
```

#### Products Table

```sql
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_products_project_sort (project_id, sort_order),

    CONSTRAINT chk_products_price CHECK (price > 0)
) ENGINE=InnoDB;
```

#### Contributions Table

```sql
CREATE TABLE contributions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    project_id INT UNSIGNED NOT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    duration_months INT NOT NULL,
    total_committed DECIMAL(12,2) NOT NULL,
    total_paid DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    arrears_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_type ENUM('monthly', 'one_time') NOT NULL,
    status ENUM('active', 'completed', 'cancelled', 'suspended') NOT NULL DEFAULT 'active',
    joined_date DATE NOT NULL,
    next_payment_due DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_contributions_user_project (user_id, project_id),
    INDEX idx_contributions_status (status),
    INDEX idx_contributions_next_payment (next_payment_due),
    INDEX idx_contributions_user (user_id),
    INDEX idx_contributions_project (project_id),

    CONSTRAINT chk_contributions_amounts CHECK (
        monthly_amount > 0 AND
        duration_months > 0 AND
        total_committed > 0 AND
        total_paid >= 0 AND
        arrears_amount >= 0
    )
) ENGINE=InnoDB;
```

#### Transactions Table

```sql
CREATE TABLE transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contribution_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    paystack_reference VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('monthly', 'arrears', 'one_time') NOT NULL,
    status ENUM('pending', 'success', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    paystack_response JSON NULL,
    failure_reason VARCHAR(500) NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (contribution_id) REFERENCES contributions(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_transactions_reference (paystack_reference),
    INDEX idx_transactions_status (status),
    INDEX idx_transactions_contribution (contribution_id),
    INDEX idx_transactions_user (user_id),
    INDEX idx_transactions_created (created_at),

    CONSTRAINT chk_transactions_amount CHECK (amount > 0)
) ENGINE=InnoDB;
```

#### Payment Schedules Table

```sql
CREATE TABLE payment_schedules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contribution_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'skipped') NOT NULL DEFAULT 'pending',
    transaction_id INT UNSIGNED NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (contribution_id) REFERENCES contributions(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    INDEX idx_payment_schedules_due_date (due_date),
    INDEX idx_payment_schedules_status (status),
    INDEX idx_payment_schedules_contribution (contribution_id),

    CONSTRAINT chk_payment_schedules_amount CHECK (amount > 0)
) ENGINE=InnoDB;
```

#### Email Notifications Table

```sql
CREATE TABLE email_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    metadata JSON NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    failure_reason VARCHAR(500) NULL,
    retry_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_email_notifications_type (type),
    INDEX idx_email_notifications_status (status),
    INDEX idx_email_notifications_user (user_id),
    INDEX idx_email_notifications_created (created_at)
) ENGINE=InnoDB;
```

#### Audit Logs Table

```sql
CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_entity (entity_type, entity_id),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_user (user_id),
    INDEX idx_audit_logs_created (created_at)
) ENGINE=InnoDB;

#### THEMES Table
Stores predefined and custom theme configurations.

```sql
CREATE TABLE themes (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('light', 'dark') NOT NULL,
    colors JSON NOT NULL,
    radius VARCHAR(20) DEFAULT '0.5rem',
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_themes_type (type),
    INDEX idx_themes_active (is_active),
    INDEX idx_themes_default (is_default)
) ENGINE=InnoDB;
```

### Views for Common Queries

#### User Contribution Summary View

```sql
CREATE VIEW user_contribution_summary AS
SELECT
    u.id as user_id,
    u.name as user_name,
    u.email as user_email,
    COUNT(c.id) as total_projects,
    SUM(c.total_committed) as total_committed,
    SUM(c.total_paid) as total_paid,
    SUM(c.total_committed - c.total_paid) as total_outstanding,
    COUNT(CASE WHEN c.status = 'active' THEN 1 END) as active_projects,
    COUNT(CASE WHEN c.status = 'completed' THEN 1 END) as completed_projects
FROM users u
LEFT JOIN contributions c ON u.id = c.user_id
GROUP BY u.id, u.name, u.email;
```

#### Project Statistics View

```sql
CREATE VIEW project_statistics AS
SELECT
    p.id as project_id,
    p.name as project_name,
    p.status as project_status,
    p.total_project_value,
    COUNT(c.id) as total_contributors,
    SUM(c.total_committed) as total_committed,
    SUM(c.total_paid) as total_collected,
    SUM(c.total_committed - c.total_paid) as total_outstanding,
    ROUND((SUM(c.total_paid) / p.total_project_value) * 100, 2) as completion_percentage
FROM projects p
LEFT JOIN contributions c ON p.id = c.project_id
GROUP BY p.id, p.name, p.status, p.total_project_value;
```

#### Overdue Payments View

```sql
CREATE VIEW overdue_payments AS
SELECT
    ps.id as schedule_id,
    ps.contribution_id,
    ps.amount,
    ps.due_date,
    DATEDIFF(CURDATE(), ps.due_date) as days_overdue,
    c.user_id,
    u.name as user_name,
    u.email as user_email,
    p.name as project_name
FROM payment_schedules ps
JOIN contributions c ON ps.contribution_id = c.id
JOIN users u ON c.user_id = u.id
JOIN projects p ON c.project_id = p.id
WHERE ps.status = 'pending'
AND ps.due_date < CURDATE()
ORDER BY ps.due_date ASC;
```

### Stored Procedures

#### Calculate Project Arrears

```sql
DELIMITER //
CREATE PROCEDURE CalculateProjectArrears(
    IN p_project_id INT,
    IN p_join_date DATE,
    OUT p_arrears_amount DECIMAL(10,2)
)
BEGIN
    DECLARE v_project_start DATE;
    DECLARE v_monthly_amount DECIMAL(10,2);
    DECLARE v_months_elapsed INT;

    SELECT start_date, monthly_amount
    INTO v_project_start, v_monthly_amount
    FROM projects
    WHERE id = p_project_id;

    IF p_join_date > v_project_start THEN
        SET v_months_elapsed = TIMESTAMPDIFF(MONTH, v_project_start, p_join_date);
        SET p_arrears_amount = v_months_elapsed * v_monthly_amount;
    ELSE
        SET p_arrears_amount = 0.00;
    END IF;
END //
DELIMITER ;
```

#### Update Contribution Balance

```sql
DELIMITER //
CREATE PROCEDURE UpdateContributionBalance(
    IN p_contribution_id INT,
    IN p_payment_amount DECIMAL(10,2)
)
BEGIN
    DECLARE v_current_paid DECIMAL(12,2);
    DECLARE v_total_committed DECIMAL(12,2);
    DECLARE v_new_status VARCHAR(20);

    START TRANSACTION;

    SELECT total_paid, total_committed
    INTO v_current_paid, v_total_committed
    FROM contributions
    WHERE id = p_contribution_id;

    UPDATE contributions
    SET total_paid = total_paid + p_payment_amount,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_contribution_id;

    -- Check if contribution is now complete
    IF (v_current_paid + p_payment_amount) >= v_total_committed THEN
        SET v_new_status = 'completed';
        UPDATE contributions
        SET status = v_new_status,
            next_payment_due = NULL
        WHERE id = p_contribution_id;
    END IF;

    COMMIT;
END //
DELIMITER ;
```

### Triggers

#### Audit Trail Trigger for Users

```sql
DELIMITER //
CREATE TRIGGER users_audit_trigger
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (
        user_id, action, entity_type, entity_id,
        old_values, new_values, created_at
    ) VALUES (
        NEW.id, 'UPDATE', 'users', NEW.id,
        JSON_OBJECT(
            'name', OLD.name,
            'email', OLD.email,
            'role', OLD.role
        ),
        JSON_OBJECT(
            'name', NEW.name,
            'email', NEW.email,
            'role', NEW.role
        ),
        CURRENT_TIMESTAMP
    );
END //
DELIMITER ;
```

#### Auto-generate Payment Schedules

```sql
DELIMITER //
CREATE TRIGGER generate_payment_schedules
AFTER INSERT ON contributions
FOR EACH ROW
BEGIN
    DECLARE v_counter INT DEFAULT 1;
    DECLARE v_payment_date DATE;

    IF NEW.payment_type = 'monthly' THEN
        SET v_payment_date = NEW.joined_date;

        WHILE v_counter <= NEW.duration_months DO
            INSERT INTO payment_schedules (
                contribution_id, amount, due_date, status
            ) VALUES (
                NEW.id, NEW.monthly_amount, v_payment_date, 'pending'
            );

            SET v_payment_date = DATE_ADD(v_payment_date, INTERVAL 1 MONTH);
            SET v_counter = v_counter + 1;
        END WHILE;

        UPDATE contributions
        SET next_payment_due = NEW.joined_date
        WHERE id = NEW.id;
    END IF;
END //
DELIMITER ;
```

### Indexes for Performance

#### Composite Indexes

```sql
-- For contribution queries by user and status
CREATE INDEX idx_contributions_user_status ON contributions(user_id, status);

-- For transaction queries by date range
CREATE INDEX idx_transactions_date_status ON transactions(created_at, status);

-- For payment schedule queries
CREATE INDEX idx_payment_schedules_due_status ON payment_schedules(due_date, status);

-- For project queries with date filtering
CREATE INDEX idx_projects_status_dates ON projects(status, start_date, end_date);
```

#### Full-text Search Indexes

```sql
-- For project search functionality
ALTER TABLE projects ADD FULLTEXT(name, description);

-- For product search functionality
ALTER TABLE products ADD FULLTEXT(name, description);
```

### Database Maintenance Scripts

#### Clean up old audit logs

```sql
-- Delete audit logs older than 1 year
DELETE FROM audit_logs
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

#### Archive completed contributions

```sql
-- Create archive table for completed contributions
CREATE TABLE contributions_archive LIKE contributions;

-- Move completed contributions older than 6 months
INSERT INTO contributions_archive
SELECT * FROM contributions
WHERE status = 'completed'
AND updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

DELETE FROM contributions
WHERE status = 'completed'
AND updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

### Backup and Recovery

#### Daily Backup Script

```bash
#!/bin/bash
# Daily backup script
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/sannu_sannu"
DB_NAME="sannu_sannu"

mkdir -p $BACKUP_DIR

mysqldump --single-transaction --routines --triggers \
  --user=backup_user --password=backup_password \
  $DB_NAME > $BACKUP_DIR/sannu_sannu_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/sannu_sannu_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

### Performance Monitoring Queries

#### Slow Query Analysis

```sql
-- Find tables with most reads
SELECT
    table_schema,
    table_name,
    table_rows,
    data_length,
    index_length,
    (data_length + index_length) as total_size
FROM information_schema.tables
WHERE table_schema = 'sannu_sannu'
ORDER BY total_size DESC;
```

#### Index Usage Statistics

```sql
-- Check index usage
SELECT
    object_schema,
    object_name,
    index_name,
    count_read,
    count_write,
    count_read / (count_read + count_write) * 100 as read_percentage
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = 'sannu_sannu'
ORDER BY count_read DESC;
```
