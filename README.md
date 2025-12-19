# 🎓 SLEA

## Student Leadership Excellence Award System

The **Student Leadership Excellence Award (SLEA) System** is a web-based application developed using the **Laravel framework** to manage student leadership records, academic information, submissions, and assessment workflows.

This project was developed as part of an **On-the-Job Training (OJT)** requirement to demonstrate applied skills in **web development**, **database design**, and **role-based system architecture**.

---

## 🛠 Technologies Used

-   **Backend:** PHP 8.1+, Laravel
-   **Frontend:** Blade Templates, Bootstrap, JavaScript
-   **Database:** MySQL
-   **Authentication:** Laravel Authentication (Role-based)
-   **Session Handling:** Database-based Sessions
-   **Mail Service:** Google SMTP (uses Google App Password, not the account password)

---

## 👥 User Roles and Core Features

### 👨‍💼 Administrator

-   Manage colleges, programs, and majors
-   Approve or reject user registrations
-   Validate student profiles and submissions
-   Manage rubric criteria
-   Generate reports and view system logs

### 🧑‍🏫 Assessor

-   Review student submissions
-   Evaluate entries using rubric-based scoring
-   Submit and finalize assessments

### 🎓 Student

-   Complete academic and leadership profile
-   Upload required documents
-   Submit leadership records
-   Track submission status

---

## ⚙ System Requirements

-   PHP **8.1 or higher**
-   Composer
-   MySQL
-   Node.js & npm
-   Web server (Apache or Laravel built-in server)

---

## 🚀 Installation & Setup

### 1️⃣ Extract the project and enter the directory

    unzip slea.zip
    cd slea

---

### 2️⃣ Install dependencies

    composer install
    npm install

---

### 3️⃣ Environment configuration

Create the environment file:

    cp .env.example .env

Update database credentials in `.env`:

    DB_DATABASE=slea_system
    DB_USERNAME=your_db_username
    DB_PASSWORD=your_db_password

Generate the application key:

    php artisan key:generate

---

### 4️⃣ Configure Mail (Google SMTP – App Password)

⚠️ **Important:**  
Do **NOT** use your regular Google account password.  
You must generate a **Google App Password**.

Update the following in `.env`:

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=yourgmail@gmail.com
    MAIL_PASSWORD=your_google_app_password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=yourgmail@gmail.com
    MAIL_FROM_NAME="SLEA System"

---

### 5️⃣ Database migration and seeding

    php artisan migrate
    php artisan db:seed

---

### 6️⃣ Build frontend assets

    npm run build

---

### 7️⃣ Run the application

    php artisan serve

Access the system at:

    http://127.0.0.1:8000

---

## 🔐 Default Notes

-   User access is **role-based** (Admin, Assessor, Student)
-   Profile completion is required before proceeding to submissions
-   Sessions are automatically handled and expired using middleware
-   Email notifications rely on proper SMTP configuration

---

## 🔐 Default Accounts

-   Default **Administrator** and **Assessor** accounts are created automatically via database seeding.
-   Account credentials are centrally configured using a custom configuration file.
-   modify the credentials first before seeding the database

### Configuration Location

-   `config/seeding.php`

### Seeder Used

-   `database/seeders/UsersAdminSeeder.php`

### Notes

-   The default password is defined once in `config/seeding.php` and securely hashed during seeding.
-   You may update the credentials in the config file, then re-run the seeder:

    php artisan db:seed --class=UsersAdminSeeder

## 📦 Files NOT Included When Packaging (OJT Submission)

The following should **NOT** be included when submitting the project as a ZIP file:

-   `vendor/`
-   `node_modules/`
-   `.env`
-   `.env.backup`
-   `.log` files
-   `storage/logs/*`
-   `storage/framework/sessions/*`
-   `storage/framework/cache/*`

These can be regenerated using the installation steps above.

---

## 📄 License

This project is developed for **academic and training purposes** and follows the **MIT License**.

---

## 👩‍💻 Author

Developed as part of an **On-the-Job Training (OJT)** requirement  
using **Laravel** and modern web development practices.
