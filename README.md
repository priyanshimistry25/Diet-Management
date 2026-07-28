# 🥗 NutriTrack

A web-based **Diet Management System** developed using **PHP, MySQL, HTML, CSS, and JavaScript**. The system helps users manage their diet plans, track meals and exercises, set health goals, and connect with dietitians for better health management.

## ✨ Features

### 👤 User Module
- User Registration & Login
- Dashboard with health overview
- Profile Management
- Meal Logging
- Exercise Tracking
- Health Goal Selection
- Recipe Suggestions
- Reminders
- Barcode Scan Support
- Fitness Device Linking

### 👨‍⚕️ Dietitian Module
- Secure Login
- Dashboard for monitoring users
- User Diet Management

### 🌐 General Features
- Responsive UI
- Secure Authentication
- MySQL Database Integration
- Session Management
- Contact & Support Pages
- Privacy Policy & Terms of Service

---

## 🛠️ Tech Stack

**Frontend**
- HTML5
- CSS3
- JavaScript

**Backend**
- PHP

**Database**
- MySQL

**Server**
- XAMPP / WAMP / LAMP

---

## 📁 Project Structure

```
Diet Management System/
│
├── client/
│   ├── dashboard.php
│   ├── profile.php
│   ├── newLogmeal.php
│   ├── exerciseLog.php
│   ├── recipes.php
│   ├── reminders.php
│   └── ...
│
├── dietitian/
│   ├── dashboard.php
│   └── logout.php
│
├── connection.php
├── index.php
├── login.php
├── registration.php
├── verifyLogin.php
├── verifyRegistration.php
└── ...
```

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/diet-management-system.git
```

### 2. Move Project

Copy the project folder into your XAMPP `htdocs` directory.

Example:

```
C:\xampp\htdocs\Diet Management System
```

### 3. Start XAMPP

Start:

- Apache
- MySQL

### 4. Create Database

Open **phpMyAdmin** and create a database named:

```
diet_management
```

### 5. Import Database

Import the provided SQL file (if included) into the `diet_management` database.

### 6. Configure Database Connection

Open:

```
connection.php
```

Default configuration:

```php
$host = "localhost";
$username = "root";
$password = "";
$database_name = "diet_management";
$port = 3306;
```

Modify these values if your MySQL configuration is different.

### 7. Run the Project

Open your browser:

```
http://localhost/Diet%20Management%20System/
```

---

## 📸 Screenshots

Add screenshots here.

Example:

- Home Page
- Login Page
- Registration Page
- User Dashboard
- Meal Log
- Exercise Tracker
- Dietitian Dashboard

---

## 🚀 Future Enhancements

- AI-based diet recommendations
- Nutrition analysis
- Water intake tracker
- Calorie prediction
- Mobile application
- Email notifications
- Admin panel
- Progress charts and analytics

---

## 📌 Requirements

- PHP 8.x or above
- MySQL
- Apache Server
- XAMPP/WAMP/LAMP

---

## 👩‍💻 Author

**Priyanshi Mistry**

GitHub: https://github.com/your-username

---

## 📄 License

This project is developed for educational purposes. Feel free to use and modify it for learning.

---

## 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a new branch
3. Commit your changes
4. Push the branch
5. Open a Pull Request

---

⭐ If you found this project helpful, consider giving it a star on GitHub!
