# Attendance Management System (AMS)

Welcome to the **Attendance Management System**! This comprehensive document contains everything you need to understand, install, run, and test the application—both through your web browser and via Postman APIs.

This guide is written in simple, clear language so that anyone (even without technical experience) can easily get the project running in minutes.

> 📖 **Tip: How to View This File in "Pretty Mode" (Formatted Preview)**
> If you are viewing this raw Markdown file inside your code editor (such as VS Code or Antigravity):
> * **Option 1 (Keyboard Shortcut)**: Press `Ctrl + Shift + V` (or `Ctrl + K` then `V` for side-by-side view).
> * **Option 2 (Click Icon)**: Click the **"Open Preview"** icon in the top-right corner of the editor window (looks like a split document with a magnifying glass).
> * **Option 3 (Right-Click)**: Right-click anywhere in this file and select **"Open Preview"**.

---

## 📋 Table of Contents
1. [About the Project](#-about-the-project)
2. [Key Features](#-key-features)
3. [Complete Step-by-Step Installation Guide](#-complete-step-by-step-installation-guide)
4. [Demo Login Credentials](#-demo-login-credentials)
5. [How Work Shifts & Attendance Rules Work](#-how-work-shifts--attendance-rules-work)
6. [Simple API Testing Guide with Postman](#-simple-api-testing-guide-with-postman)
7. [Running Automated Tests](#-running-automated-tests)
8. [Helpful Commands & Quick Troubleshooting](#-helpful-commands--quick-troubleshooting)

---

## 🌟 About the Project

The Attendance Management System is a modern web application built with **Laravel 11**, designed to simplify daily office attendance, staff records, and shift calculations.

It provides two distinct experiences:
* **Admin Portal**: For HR and managers to oversee all employees, monitor daily attendance, track absent staff, and manage records securely.
* **Employee Portal**: A self-service portal where employees can log in, punch in when arriving at work, punch out when leaving, and review their monthly attendance history.
* **RESTful APIs**: Complete backend interfaces allowing external applications (or mobile apps) to communicate with the system using secure token authentication.

---

## 🚀 Key Features

### 1. User Authentication & Security
* Separate dashboards for **Administrators** and **Employees**.
* Password visibility toggle with an eye icon for user convenience.
* Security protection preventing deactivated/inactive employees from logging in.
* Token-based Sanctum API authentication for mobile and API testing.

### 2. Employee Management (Admin)
* View all company employees with instant searching and filtering.
* Automatically generated employee codes (`EMP001`, `EMP002`, etc.).
* Add, edit, and safely archive (soft delete) employee accounts.
* Mobile number format verification.

### 3. Attendance Management
* One-click **Check-In** and **Check-Out** directly from the employee dashboard.
* Real-time live digital clock and SweetAlert confirmation dialogs.
* Automatic working hours calculation in Indian Standard Time (IST / `Asia/Kolkata`).
* Automatic attendance status tagging (Present, Half Day, or Absent).
* Admin reporting for today's present, absent, and on-leave staff.

---

## 🛠️ Complete Step-by-Step Installation Guide

Follow these 7 simple steps to install and run the project on your computer.

### Prerequisites Needed
Before starting, ensure you have the following installed:
* **PHP 8.2 or higher**
* **Composer** (PHP package manager)
* **MySQL** (via XAMPP, WAMP, Laragon, or standalone MySQL)
* Any modern web browser (Chrome, Edge, Firefox, Brave)

---

### Step 1: Open Your Terminal or Command Prompt
Open your terminal (Command Prompt, PowerShell, or Git Bash) and navigate to the project directory:
```bash
cd C:\xampp\htdocs\attendance_management_system
```

---

### Step 2: Install Project Dependencies
Run this command to download and set up all required Laravel libraries:
```bash
composer install
```

---

### Step 3: Environment Configuration (.env)
The `.env` configuration file is already pre-configured and included in this project with:
* Pre-generated Application Key (`APP_KEY`)
* Database name set to: `attendance_management_system`
* Default MySQL credentials (username: `root`, no password for XAMPP)

*(Note: If your system hid the file during extraction, simply run: `copy .env.example .env && php artisan key:generate`)*

---

### Step 4: Create the Database in MySQL
1. Open your **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL** (both should show green).
2. Open your web browser and visit phpMyAdmin:
   [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Click **New** on the left menu and create a database named:
   `attendance_management_system`

---

### Step 5: Run Database Setup & Sample Demo Data
Run this single command in your terminal:
```bash
php artisan migrate:fresh --seed
```

**What this command does for you automatically:**
* Creates all necessary database tables (users, employees, departments, attendance, settings).
* Sets up the Administrator account and 5 company departments.
* Populates active and inactive employee accounts.
* Creates realistic past attendance records and today's shift logs so you can evaluate the system immediately.

---

### Step 6: Start the Development Server
Run the built-in Laravel server:
```bash
php artisan serve
```

You will see:
```text
INFO  Server running on [http://127.0.0.1:8000].
```
Keep this terminal window open while using the application.

---

### Step 7: Open the Application in Your Browser
Open your browser and visit:
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Demo Login Credentials

You can use any of the pre-configured accounts below to test the web portal and APIs:

### Administrator Account
| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` | `password123` | Full access to Admin Dashboard, Employee CRUD, Attendance Logs, and Absentee Reports |

### Active Employee Accounts
| Name | Email | Password | Employee ID | Department |
| :--- | :--- | :--- | :--- | :--- |
| **Rahul Sharma** | `rahul.sharma@example.com` | `password123` | EMP001 | Information Technology |
| **Priya Patel** | `priya.patel@example.com` | `password123` | EMP002 | Human Resources |
| **Amit Verma** | `amit.verma@example.com` | `password123` | EMP003 | Finance & Accounts |
| **Neha Gupta** | `neha.gupta@example.com` | `password123` | EMP004 | Marketing & Sales |
| **Vikas Singh** | `vikas.singh@example.com` | `password123` | EMP005 | Operations |

*(Note: For your convenience, passwords also accept `Password@123`)*

### Inactive Employee Account (For Security Testing)
* **Name**: Anjali Mehta
* **Email**: `anjali.mehta@example.com` | **Password**: `password123`
* **Status**: Inactive / Disabled
* **Behavior**: If this user attempts to log in, the system blocks access and displays: *"Your account is currently inactive. Please contact your administrator."*

---

## 🕒 How Work Shifts & Attendance Rules Work

* **Standard Shift Duration**: 8.0 Hours per working day.
* **Self Check-In & Check-Out**: Employees record their own attendance directly with a single click.
* **Automatic Status Calculation**:
  * **8.0 Hours or more**: Marked as **Present**
  * **Between 4.0 and 7.99 Hours**: Marked as **Half Day**
  * **Less than 4.0 Hours**: Marked as **Absent**
* **Duplicate Prevention**: An employee cannot check in twice on the same day.
* **Check-Out Validation**: An employee cannot check out unless they have checked in first.
* **Soft Deletes**: Deleted employee records are safely archived in the database using Laravel Soft Deletes, preserving historical payroll and attendance logs.

---

## 📬 Simple API Testing Guide with Postman

### What is Postman and What Are We Testing?
Normally, people use a website by opening a browser and clicking buttons. Postman is a friendly application that allows you to send requests directly to the website's background system (called the API). This confirms that features like login, employee management, and attendance punching work properly behind the scenes.

---

### Step 1: Start the Local Server
Make sure MySQL is started and your Laravel server is active in terminal:
```bash
php artisan serve
```
Base API URL: `http://127.0.0.1:8000/api`

---

### Step 2: Import the Pre-Configured Postman Collection
A complete, ready-to-use Postman collection is already included in the project root:
* **File Name**: `postman_collection.json`

To import it into Postman:
1. Open the Postman application.
2. Click the **Import** button in the top-left corner.
3. Drag and drop the `postman_collection.json` file from your project folder.
4. You will see a collection named **"Employee & Attendance Management System API"** with all endpoints organized into three folders:
   * **Authentication** (Login and Logout)
   * **Employees** (List, Details, and Delete)
   * **Attendance** (Check-In, Check-Out, and History Records)

---

### Step 3: Automatic Security Token Handling
When you click **Send** on the `Login` request, Postman automatically captures the returned security token and stores it in the `{{token}}` variable. All subsequent requests automatically use this token—no manual copying and pasting needed!

---

### Step 4: Step-by-Step API Walkthrough

#### 1. Log In to the System
* Open: **Authentication** -> **Login (Admin or Employee)**
* Body:
  ```json
  {
      "email": "admin@example.com",
      "password": "password123"
  }
  ```
* Click the blue **Send** button.
* **Expected Result**: Green `200 OK` status with `"message": "Login successful."` and the generated access token.
* *Employee test*: Change email to `priya.patel@example.com` and send again.

#### 2. View Employee List
* Open: **Employees** -> **Get Employees List**
* Click **Send**.
* **Expected Result**: `200 OK` with a paginated list of all employees and their departments.
* *Search tip*: In the **Params** tab, type a name in the `search` box (e.g., `Rahul`).

#### 3. View a Specific Employee Profile
* Open: **Employees** -> **Get Employee by ID**
* URL: `{{base_url}}/employees/1`
* Click **Send**.
* **Expected Result**: `200 OK` with full details of employee ID 1.
* *Privacy rule*: An employee can only view their own profile. If an employee tries to access another employee's profile, the system responds with `403 Forbidden` (*"Unauthorized. You can only view your own profile."*).

#### 4. Punch In (Attendance Check-In)
* Open: **Attendance** -> **Attendance Check-In**
* Body:
  ```json
  {
      "notes": "Arrived at office on time"
  }
  ```
* Click **Send**.
* **Expected Result**: `201 Created` with `"message": "Checked in successfully."` and the recorded check-in timestamp.
* *Safety rule*: Clicking Send a second time returns `400 Bad Request` (*"Already checked in today."*).

#### 5. Punch Out (Attendance Check-Out)
* Open: **Attendance** -> **Attendance Check-Out**
* Body:
  ```json
  {
      "notes": "Day tasks completed"
  }
  ```
* Click **Send**.
* **Expected Result**: `200 OK` with total hours automatically calculated and status set (`present`, `half_day`, or `absent`).

#### 6. Check Attendance History
* Open: **Attendance** -> **Get Attendance Records**
* Click **Send**.
* **Expected Result**: `200 OK` with attendance history records.
  * If logged in as Admin: Shows attendance across the entire company with filters for department, date, and status.
  * If logged in as an Employee: Only shows the logged-in employee's personal records.

#### 7. Delete an Employee (Admin Only)
* Open: **Employees** -> **Delete Employee (Admin Only)**
* Click **Send**.
* **Expected Result**:
  * As Admin: `200 OK` (*"Employee soft deleted successfully."*).
  * As Employee: `403 Forbidden` (*"Unauthorized. Only administrators can delete employees."*).

#### 8. Log Out
* Open: **Authentication** -> **Logout**
* Click **Send**.
* **Expected Result**: `200 OK` (*"Logged out successfully."*). The security token is invalidated immediately.

---

### What Do the Status Numbers Mean?
| Code | Meaning | Everyday Explanation |
| :--- | :--- | :--- |
| **200 OK** | Success | Everything worked as expected. |
| **201 Created** | Record Created | A new record (like Check-In) was created successfully. |
| **400 Bad Request** | Rule Blocked | A business rule blocked the request (e.g., checking in twice). |
| **401 Unauthorized** | Not Logged In | You must log in first to receive an access token. |
| **403 Forbidden** | Permission Denied | You do not have permission for this action (e.g., employee trying to delete staff). |
| **404 Not Found** | Not Found | The requested employee ID does not exist. |
| **422 Validation Error** | Missing Data | Required inputs are missing or formatted incorrectly. |

---

## 🧪 Running Automated Tests

The application comes with a comprehensive automated test suite verifying web routes, permissions, shift calculation logic, and REST APIs:

```bash
php artisan test
```

**Test Suite Summary:**
* **33 passing tests** with **134 assertions**.
* Covers login, dashboard, employee CRUD, punch in/out, shift math, and API security.

---

## 💡 Helpful Commands & Quick Troubleshooting

### Helpful Maintenance Commands
```bash
# Clear all cached configuration settings
php artisan config:clear

# Clear all application cache
php artisan cache:clear

# Clear cached blade views
php artisan view:clear

# Clear cached routes
php artisan route:clear

# Reset the database completely with fresh demo data
php artisan migrate:fresh --seed
```

### Quick Troubleshooting

* **Issue: "Could not send request" or "Connection refused" in Postman**
  * **Solution**: Ensure your development server is running via `php artisan serve`.
* **Issue: 401 Unauthenticated error**
  * **Solution**: Run the `POST /api/login` request first to generate a fresh security token.
* **Issue: 419 Page Expired or HTML response**
  * **Solution**: Make sure the request header has `Accept: application/json` and the URL starts with `/api/`.
* **Issue: Database connection error**
  * **Solution**: Make sure Apache and MySQL are running in your XAMPP Control Panel and the database `attendance_management_system` has been created.
