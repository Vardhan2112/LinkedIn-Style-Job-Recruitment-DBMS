# LinkedIn-Style-Job-Recruitment-DBMS

## Overview

This project implements a **Job Recruitment System** similar to LinkedIn, using **PHP** and a **MySQL Database**. The system allows employers to post job openings, and candidates can be queried based on their eligibility, experience, and job titles.

---

## Features

- **Employer Functionalities**:
   - Post job openings.
   - Notify companies or individuals about eligible candidates.

- **Candidate Functionalities**:
   - View available job postings.
   - Query eligible job listings based on specific criteria.

- **Database Management**:
   - Query job titles, candidates, and user records.
   - Find the most experienced users for recruitment purposes.

---

## Requirements

- **XAMPP** (Apache, MySQL, PHP)
- Web browser (e.g., Chrome, Firefox)
- Code Editor (e.g., Visual Studio Code, Sublime)

---

## Project Structure

LinkedIn-Style-Job-Recruitment-DBMS/ 
├── database.php # Database connection script 
├── fetch_data.php # Fetch data queries 
├── fetch_eligible_jobs.php # Fetch eligible job postings 
├── fetch_eligible_users.php # Retrieve eligible candidates 
├── fetch_job_titles.php # Fetch job titles from DB 
├── fetch_users.php # Retrieve user data 
├── find_eligible_candidates.php # Find candidates matching criteria 
├── find_most_experienced_user.php # Find users with the most experience 
├── index.php # Main entry point 
├── job_postings.php # Job posting logic 
├── jrs.sql # Database dump file (import to MySQL) 
├── manage_record.php # Manage and manipulate records 
├── notify_company.php # Notify companies of candidates 
├── run_function.php # Core functional scripts 
└── run_query.php # Execute queries in the database

---

## Setup Instructions

### 1. Clone the Repository
Clone the repository to your local machine:
```
git clone https://github.com/your-username/LinkedIn-Style-Job-Recruitment-DBMS.git
```

### 2. Place Project Files in htdocs
Move the project folder to the htdocs directory in XAMPP:
```
C:\xampp\htdocs\LinkedIn-Style-Job-Recruitment-DBMS
```

### 3. Set Up the Database
Open phpMyAdmin in your browser.

Create a new database named jrs (or any name you prefer).

Import the jrs.sql file:
Click on the database.

Go to the Import tab.
Select jrs.sql and click Go.

### 4. Configure Database Connection
Edit the database.php file with your MySQL credentials:
```
$servername = "localhost";
$username = "root";
$password = "";       // Default MySQL password for XAMPP
$dbname = "jrs";      // Name of your database
```
### 5. Start XAMPP Services
Open the XAMPP Control Panel.

Start the Apache and MySQL services.

### 6. Access the Project
Open your browser and visit:
```
http://localhost/LinkedIn-Style-Job-Recruitment-DBMS
```


Usage:
Use the index.php file as the main entry point.

Test functionalities like fetching job postings, eligible candidates, and managing records.


Contribution Guidelines:
Fork the repository.

Create a new branch for your changes:
```
git checkout -b feature/your-feature
```

Commit your changes:
```
git commit -m "Add new feature"
```

Push to your branch:
```
git push origin feature/your-feature
```
Open a Pull Request.


License
This project is licensed under the MIT License.

Contact
For questions or suggestions, please reach out via:
Github:Vardhan2112
