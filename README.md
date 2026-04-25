Submission for CMSC 126 PHP Lab Activity by Minus_onee

## Requirements

- PHP 8.0+
- MySQL/MariaDB

## Database

Create database `lab7` and table `students`:

```sql
CREATE DATABASE IF NOT EXISTS lab7;
USE lab7;

CREATE TABLE IF NOT EXISTS students (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(40) NOT NULL,
	age TINYINT UNSIGNED NOT NULL,
	email VARCHAR(40) NOT NULL,
	course VARCHAR(40) NOT NULL,
	year_level TINYINT UNSIGNED NOT NULL,
	graduating TINYINT(1) NOT NULL DEFAULT 0,
	image_path VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Run

1. Place project under your PHP server root (for example XAMPP htdocs).
2. Update DB credentials in `DBConnector.php` if needed.
3. Open `index.html` in your browser.

## CRUD Flow

1. Submit form to create a student.
2. Search by student ID to load record details into the form.
3. Edit fields and click Update.
4. Click Delete to remove a student.

## Validation Rules

- Name, course: required, max 40 characters
- Age: 0 to 99
- Email: valid format, max 40 characters
- Year level: 1 to 4
- Image: required for create, optional for update, max 2MB, must be JPG/PNG/WEBP