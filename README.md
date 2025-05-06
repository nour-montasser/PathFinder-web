This project is a user management platform built with Symfony (PHP). It provides full authentication and user profile features, enhanced by a Python-based face recognition module for optional biometric login.

Overview
The system supports:

Standard authentication with session handling

User registration and login forms

Optional login using face recognition (Python + ML)

Dynamic search and filtering

PDF download functionality

QR code-based password reset via email

Image upload and profile management

Basic statistics and data visualizations

Features
Authentication
Sign Up / Sign In with session support

Optional face recognition login via Python script

Admin and standard user roles

Face Recognition (Python Integration)
Python script using face recognition libraries

Integrated via API call or command execution from the Symfony backend

Used as an alternative login method for users

User and Profile Management
Two entities: User and Profile

Each user may have one profile (not required for admin users)

Profile includes editable user details and profile image

Password Management
Password reset via email

QR code included in the email to verify the request and securely guide the reset process

Advanced Functionality
Dynamic filtering and search across users or profiles

Basic dashboard with statistical data

PDF generation and download

Image upload and password change support

Tech Stack
Backend: PHP (Symfony)

Face Recognition: Python with ML libraries (e.g., face_recognition, OpenCV)

Database:  MySQL

Other Tools: QR code generation, Email services, PDF generation, API integration between PHP and Python
