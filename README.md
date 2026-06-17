# CBHLC Web-Based Enrollment System

A modern, full-stack enrollment management platform for Christian Bible Heritage Learning Center, built with Laravel 12, React 18, and Inertia.js.

## 📋 Overview

This system digitizes and streamlines the enrollment process for CBHLC, replacing manual, paper-based operations with an efficient, organized, and user-friendly web platform. It provides online enrollment capabilities, administrative tools for managing applications, role-based access control, and comprehensive reporting features.

### Key Features

- 🎯 **Online Enrollment** - Parents can submit applications with document uploads
- 📊 **Application Management** - Registrars can review, approve, or reject applications
- 👥 **Role-Based Access** - Super Admin, Administrator, Registrar, Parent, and Student roles
- 📄 **Document Management** - Secure upload and verification of required documents
- 💰 **Billing Management** - View tuition fees and payment plans
- 📈 **Reporting** - Generate enrollment statistics and demographic reports
- 🔒 **Security** - RBAC with Spatie Laravel Permission, bcrypt hashing, audit logging
- 📧 **Notifications** - Status updates and communication tools
- 🌐 **Responsive Design** - Mobile-friendly interface using shadcn/ui and Tailwind CSS

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12 (PHP 8.3.25)
- **Database**: MySQL 8.0.43
- **ORM**: Eloquent
- **Testing**: Pest 4.0 (primary), PHPUnit compatibility maintained
- **Authorization**: Spatie Laravel Permission 6.21
- **Activity Logging**: Spatie Laravel Activity Log 4.10
- **API Bridge**: Inertia.js 2.0 (monolith SPA approach)

### Frontend
- **Framework**: React 18+
- **Component Library**: shadcn/ui (Radix UI + Tailwind CSS)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS v4
- **Form Handling**: React Hook Form
- **Type Safety**: TypeScript

### DevOps & CI/CD
- **Development**: Docker Compose + Laravel Sail
- **CI Pipeline**: GitHub Actions
- **Deployment**: Laravel Forge (Automatic)
- **Server**: DigitalOcean Droplet (all-in-one)
- **Web Server**: Nginx 1.28.0
- **Code Quality**: Laravel Pint, ESLint, Larastan

## 🚀 Quick Start

### Prerequisites
- Docker Desktop
- Git
- Node.js 22+
- Composer 2.x

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/cbhlc-enrollment.git
   cd cbhlc-enrollment
   ```

2. **Set up environment**
   ```bash
   cp .env.example .env
   ```

3. **Start development environment**
   ```bash
   # Using Laravel Sail (Docker)
   docker run --rm \
     -u "$(id -u):$(id -g)" \
     -v "$(pwd):/var/www/html" \
     -w /var/www/html \
     laravelsail/php84-composer:latest \
     composer install --ignore-platform-reqs

   ./vendor/bin/sail up -d
   ```

4. **Generate application key and run migrations**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ```

5. **Install frontend dependencies**
   ```bash
   npm install
   ```

6. **Start development servers**
   ```bash
   # In one terminal - Laravel backend
   ./vendor/bin/sail artisan serve

   # In another terminal - Vite frontend
   npm run dev
   ```

7. **Access the application**
   - Frontend: http://localhost:5173
   - Laravel: http://localhost:8000

## 📁 Project Structure

```
.
├── app/                    # Laravel application code
│   ├── Http/              # Controllers, middleware, requests
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   ├── Events/            # Application events
│   ├── Enums/             # Type enums (Gender, GradeLevel, etc.)
│   ├── Mail/              # Mailable classes
│   ├── Notifications/     # Notification classes
│   └── Policies/          # Authorization policies
├── database/
│   ├── migrations/        # Database migrations
│   ├── seeders/          # Database seeders
│   └── factories/        # Model factories for testing
├── resources/
│   ├── js/               # React components
│   │   ├── Components/   # Reusable React components
│   │   ├── Layouts/      # Page layouts
│   │   ├── Pages/        # Page components (routed)
│   │   └── app.tsx       # React entry point
│   └── css/              # Tailwind CSS
├── routes/
│   ├── web.php          # Web routes
│   ├── auth.php         # Auth routes
│   └── api.php          # API routes (if any)
├── tests/
│   ├── Feature/         # Feature tests
│   ├── Unit/            # Unit tests
│   └── Browser/         # Browser/Dusk tests
├── config/              # Laravel configuration
├── storage/             # File storage
└── public/              # Public assets
```

## 🧪 Testing

### Run All Tests
```bash
./vendor/bin/sail pest --coverage --min=60
```

### Run Specific Tests
```bash
./vendor/bin/sail pest tests/Feature/EnrollmentTest.php
```

### Run with Coverage Report
```bash
./vendor/bin/sail pest --coverage --coverage-html=coverage
```

### Frontend Tests
```bash
npm test
```

## 📝 Code Standards

### Backend
- Follow PSR-12 standards
- Use Laravel Pint for automatic code formatting
  ```bash
  ./vendor/bin/pint
  ```
- Static analysis with Larastan
  ```bash
  ./vendor/bin/sail artisan code-analysis
  ```

### Frontend
- Use ESLint for linting
  ```bash
  npm run lint
  ```
- Use Prettier for formatting
  ```bash
  npm run format
  ```

## 🔒 Authentication & Authorization

The system uses **Spatie Laravel Permission** for role-based access control:

### User Roles
- **Super Admin** - Complete system access
- **Administrator** - Full system access and user management
- **Registrar** - Enrollment processing and reporting
- **Parent/Guardian** - Enrollment submission and status tracking
- **Student** - Limited access to own information

### Key Permissions
- `student.view`, `student.create`, `student.update`, `student.delete`
- `enrollment.view`, `enrollment.create`, `enrollment.approve`, `enrollment.reject`
- `documents.view`, `documents.verify`
- `reports.view`, `reports.generate`
- `users.manage`, `system.configure`

## 🚢 Deployment

### GitHub Actions CI/CD Pipeline
The project includes automated GitHub Actions workflows:
- PHP syntax validation
- Code style checks (Laravel Pint)
- Static analysis (Larastan)
- Security auditing (Composer audit)
- Automated tests (PHPUnit/Pest)
- ESLint checks

### Production Deployment
Deployment is handled through **Laravel Forge**:
1. Push to `main` branch triggers GitHub Actions pipeline
2. On success, Laravel Forge automatically deploys to DigitalOcean
3. Zero-downtime deployments with automatic health checks
4. Rollback capability if deployment fails

**Server Details**
- Host: DigitalOcean Droplet
- IP: 128.199.161.224
- Web Server: Nginx 1.28.0
- PHP: 8.3.25
- Database: MySQL 8.0.43 (local)

See [DEPLOYMENT-GUIDE.md](DEPLOYMENT-GUIDE.md) for detailed deployment instructions.

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Issues**: Search existing issues before creating new ones
2. **Branches**: Use feature branches (e.g., `feature/documents-upload`)
3. **PRs**: Link related issues and include acceptance criteria
4. **Tests**: Add tests for new functionality (maintain 60%+ coverage)
5. **Code Style**: Run formatters before committing

For detailed guidelines, see [CONTRIBUTING.md](CONTRIBUTING.md).

## 📚 Documentation

- [SRS - Software Requirements Specification](./CLAUDE.md)
- [Contributing Guide](./CONTRIBUTING.md)
- [Testing Guide](./TESTING_GUIDE.md)
- [Deployment Guide](./DEPLOYMENT-GUIDE.md)
- [Project Roadmap](./ROADMAP.md)

## 🔐 Security

- Laravel 12's enhanced authentication with bcrypt hashing
- RBAC implementation with Laravel Gates and Policies
- CSRF protection via Laravel and Inertia.js
- SQL injection prevention through Eloquent ORM
- XSS protection via React and Laravel output sanitization
- Automated security scanning in CI/CD pipeline
- Dependency vulnerability scanning via Composer audit

## 📞 Support

For issues, feature requests, or questions:
1. Check existing issues and documentation
2. Create a new GitHub issue with detailed information
3. Include steps to reproduce for bugs
4. Add screenshots when relevant

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Last Updated**: June 2026  
**Version**: 1.0  
**Maintained By**: st4rboy1 (Me)
