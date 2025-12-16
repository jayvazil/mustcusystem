CREATE DATABASE IF NOT EXISTS mustcu;
USE mustcu;

CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255),
    position VARCHAR(50),
    role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    start_date TIMESTAMP,
    inherited_from INT,
    reset_token VARCHAR(255),
    reset_expiry DATETIME,
    FOREIGN KEY (inherited_from) REFERENCES admins(id)
);

CREATE TABLE IF NOT EXISTS leaders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    year INT,
    course VARCHAR(255),
    completion_year VARCHAR(7),
    ministry VARCHAR(50),
    position VARCHAR(50),
    docket VARCHAR(50),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    start_date TIMESTAMP,
    password VARCHAR(255),
    reset_token VARCHAR(255),
    reset_expiry DATETIME,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    year INT,
    course VARCHAR(255),
    completion_year VARCHAR(7),
    ministry VARCHAR(50),
    password VARCHAR(255),
    reset_token VARCHAR(255),
    reset_expiry DATETIME,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS associates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    completion_year VARCHAR(7),
    ministry VARCHAR(50),
    password VARCHAR(255),
    reset_token VARCHAR(255),
    reset_expiry DATETIME,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS position_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    position VARCHAR(50),
    docket VARCHAR(50),
    start_date TIMESTAMP,
    end_date TIMESTAMP,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    inherited_from INT,
    FOREIGN KEY (user_id) REFERENCES members(id),
    FOREIGN KEY (inherited_from) REFERENCES position_history(id)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_name VARCHAR(50) NOT NULL,
    value VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_id INT,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    creator_id INT,
    creator_type ENUM('leader', 'admin'),
    content TEXT,
    audience VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT,
    approved_at TIMESTAMP,
    read_by TEXT,
    FOREIGN KEY (creator_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES admins(id)
);

CREATE TABLE IF NOT EXISTS emails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    creator_id INT,
    creator_type ENUM('leader', 'admin'),
    subject VARCHAR(255),
    content TEXT,
    audience VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_by INT,
    approved_at TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES admins(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    sender_type ENUM('member', 'leader', 'associate'),
    sender_name VARCHAR(255),
    admin_id INT,
    subject VARCHAR(255),
    content TEXT,
    attachment VARCHAR(255),
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP,
    reply_content TEXT,
    FOREIGN KEY (sender_id) REFERENCES members(id),
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

CREATE TABLE IF NOT EXISTS password_reset_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    role ENUM('member', 'leader', 'associate', 'admin') NOT NULL,
    email VARCHAR(255) NOT NULL,
    reset_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES members(id)
);



CREATE TABLE IF NOT EXISTS orientation_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    page_number INT NOT NULL CHECK (page_number BETWEEN 1 AND 5),
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES members(id)
);




mustcu_login_system/
├── admin.mustcu.or.ke/
│   ├── css/
│   │   └── style.css          # Updated for #0207ba, #ff7900, #fff000, responsive design, icons
│   ├── js/
│   │   └── script.js          # Updated for animations (slide-in, bounce), responsive scripts
│   ├── includes/
│   │   ├── header.php         # Reset Password button with lock icon
│   │   ├── footer.php
│   │   └── db_connect.php
│   ├── index.php              # Plain-text password comparison, phone default, reset link form
│   ├── dashboard.php          # Shows password reset history
│   ├── approve_post.php
│   ├── approve_email.php
│   ├── promote_leader.php
│   ├── create_post.php
│   ├── send_email.php
│   ├── manage_positions.php
│   ├── set_transition_period.php
│   ├── messaging_center.php
│   ├── register.php
│   ├── reset_password.php     # Animations, confirm password, mandatory reset message
│   └── logout.php
├── leaders.mustcu.or.ke/
│   ├── css/
│   │   └── style.css          # Updated for #0207ba, #ff7900, #fff000, responsive design, icons
│   ├── js/
│   │   └── script.js          # Updated for animations, responsive scripts
│   ├── includes/
│   │   ├── header.php         # Reset Password button with lock icon
│   │   ├── footer.php
│   │   └── db_connect.php
│   ├── index.php              # Plain-text password comparison, phone default, reset link form
│   ├── dashboard.php          # Shows position transition countdown
│   ├── create_post.php
│   ├── send_email.php
│   ├── contact_admin.php
│   ├── register.php
│   ├── reset_password.php     # Animations, confirm password, mandatory reset message
│   └── logout.php
├── members.mustcu.or.ke/
│   ├── css/
│   │   └── style.css          # Updated for #0207ba, #ff7900, #fff000, responsive design, icons
│   ├── js/
│   │   └── script.js          # Updated for animations, responsive scripts
│   ├── includes/
│   │   ├── header.php         # Reset Password button with lock icon
│   │   ├── footer.php
│   │   └── db_connect.php
│   ├── index.php              # Plain-text password comparison, phone default, reset link form
│   ├── dashboard.php
│   ├── contact_admin.php
│   ├── register.php
│   ├── reset_password.php     # Animations, confirm password, mandatory reset message
│   └── logout.php
├── associates.mustcu.or.ke/
│   ├── css/
│   │   └── style.css          # Updated for #0207ba, #ff7900, #fff000, responsive design, icons
│   ├── js/
│   │   └── script.js          # Updated for animations, responsive scripts
│   ├── includes/
│   │   ├── header.php         # Reset Password button with lock icon
│   │   ├── footer.php
│   │   └── db_connect.php
│   ├── index.php              # Plain-text password comparison, phone default, reset link form
│   ├── dashboard.php
│   ├── contact_admin.php
│   ├── register.php
│   ├── reset_password.php     # Animations, confirm password, mandatory reset message
│   └── logout.php
├── shared/
│   ├── config/
│   │   └── config.php
│   ├── lib/
│   │   └── email_handler.php  # Handles reset emails, confirmation with timestamp/role
│   ├── templates/
│   │   ├── reset_link.html
│   │   ├── reset_confirmation.html  # Includes timestamp, role
│   │   ├── approval_notification.html
│   │   ├── contact_message.html
│   │   └── position_transition.html
│   └── sql/
│       └── schema.sql        # Updated for password_reset_history table