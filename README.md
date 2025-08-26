# ARXD Hosting Staff Schedule

A responsive PHP + MySQL staff scheduling system for hosting businesses, featuring drag-and-drop shifts, pending changes, and Discord integration. Built with a modern, dark theme inspired by ARXD Hosting's admin interface.

---

## Features

- **Drag-and-Drop Scheduling** – Assign staff to shifts quickly.
- **Shift Types** – Morning and Afternoon shifts with color-coding.
- **Pending Changes Tracking** – Tracks unsaved changes for review.
- **Save/Clear/Undo Schedule** – Fully manage the monthly calendar.
- **Discord Integration** – Send schedule + pending changes to a Discord channel.
- **Responsive Design** – Works on desktops, tablets, and mobile devices.
- **Admin & Employee Views** – Separate login for admins (full access) and employees (view-only).
- **Dark Modern Theme** – Inspired by ARXD Hosting branding.

---

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/arxd-schedule.git
   cd arxd-schedule

2. **Set Up Your Database:**
   ```sql
   CREATE DATABASE arxd_schedule;
    USE arxd_schedule;
    
    CREATE TABLE staff (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL
    );
    
    CREATE TABLE shifts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        shift_date DATE NOT NULL,
        shift_time ENUM('Morning','Afternoon') NOT NULL,
        staff_name VARCHAR(255) NOT NULL
    );
3. **Configure your database connection:**
   Edit db.php with your database credentials:
    ```php
    <?php
    $servername = "localhost";
    $username = "db_user";
    $password = "db_pass";
    $dbname = "arxd_schedule";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>
4. **Set up admin credentials:**
In db.php, set your admin username and password:
    ```php
    $admin_user = "admin";
    $admin_pass = "password";
5. **Set up Discord webhook (optional):**
Edit send_to_discord.php with your webhook URL:
    ```php
    <?php
    $webhook_url = "https://discord.com/api/webhooks/your_webhook_id/your_webhook_token";
    ?>
# Usage
 - Access the admin panel at admin.php to manage staff and shifts.

 - Employees can view their schedule in index.php.

 - Drag staff from the sidebar into shift slots to assign them.

 - Save changes to commit them to the database.

 - Use the Send to Discord button to send the schedule and pending changes to your Discord channel.

# Contributing
1. **Fork the repository.**
2. **Create a new branch for your feature or bugfix.**
3. **Make your changes and commit them with descriptive messages.**
4. **Submit a pull request back to the main repository.**

# License
**This project is licensed under the MIT License.**
