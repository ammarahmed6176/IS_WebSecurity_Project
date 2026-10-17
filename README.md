
# University Student Portal — Web Security Project

## Information Security Assignment

This project demonstrates common web application security vulnerabilities and their corresponding defenses using a functional University Student Portal.

The application has two versions:

- **Vulnerable Version:** Contains intentionally insecure implementations for educational security testing.
- **Safe Version:** Uses secure coding practices to prevent the demonstrated attacks.

> Warning: The vulnerable application is intended for local educational testing only. Do not deploy it publicly or use real student information.

## Technology Stack

- Frontend: HTML5, CSS3, JavaScript
- Backend: PHP 8
- Database: MySQL
- Database Access: PHP PDO
- Local Server: MAMP (Apache + MySQL)
- Development: Visual Studio Code
- Version Control: Git and GitHub

## Application Features

- Student registration
- Student login and logout
- Student dashboard
- Student search
- Student directory
- Student comments
- Profile editing
- Student record lookup

## Project Structure

```text
IS_WebSecurity_Project/
|
|-- vulnerable/
|   |-- index.php
|   |-- login.php
|   |-- register.php
|   |-- logout.php
|   |-- dashboard.php
|   |-- search.php
|   |-- directory.php
|   |-- comments.php
|   |-- profile.php
|   |-- lookup.php
|   `-- assets/
|
|-- safe/
|   |-- index.php
|   |-- login.php
|   |-- register.php
|   |-- logout.php
|   |-- dashboard.php
|   |-- search.php
|   |-- directory.php
|   |-- comments.php
|   |-- profile.php
|   |-- lookup.php
|   `-- assets/
|
|-- database/
|   `-- setup.sql
|
|-- attack-pages/
|   `-- csrf-demo.html
|
|-- docs/
|   `-- evidence/
|
|-- config.example.php
|-- config.php (local only, not committed)
|-- .gitignore
`-- README.md
```

## Local Installation

### 1. Requirements

Install MAMP, PHP 8 with PDO MySQL enabled, MySQL, and a web browser.

### 2. Project Location

Place the project inside the MAMP document root.

Example:

`C:\MAMP\htdocs\IS_WebSecurity_Project`

### 3. Start Services

Open MAMP and start Apache and MySQL.

Example configuration used during development:

- Apache Port: 80
- MySQL Port: 8888

Ports may differ on another computer.

### 4. Configure Database

Open phpMyAdmin and import:

`database/setup.sql`

The script creates the `is_security_project` database and the required tables.

### 5. Configure PHP Connection

Create `config.php` in the project root using `config.example.php` as a template.

Set the correct database hostname, port, username, and password.

Do not commit actual database credentials.

### 6. Run the Application

Safe Version:

http://127.0.0.1/IS_WebSecurity_Project/safe/login.php

Vulnerable Version:

http://127.0.0.1/IS_WebSecurity_Project/vulnerable/login.php

Both applications use separate PHP session names.

### 7. Create Demo Accounts

Register fictional students through the registration page.

Create multiple demonstration accounts for search and SQL Injection tests.

Do not use real student data or real passwords.

## Security Demonstrations

### 1. Reflected Cross-Site Scripting

**Affected page:** `vulnerable/search.php`

The application displays user-controlled search input without output encoding.

Test payload:

`<script>alert('XSS Test')</script>`

Expected vulnerable result: JavaScript alert executes.

Safe implementation: HTML output encoding using `htmlspecialchars()`.

### 2. Stored Cross-Site Scripting

**Affected page:** `vulnerable/comments.php`

A submitted comment is stored in MySQL and subsequently rendered without HTML escaping.

Test payload:

`<script>alert('Stored XSS Test')</script>`

Expected vulnerable result: The alert executes whenever the stored comment is displayed.

Safe implementation: Context-appropriate output encoding.

### 3. DOM-based Cross-Site Scripting

**Affected page:** `vulnerable/directory.php`

The application reads a URL parameter and inserts it using `innerHTML`.

Test payload:

`<img src=x onerror="alert('DOM XSS Test')">`

Expected vulnerable result: Browser executes the event handler.

Safe implementation: `textContent` instead of `innerHTML` for plain text.

### 4. Cross-Site Request Forgery

**Affected page:** `vulnerable/profile.php`

The vulnerable application accepts a profile-changing POST request without verifying a CSRF token.

Demonstration page:

`attack-pages/csrf-demo.html`

Expected vulnerable result: A forged request changes the logged-in student's profile when session cookies are sent.

Safe implementation: Server-generated CSRF tokens and verification before profile updates.

### 5. SQL Injection — Easy

**Affected page:** `vulnerable/search.php`

User-controlled search input is concatenated into an SQL query.

Test input:

`' OR 1=1 #`

Expected vulnerable result: The query condition is bypassed and additional records are returned.

Safe implementation: Prepared statements with bound parameters.

### 6. SQL Injection — Intermediate

**Affected page:** `vulnerable/lookup.php`

A Boolean-based SQL Injection changes the application's true/false lookup response.

Test inputs:

- `999999 OR 1=1 #`
- `999999 OR 1=0 #`

Expected vulnerable result: Different lookup responses reveal the effect of SQL conditions.

Safe implementation: Integer validation and parameterized SQL.

### 7. SQL Injection — Advanced

**Affected page:** `vulnerable/directory.php`

The demonstration covers:

1. Determining SQL query column count.
2. Retrieving the database name.
3. Enumerating database tables.
4. Enumerating table columns.
5. Retrieving fictional demonstration records through a UNION-based query.

Safe implementation: Input validation and prepared statements.

## Security Comparison

| Vulnerability | Vulnerable Version | Safe Version |
|---|---|---|
| Reflected XSS | Unescaped output | HTML encoding |
| Stored XSS | Raw stored comment rendering | Encoded comment output |
| DOM XSS | innerHTML | textContent |
| CSRF | No CSRF token verification | CSRF token verification |
| SQLi Easy | SQL concatenation | Prepared statements |
| SQLi Intermediate | Unvalidated SQL expression | Integer validation and bound parameter |
| SQLi Advanced | UNION injection possible | Validated, parameterized lookup |

## Testing Evidence

Actual screenshots of the vulnerable and safe test outcomes are stored under:

`docs/evidence/`

Evidence should show the input payload, relevant URL, observed result, and the difference between the two implementations.

Screenshots must come from real application execution.

## Limitations

- This is an educational security laboratory, not a production application.
- The intentionally vulnerable implementation must remain on localhost.
- Additional production controls such as rate limiting, stronger session cookie policies, least-privilege database accounts, and security logging would be required before production deployment.

## GitHub

The repository maintains the project source code and development history.

Each commit should accurately represent the contributor's work.

## Disclaimer

All attacks are demonstrated against the developers' own isolated local application using fictional data, solely for educational purposes.
