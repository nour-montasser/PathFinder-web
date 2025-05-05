# Pathfinder

## Overview

**Pathfinder** is an online job and freelance application platform developed as part of the coursework at **Esprit School of Engineering**.
It connects **job seekers** and **companies** by offering features such as CV generation, skill validation through tests, real-time application tracking, and freelance service offers.
The platform is designed to modernize recruitment processes while helping users manage their professional careers effectively.

## Features

* 📝 **CV Generator**: Create and manage CVs directly on the platform.
* 💼 **Job Offers**: Search and apply for job postings from companies.
* 🎯 **Skill Tests**: Validate your skills through quizzes before applying.
* 🤝 **Freelance Module**: Post gigs and offer freelance services.
* 📊 **Application Tracking**: Track job and freelance application statuses.
* 💬 **Real-Time Chat**: Chat between users and companies.
* 🔒 **Admin Panel**: Manage users, offers, applications, and reports.

## Tech Stack

### Backend

* **Symfony (PHP Framework)**
* **MySQL** (Database)
* **Doctrine ORM**
* **Twig** (Templating engine)

### Frontend

* **HTML5 / CSS3**
* **JavaScript**
* **Mazer Template** (Front and Back office design)

### Other Tools

* **Git / GitHub** (Version control)
* **Visual Studio Code** (Development)
* **Postman** (API testing)

## Directory Structure

```
📦 PathFinder
├── 📂 assets
│   ├── 📂 controllers
│   ├── 📂 css
│   ├── 📂 images
│   ├── 📂 js
│   └── 📂 styles
├── 📂 bin
├── 📂 config
│   ├── 📂 packages
│   └── 📂 routes
├── 📂 docker
├── 📂 migrations
├── 📂 ml
├── 📂 public
│   ├── 📂 build
│   ├── 📂 bundles
│   ├── 📂 images
│   ├── 📂 js
│   └── 📂 uploads
├── 📂 src
│   ├── 📂 Command
│   ├── 📂 Controller
│   ├── 📂 Entity
│   ├── 📂 EventSubscriber
│   ├── 📂 Form
│   ├── 📂 Repository
│   └── 📂 Service
├── 📂 templates
│   ├── 📂 admin
│   ├── 📂 application_job
│   ├── 📂 application_service
│   ├── 📂 app_user
│   ├── 📂 auth
│   ├── 📂 chat
│   ├── 📂 cv
│   ├── 📂 home
│   ├── 📂 includes
│   ├── 📂 job_application
│   ├── 📂 job_offer
│   ├── 📂 Login
│   ├── 📂 message
│   ├── 📂 profile
│   ├── 📂 questions
│   ├── 📂 serviceoffre
│   ├── 📂 skilltest
│   ├── 📂 stripe
│   └── 📂 user
├── 📂 translations
└── 📂 venv
    ├── 📂 Include
    ├── 📂 Lib
    └── 📂 Scripts
```

## Getting Started

### Installation

Clone the repository:

```bash
git clone https://github.com/G-Azz/PathFinder-web.git
cd pathfinder
```

Install PHP dependencies:

```bash
composer install
```

Configure `.env.local` with your database URL:

```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/pathfinder"
```

Run migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Start the server:

```bash
symfony server:start
```

Visit `http://localhost:8000`.

## Usage

1. **Register** as a seeker or company.
2. **Complete your profile** with CVs and skills.
3. **Browse offers** and apply or post freelance gigs.
4. **Take skill tests** when needed.
5. **Chat** with companies or freelancers.
6. **Track applications** (Pending / Accepted / Refused).
7. **Admin access**: Manage platform data.

## Hosting

This project is hosted on **GitHub Education** as a public repository.
Optional deployments can be done on **Heroku**, **DigitalOcean**, or **Namecheap**.

## Contributions

We welcome contributions!

1. Fork the repo.
2. Create a feature branch:

```bash
git checkout -b feature/your-feature
```

3. Commit:

```bash
git commit -m "Add your feature"
```

4. Push:

```bash
git push origin feature/your-feature
```

5. Open a Pull Request on GitHub.

## Acknowledgments

Developed as part of the coursework at **Esprit School of Engineering**.
Inspired by professional platforms like **LinkedIn** and **Upwork**.

## License

Licensed under the **MIT License**. See `LICENSE`.

## Topics (for GitHub Repository)

```
esprit-school-of-engineering job-application freelance symfony php mysql skill-tests chat recruitment platform
```

