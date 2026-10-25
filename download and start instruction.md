# LocalLoop — Download & Start Instructions

This guide provides step-by-step instructions to download, configure, and run **LocalLoop** from GitHub on your local machine using **XAMPP**.

---

## 📋 System Requirements

Before you begin, ensure you have the following installed on your machine:
- **XAMPP** (with **Apache**, **MySQL**, and **PHP 8.0+**) — [Download XAMPP](https://www.apachefriends.org/)
- **Git** (optional, for cloning) — [Download Git](https://git-scm.com/)
- Modern web browser (Google Chrome, Microsoft Edge, Mozilla Firefox, or Brave)

---

## 📥 Step 1: Download / Clone the Repository from GitHub

### Option A: Using Git (Recommended)
1. Open PowerShell, Command Prompt, or Git Bash.
2. Navigate to your XAMPP `htdocs` directory:
   ```cmd
   cd C:\xampp\htdocs
   ```
3. Clone the repository into a folder named `BCN project`:
   ```bash
   git clone https://github.com/amarthyan/Local-Loop-using-xampp.git "BCN project"
   ```
4. Move into the project directory:
   ```cmd
   cd "BCN project"
   ```

### Option B: Download as ZIP
1. Visit the repository page on GitHub:  
   👉 [https://github.com/amarthyan/Local-Loop-using-xampp](https://github.com/amarthyan/Local-Loop-using-xampp)
2. Click the green **Code** button and select **Download ZIP**.
3. Extract the downloaded ZIP file into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\BCN project\
   ```
   *(Ensure the project files like `index.php`, `run.bat`, and `database.sql` are directly inside `C:\xampp\htdocs\BCN project\`)*.

---

## 🗄️ Step 2: Import the Database

The project includes pre-configured database schema and seed data in `database.sql`.

### Method 1: Using phpMyAdmin (Browser)
1. Open your browser and go to:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab at the top.
3. Click **Choose File** (or Browse) and select:
   ```
   C:\xampp\htdocs\BCN project\database.sql
   ```
4. Scroll to the bottom and click **Import** (or **Go**).
5. The `localloop` database along with all required tables, sample users, posts, events, and categories will be created automatically.

### Method 2: Using the Command Line / XAMPP Shell
1. Open Command Prompt or the **Shell** button inside XAMPP Control Panel.
2. Run the following command:
   ```cmd
   C:\xampp\mysql\bin\mysql.exe -u root < "C:\xampp\htdocs\BCN project\database.sql"
   ```
   *(Press `Enter` if prompted for a password, as XAMPP's default MySQL root password is empty)*.

---

## ⚙️ Step 3: Verify Database Configuration

Open [config/database.php](file:///c:/xampp/htdocs/BCN%20project/config/database.php) and confirm the settings match your local XAMPP MySQL configuration (the defaults are already preset):

```php
$host     = 'localhost';
$dbname   = 'localloop';
$username = 'root';
$password = '';      // Default for XAMPP is blank
```

---

## 🚀 Step 4: Start & Launch the Application

### Quick Launch via Launcher Script (Windows)
Double-click the included [run.bat](file:///c:/xampp/htdocs/BCN%20project/run.bat) file inside `C:\xampp\htdocs\BCN project\`:
- It automatically verifies and starts Apache and MySQL if they are not already running.
- It launches **LocalLoop** directly in your default browser.
- It displays quick-access menus to reopen the app, access the Admin Panel, or open phpMyAdmin.

---

### Manual Launch via XAMPP Control Panel
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
4. Open your web browser and navigate to:
   - **Main Web Application:**  
     [http://localhost/BCN%20project/](http://localhost/BCN%20project/)
   - **Admin Management Panel:**  
     [http://localhost/BCN%20project/admin/](http://localhost/BCN%20project/admin/)
   - **phpMyAdmin (Database Management):**  
     [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)

---

## 🔑 Demo Login Credentials

You can test different user roles using the pre-seeded accounts or the 1-click preset buttons on the [Login Page](http://localhost/BCN%20project/login.php):

| Role | Username | Password | Access & Capabilities |
| :--- | :--- | :--- | :--- |
| **System Admin** | `admin` | `admin123` | Full Admin Dashboard, moderation queue, user management, audit logs, and category control |
| **Resident Citizen** | `rahulk` | `password123` | Community feed, create posts, comments, likes, emergency help requests, event RSVPs |
| **Local Business** | `malabarbakes` | `password123` | Post jobs and hiring listings, manage applicants, publish local business offers |
| **Community Helper** | `ananya_doc` | `password123` | Medical & mutual aid assistance, respond to help requests, volunteer support |

---

## 🛠️ Troubleshooting & Tips

- **Port 80 / 443 Conflicts (Apache won't start):**  
  Ensure applications like Skype, VMware, or IIS are not occupying port 80 or 443. Alternatively, change Apache's port in `httpd.conf` inside XAMPP.
- **Port 3306 Conflict (MySQL won't start):**  
  Check if a standalone MySQL or MariaDB instance is already running on your machine.
- **Upload Directories:**  
  The upload folders are located under `uploads/` (`events/`, `lostfound/`, `marketplace/`, `posts/`, `profiles/`, `resumes/`). If uploads fail, verify that these directories have write permissions.
- **Spaces in URL:**  
  Because the folder name contains a space (`BCN project`), use `%20` in the URL: `http://localhost/BCN%20project/`.
