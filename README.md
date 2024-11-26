Msasa Academy - Climate Education Platform


A web-based platform for climate change education featuring quizzes, discussion forums, and real-time climate news integration.


System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)
- Web browser (Chrome, Firefox, Safari, or Edge recommended)


Installation Guide


1. Server Setup (Using XAMPP)

1.1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
1.2. Start Apache and MySQL services from XAMPP Control Panel
1.3. Verify installation by visiting `http://localhost` in your browser


2. Database Setup

2.1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2.2. Create a new database:
   ```sql
   CREATE DATABASE msasa;
   ```
2.3. Import the database schema:
   - Navigate to the 'Import' tab
   - Select the `database_schema.sql` file from the project files
   - Click 'Go' to execute the import


3. Project Files Setup

3.1. Clone or download the project repository
3.2. Move project files to your web server directory:
   - For XAMPP: `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
   - Create a folder named 'msasa' and copy all files there
3.3. Update database configuration:
   - Open `db/config.php`
   - Modify the following constants as needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'msasa');
     ```


4. Initial Admin Setup

4.1. Access the platform through your web browser: `http://localhost/msasa`
4.2. Log in with default admin credentials:
   - Username: `superadmin123`
   - Email: `admin@msasa.edu`
   - Password: `password123`


5. Project Structure

```
msasa/
├── actions/                   # Handle form submissions & user interactions
│   ├── admin/                 
│   ├── forum/             	 
│   └── quiz/              	  
├── assets/                    # Static files (CSS, JS, images)
│   ├── css/                 
│   ├── images/             	  
│   └── js/              	  
├── db/                        # Database configuration
├── functions/                 # Reusable functions
├── utils/                     # Helper utilities
├── view/                      # Front-end files
│   ├── admin/                # Admin dashboard and management
│   ├── teacher/              # Teacher interfaces
│   ├── student/              # Student interfaces
│   ├── forum/                # Forum pages
│   └── news.php              # News page
└── index.html                 # Main entry point
```


6. User Roles

6.1. Admin
   - Manage users, quizzes, and forum content
   - Access system statistics
   - Monitor platform activity

6.2. Teacher
   - Create and manage quizzes
   - View student performance
   - Participate in forums
   - Access climate news

6.3. Student
   - Take quizzes
   - View performance history
   - Participate in forums
   - Access climate news
