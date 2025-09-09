# Software Requirements Specification (SRS)
## Web-Based Enrollment System for Christian Bible Heritage Learning Center

**Document Version:** 3.0 (Updated with Laravel 12 and CI/CD pipeline)  
**Date:** January 2025  
**Technology Stack:** Laravel 12 + React 18 + Inertia.js + shadcn/ui + Tailwind CSS  
**CI/CD Pipeline:** GitHub Actions (CI) + Laravel Forge (CD)  
**Authors:** Mhico D. Aro, Christian Kyle M. Masangcay, Manero SJ. Rodriguez, Carl Michael Tojino  
**Client:** Christian Bible Heritage Learning Center  

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Overall Description](#2-overall-description)
3. [System Features](#3-system-features)
4. [External Interface Requirements](#4-external-interface-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [System Architecture](#6-system-architecture)
7. [Database Requirements](#7-database-requirements)
8. [Constraints and Assumptions](#8-constraints-and-assumptions)
9. [Glossary](#9-glossary)

---

## 1. Introduction

### 1.1 Purpose
This Software Requirements Specification (SRS) document describes the functional and non-functional requirements for the Web-Based Enrollment System for Christian Bible Heritage Learning Center (CBHLC). This system aims to digitize and streamline the currently manual enrollment process, providing an efficient, organized, and user-friendly platform for student registration.

### 1.2 Scope
The Web-Based Enrollment System will provide:
- Online student enrollment capabilities for parents/guardians
- Administrative tools for managing enrollment applications
- Role-based access control for different user types
- Digital record management and reporting capabilities
- Billing information display (view-only)
- Student report generation

### 1.3 Intended Audience
- **Primary Users:** Parents/Guardians of prospective and current students
- **Administrative Users:** School administrators and registrars
- **System Administrators:** IT personnel responsible for system maintenance
- **Stakeholders:** School management and teaching staff

### 1.4 Product Overview
The system replaces the current manual, paper-based enrollment process with a comprehensive web-based solution that allows parents to submit enrollment applications remotely while providing administrative staff with efficient tools for managing the enrollment workflow.

---

## 2. Overall Description

### 2.1 Product Perspective
The Web-Based Enrollment System is a standalone web application designed specifically for Christian Bible Heritage Learning Center. It addresses the institution's current challenges with manual enrollment processing by providing a digital platform accessible via web browsers.

### 2.2 Product Functions
- **User Authentication and Authorization**
- **Online Enrollment Form Submission**
- **Document Upload and Management**
- **Application Status Tracking**
- **Administrative Review and Approval Workflow**
- **Student Record Management**
- **Billing Information Display**
- **Report Generation**
- **Communication Tools**

### 2.3 User Characteristics
- **Parents/Guardians:** Varying levels of technical expertise; primarily concerned with ease of use and accessibility
- **Registrars:** Moderate technical skills; focus on efficiency and data accuracy
- **Administrators:** Basic to intermediate technical skills; require comprehensive system oversight capabilities

### 2.4 Operating Environment
- **Client Side:** Modern web browsers supporting React applications (Chrome, Firefox, Safari, Edge)
- **Server Side:** Laravel 12 framework with Inertia.js for SPA functionality
- **Database:** MySQL 8.3+ Server (managed via Laravel's Eloquent ORM)
- **Development Environment:** Docker Compose with Laravel Sail
- **Deployment Platform:** Linode Nanode (1GB) managed by Laravel Forge
- **CI/CD Pipeline:** GitHub Actions for automated testing and Laravel Forge for deployment
- **Internet Connectivity:** Required for system access

### 2.5 Assumptions and Dependencies
- Users have access to internet-connected devices with modern web browsers supporting React applications
- Linode hosting infrastructure managed via Laravel Forge with automated deployment
- Docker environment available for local development with Laravel Sail
- GitHub repository configured with automated CI/CD pipeline
- Staff training will be provided for system usage
- Current enrollment policies and procedures remain applicable
- Modern JavaScript enabled in user browsers
- Development team has Laravel 12 experience and follows modern CI/CD practices

---

## 3. System Features

### 3.1 User Authentication System

#### 3.1.1 Description
Secure login/logout functionality with role-based access control ensuring appropriate system access for different user types.

#### 3.1.2 Functional Requirements
- **FR-1.1:** System shall provide secure login functionality for all user types
- **FR-1.2:** System shall implement role-based access control (RBAC)
- **FR-1.3:** System shall provide password reset functionality
- **FR-1.4:** System shall maintain user session management
- **FR-1.5:** System shall redirect users to appropriate dashboards based on roles

#### 3.1.3 User Roles
- **Administrator:** Full system access, user management, system configuration
- **Registrar:** Enrollment processing, student records management, reporting
- **Parent/Student:** Enrollment form submission, status tracking, billing information access

### 3.2 Online Enrollment Module

#### 3.2.1 Description
Comprehensive online form for collecting student and guardian information, with integrated document upload functionality.

#### 3.2.2 Functional Requirements
- **FR-2.1:** System shall provide online enrollment form with required field validation
- **FR-2.2:** System shall support document upload (Birth Certificate, Report Cards, Form 138, Good Moral Certificate)
- **FR-2.3:** System shall accept JPEG and PNG file formats with maximum 50MB file size
- **FR-2.4:** System shall allow users to edit applications before final submission
- **FR-2.5:** System shall provide form save functionality for incomplete applications
- **FR-2.6:** System shall generate unique application reference numbers

#### 3.2.3 Required Information
- Student personal details (name, birthdate, address, gender, grade level)
- Guardian contact information (names, relationship, phone numbers, email)
- Previous academic records
- Supporting documents

### 3.3 Dashboard Module

#### 3.3.1 Description
Role-specific dashboards providing relevant information and functionality access for each user type.

#### 3.3.2 Functional Requirements
- **FR-3.1:** System shall provide customized dashboards for each user role
- **FR-3.2:** Parent dashboard shall display application status and school announcements
- **FR-3.3:** Administrative dashboards shall show system statistics and pending tasks
- **FR-3.4:** System shall display school calendar and important events
- **FR-3.5:** Dashboard shall provide quick access to frequently used functions

### 3.4 Enrollment Processing Module

#### 3.4.1 Description
Administrative tools for reviewing, approving, or rejecting enrollment applications.

#### 3.4.2 Functional Requirements
- **FR-4.1:** System shall provide application review interface for registrars
- **FR-4.2:** System shall allow approval, rejection, or request for additional information
- **FR-4.3:** System shall send status update notifications to parents
- **FR-4.4:** System shall maintain audit trail of all application actions
- **FR-4.5:** System shall support bulk processing of applications
- **FR-4.6:** System shall generate enrollment confirmation documents

### 3.5 Student Records Management

#### 3.5.1 Description
Comprehensive system for managing enrolled student information and academic records.

#### 3.5.2 Functional Requirements
- **FR-5.1:** System shall maintain complete student profiles
- **FR-5.2:** System shall support student information updates
- **FR-5.3:** System shall track enrollment history
- **FR-5.4:** System shall provide search and filter capabilities
- **FR-5.5:** System shall support data export functionality

### 3.6 Billing Module

#### 3.6.1 Description
View-only billing information display showing tuition fees and payment plans.

#### 3.6.2 Functional Requirements
- **FR-6.1:** System shall display tuition fee structures by grade level
- **FR-6.2:** System shall show available payment plans (Annual, Semestral, Monthly)
- **FR-6.3:** System shall generate billing statements for enrolled students
- **FR-6.4:** Parents shall be able to view their child's billing information
- **FR-6.5:** System shall calculate total fees including miscellaneous charges

### 3.7 Reporting Module

#### 3.7.1 Description
Report generation capabilities for administrative and statistical purposes.

#### 3.7.2 Functional Requirements
- **FR-7.1:** System shall generate enrollment statistics reports
- **FR-7.2:** System shall produce student demographic reports
- **FR-7.3:** System shall create class roster reports
- **FR-7.4:** System shall support report filtering by date, grade, and status
- **FR-7.5:** System shall export reports in multiple formats (PDF, Excel)

### 3.8 Communication Module

#### 3.8.1 Description
Tools for communication between parents and school administration.

#### 3.8.2 Functional Requirements
- **FR-8.1:** System shall provide inquiry form for parent-school communication
- **FR-8.2:** System shall display school contact information
- **FR-8.3:** System shall show school office hours
- **FR-8.4:** System shall support announcement broadcasting
- **FR-8.5:** System shall maintain communication history

---

## 4. External Interface Requirements

### 4.1 User Interfaces

#### 4.1.1 General UI Requirements
- **UI-1:** Interface shall be responsive using Tailwind CSS responsive utilities across devices
- **UI-2:** Design shall incorporate CBHLC branding with customized shadcn/ui theme
- **UI-3:** Navigation shall be intuitive using consistent shadcn/ui navigation components
- **UI-4:** Forms shall utilize shadcn/ui form components with React Hook Form validation
- **UI-5:** Interface shall support accessibility standards (WCAG 2.1) via shadcn/ui's built-in accessibility features
- **UI-6:** Component consistency maintained through shadcn/ui's design system
- **UI-7:** Dark/light mode support available through Tailwind CSS and shadcn/ui theming

#### 4.1.2 Specific Interface Components
- **Login Page:** React component with shadcn/ui Card and Form components for secure authentication
- **Dashboard:** Role-specific React dashboards using shadcn/ui layout components and data visualization
- **Enrollment Form:** Multi-step React form utilizing shadcn/ui form components with real-time validation
- **Document Upload:** Drag-and-drop interface using shadcn/ui file input components
- **Administrative Panels:** Data management interfaces with shadcn/ui Tables, Modals, and Action components
- **Reporting Interface:** Interactive reports using shadcn/ui Charts and export functionality

### 4.2 Hardware Interfaces
- **HI-1:** System shall operate on standard web server hardware
- **HI-2:** Client access via standard computing devices (desktop, laptop, mobile)
- **HI-3:** File storage shall support document management requirements

### 4.3 Software Interfaces

#### 4.3.1 Web Server Interface
- **SI-1:** Nginx web server (configured via Laravel Forge)
- **SI-2:** PHP 8.2+ runtime with Laravel 12 framework (PHP 8.4 recommended for optimal performance)
- **SI-3:** SSL/TLS encryption via Let's Encrypt (automated by Laravel Forge)
- **SI-4:** Laravel Forge server provisioning and configuration management
- **SI-5:** GitHub Actions integration for automated deployment triggers

#### 4.3.2 Database Interface
- **SI-6:** MySQL 8.0 managed via Laravel's Eloquent ORM with Laravel 12 optimizations (MySQL 8.3+ recommended for production)
- **SI-7:** Database migrations and seeding via Laravel's migration system
- **SI-8:** Connection pooling managed by Laravel's database configuration
- **SI-9:** Automated backup and recovery via Laravel Forge
- **SI-10:** Database monitoring and performance optimization through Laravel Forge dashboard
- **SI-11:** Test database provisioning automated via GitHub Actions CI pipeline

#### 4.3.3 Browser Compatibility
- **SI-10:** Google Chrome (latest version) - Primary development target
- **SI-11:** Mozilla Firefox (latest version) - Full React support
- **SI-12:** Microsoft Edge (latest version) - Modern JavaScript compatibility
- **SI-13:** Safari (latest version) - iOS/macOS support
- **SI-14:** Mobile browsers supporting modern JavaScript (React applications)
- **SI-15:** Minimum ES2020 support required for React and Inertia.js functionality

---

## 5. Non-Functional Requirements

### 5.1 Performance Requirements
- **NFR-1.1:** Initial page load shall not exceed 2 seconds (optimized through React SSR and Inertia.js)
- **NFR-1.2:** Subsequent navigation shall be near-instantaneous (<500ms) via SPA functionality
- **NFR-1.3:** System shall support concurrent access by up to 50 users on Nanode infrastructure
- **NFR-1.4:** File uploads shall complete within 30 seconds for 50MB files with progress indication
- **NFR-1.5:** Database queries shall execute within 1 second (optimized via Laravel 12 Eloquent ORM and indexing)
- **NFR-1.6:** React component rendering shall be optimized to prevent layout shifts
- **NFR-1.7:** JavaScript bundle size shall be optimized through code splitting and tree shaking
- **NFR-1.8:** System downtime shall not exceed 2% annually (accounting for Nanode server specifications)
- **NFR-1.9:** CI/CD pipeline execution time shall not exceed 10 minutes for full test suite
- **NFR-1.10:** Automated deployments shall complete within 5 minutes via Laravel Forge

### 5.2 Security Requirements
- **NFR-2.1:** Laravel 12's enhanced authentication with bcrypt hashing and secure session management
- **NFR-2.2:** Personal data protection through Laravel's encrypted database columns and GDPR compliance features
- **NFR-2.3:** RBAC implementation using Laravel Gates and Policies with Inertia.js middleware
- **NFR-2.4:** Comprehensive audit logging using Laravel's event system and model observers
- **NFR-2.5:** File upload validation through Laravel 12's enhanced file validation rules and MIME type checking
- **NFR-2.6:** CSRF protection automatically handled by Laravel and Inertia.js integration
- **NFR-2.7:** SQL injection prevention through Eloquent ORM's parameter binding
- **NFR-2.8:** XSS protection via React's built-in escaping and Laravel's output sanitization
- **NFR-2.9:** Security vulnerability scanning automated via GitHub Actions CI pipeline
- **NFR-2.10:** Dependency security auditing using Composer audit in CI/CD pipeline

### 5.3 Reliability Requirements
- **NFR-3.1:** System availability shall be 99% during business hours
- **NFR-3.2:** Data backup shall occur daily with point-in-time recovery capability
- **NFR-3.3:** System shall implement graceful error handling and recovery
- **NFR-3.4:** Transaction integrity shall be maintained during system failures

### 5.4 Usability Requirements
- **NFR-4.1:** New users shall complete enrollment forms within 30 minutes
- **NFR-4.2:** System help documentation shall be comprehensive and accessible
- **NFR-4.3:** User interface shall be intuitive for users with basic computer skills
- **NFR-4.4:** Error messages shall be clear and provide actionable guidance

### 5.5 Scalability Requirements
- **NFR-5.1:** System shall accommodate 500+ student records
- **NFR-5.2:** Database shall support growth to 1000+ annual applications
- **NFR-5.3:** File storage shall scale to accommodate increasing document volumes
- **NFR-5.4:** System architecture shall support horizontal scaling

### 5.6 Maintainability Requirements
- **NFR-6.1:** Code shall follow established development standards
- **NFR-6.2:** System documentation shall be comprehensive and current
- **NFR-6.3:** Database schema shall support version migration
- **NFR-6.4:** System shall provide diagnostic and monitoring capabilities

---

## 6. System Architecture

### 6.1 Architectural Overview
The system follows a modern full-stack architecture leveraging Laravel 12's ecosystem:
- **Presentation Layer:** React 18+ based SPA with shadcn/ui components and Tailwind CSS styling
- **Application Layer:** Laravel 12 framework with Inertia.js 2.0 bridging server-side routing to client-side React components
- **Data Layer:** MySQL 8.0 database managed via Laravel's Eloquent ORM (MySQL 8.3+ recommended for production)
- **Development Environment:** Containerized using Docker Compose with Laravel Sail
- **CI/CD Pipeline:** GitHub Actions for automated testing, code quality checks, and security auditing
- **Deployment:** Automated deployment via Laravel Forge to Linode infrastructure with zero-downtime deployments

### 6.2 Technology Stack
- **Frontend Framework:** React 18+ with TypeScript support
- **UI Components:** shadcn/ui component library (copy-paste approach with full customization)
- **CSS Framework:** Tailwind CSS v4 with utility-first styling
- **Full-Stack Bridge:** Inertia.js 2.0 (eliminating need for separate API)
- **Backend Framework:** Laravel 12 (PHP 8.2+, PHP 8.4 recommended)
- **Database:** MySQL 8.0 with Eloquent ORM (upgrading to 8.3+ recommended)
- **Web Server:** Nginx (configured via Laravel Forge)
- **Local Development:** Docker Compose with Laravel Sail (PHP 8.4 container)
- **CI/CD Pipeline:** GitHub Actions for continuous integration
- **Deployment Platform:** Laravel Forge managing Linode Nanode servers
- **Version Control Integration:** Git with automated deployment triggers via GitHub Actions
- **Testing Framework:** Pest 4.0 (primary), PHPUnit compatibility maintained
- **Development Methodology:** Agile with iterative sprints and automated testing

### 6.3 Security Architecture
- **Authentication:** Laravel 12's enhanced authentication with bcrypt password hashing
- **Authorization:** Laravel's Gate and Policy system for RBAC implementation
- **CSRF Protection:** Laravel's automatic CSRF token handling via Inertia.js
- **Data Protection:** SSL/TLS encryption (Let's Encrypt certificates via Laravel Forge)
- **File Security:** Laravel 12's enhanced file validation and secure storage with configurable upload constraints
- **API Security:** Inertia.js eliminates exposed API endpoints, reducing attack surface
- **Server Security:** Laravel Forge automated security updates and firewall configuration
- **CI/CD Security:** GitHub Actions with automated security auditing using Composer audit
- **Dependency Security:** Automated vulnerability scanning and dependency updates via GitHub Actions
- **Code Quality Security:** Static analysis and security linting integrated in CI pipeline

### 6.4 Development Environment Architecture
- **Local Development:** Docker Compose with Laravel Sail providing consistent development environment
- **Container Services:** PHP 8.4 (Docker container), MySQL 8.0, Redis, Node.js 22 for asset compilation
- **Hot Module Replacement:** Vite development server integrated with Laravel for fast React development
- **Database Seeding:** Laravel factories and seeders for consistent development data
- **Testing Environment:** Pest 4.0 for primary testing (with PHPUnit compatibility), React Testing Library for frontend components, Browser testing capabilities via Pest v4
- **CI/CD Integration:** GitHub Actions workflows for automated testing, linting, and security checks
- **Pre-scaffolded Components:** Development team has pre-built core application structure and components

### 6.5 CI/CD Pipeline Architecture
- **Continuous Integration:** GitHub Actions workflows triggered on pull requests and pushes
- **Pipeline Stages:** Syntax validation, code style checks, static analysis, security auditing, automated testing
- **Test Strategy:** Fast-fail approach with PHP syntax check, PHPUnit/Pest test suite, and Laravel Dusk browser tests
- **Code Quality:** Automated linting, Laravel Pint code formatting, and Larastan static analysis
- **Security:** Composer audit for dependency vulnerabilities and automated security scanning
- **Build Optimization:** Composer dependency caching and optimized Docker layer caching
- **Multi-Environment Testing:** Matrix workflows testing across different PHP versions and environments
- **Deployment Trigger:** Successful CI pipeline triggers automated deployment via Laravel Forge

### 6.6 Deployment Architecture
- **Server Provisioning:** Laravel Forge automated server setup on Linode Nanode (1GB RAM, 1 CPU Core, 25GB SSD)
- **Web Server:** Nginx optimized for Laravel 12 applications
- **Process Management:** PHP-FPM with optimized worker configuration for small server
- **SSL Certificates:** Automated Let's Encrypt certificate management
- **Monitoring:** Laravel Forge server monitoring and performance metrics
- **Backup Strategy:** Automated daily database backups with 7-day retention
- **Deployment Pipeline:** GitHub Actions CI success triggers Laravel Forge deployment hooks
- **Zero-Downtime Deployments:** Laravel Forge atomic deployments with rollback capability

### 6.7 Asset Compilation and Management
- **Build Tool:** Vite for fast asset compilation and bundling
- **JavaScript Bundling:** React components and dependencies optimized for production
- **CSS Processing:** Tailwind CSS with PurgeCSS for optimized bundle sizes
- **Code Splitting:** Dynamic imports for optimized loading of React components
- **Asset Versioning:** Laravel Mix/Vite asset versioning for cache busting
- **CI Integration:** Automated asset building and optimization in GitHub Actions pipeline

---

## 7. Database Requirements

### 7.1 Data Storage Requirements
- **Student information and enrollment applications**
- **User accounts and authentication data**
- **Document metadata and file references**
- **System configuration and settings**
- **Audit logs and activity tracking**

### 7.2 Data Backup and Recovery
- **Daily automated backups**
- **Point-in-time recovery capability**
- **Disaster recovery procedures**
- **Data retention policies**

### 7.3 Data Integrity Requirements
- **Referential integrity constraints**
- **Data validation rules**
- **Transaction consistency**
- **Concurrency control**

---

## 8. Constraints and Assumptions

### 8.1 System Constraints
- **CONS-1:** No online payment processing functionality
- **CONS-2:** No academic grading or performance tracking
- **CONS-3:** Internet connectivity required for system access
- **CONS-4:** File upload limited to 50MB per document
- **CONS-5:** System operates in English language only

### 8.2 Business Constraints
- **CONS-6:** Must comply with CBHLC enrollment policies
- **CONS-7:** Implementation within academic year constraints
- **CONS-8:** Budget limitations for infrastructure
- **CONS-9:** Staff training requirements

### 8.3 Technical Constraints
- **CONS-10:** Web-based deployment only
- **CONS-11:** Compatible with existing school infrastructure
- **CONS-12:** Modern web technologies with Laravel 12 framework requirements
- **CONS-13:** Single-school deployment (not multi-tenant)
- **CONS-14:** PHP 8.2+ runtime requirement (PHP 8.4 recommended)
- **CONS-15:** GitHub repository required for CI/CD pipeline
- **CONS-16:** CI pipeline execution time limited to 10 minutes

### 8.4 Assumptions
- **ASS-1:** Users have basic computer and internet skills with modern browser support
- **ASS-2:** Reliable internet connectivity available for SPA functionality
- **ASS-3:** Linode Nanode hosting managed via Laravel Forge with automated deployments
- **ASS-4:** Current enrollment procedures remain applicable
- **ASS-5:** Staff will receive adequate system training including React-based interface usage
- **ASS-6:** Development team has experience with Laravel 12, React, and Inertia.js
- **ASS-7:** Docker environment available for local development setup
- **ASS-8:** GitHub Actions CI/CD pipeline is configured and operational
- **ASS-9:** Core application structure has been pre-scaffolded by development team
- **ASS-10:** PHPUnit 11+ and Pest 3+ testing frameworks are properly configured
- **ASS-11:** Automated testing and deployment processes are reliable and maintainable

---

## 9. Glossary

| Term | Definition |
|------|------------|
| **CBHLC** | Christian Bible Heritage Learning Center |
| **RBAC** | Role-Based Access Control |
| **SRS** | Software Requirements Specification |
| **UI** | User Interface |
| **SPA** | Single Page Application |
| **SSL/TLS** | Secure Sockets Layer/Transport Layer Security |
| **CRUD** | Create, Read, Update, Delete operations |
| **MVP** | Minimum Viable Product |
| **UAT** | User Acceptance Testing |
| **ORM** | Object-Relational Mapping |
| **Inertia.js** | Modern monolith framework bridging server-side Laravel with client-side React |
| **shadcn/ui** | Copy-paste React component library built on Radix UI and Tailwind CSS |
| **Laravel Sail** | Docker-based local development environment for Laravel |
| **Laravel Forge** | Server provisioning and deployment service for Laravel applications |
| **Eloquent** | Laravel's built-in ORM (Object-Relational Mapping) system |
| **Vite** | Fast build tool and development server for modern web applications |
| **Linode Nanode** | Entry-level VPS offering from Linode (1GB RAM, 1 CPU, 25GB SSD) |
| **GitHub Actions** | CI/CD platform for automated testing, building, and deployment workflows |
| **PHPUnit** | PHP testing framework for unit and feature testing (compatibility maintained) |
| **Pest** | Elegant PHP testing framework with expressive syntax - Primary testing framework |
| **Pest v4** | Latest version with browser testing capabilities and Laravel integration |
| **Laravel Pint** | Opinionated PHP code style fixer built for Laravel |
| **Larastan** | Static analysis tool for Laravel applications |
| **Composer Audit** | Security vulnerability scanner for PHP dependencies |
| **CI/CD** | Continuous Integration and Continuous Deployment pipeline |
| **Fast-Fail** | CI strategy that stops pipeline execution on first failure to save resources |

---

## 10. Development Workflow and Standards

### 10.1 Local Development Setup
- **Environment:** Laravel Sail with Docker Compose
- **Installation:** `curl -s https://laravel.build/cbhlc-enrollment | bash` (Laravel 12)
- **Services:** PHP 8.2+ (PHP 8.4 recommended), MySQL 8.3+, Redis, Node.js, Mailpit
- **Frontend Development:** Vite dev server with Hot Module Replacement (HMR)
- **React Development:** TypeScript support with shadcn/ui components
- **Pre-scaffolded:** Core application structure and components already implemented

### 10.2 Code Standards and Best Practices
- **Backend:** Laravel 12 coding standards with PSR-12 compliance
- **Frontend:** React 18+ function components with TypeScript
- **Component Library:** shadcn/ui components with customization through CSS variables
- **Styling:** Tailwind CSS v4 utility-first approach with custom design system
- **State Management:** React hooks and Inertia.js 2.0 shared data
- **Form Handling:** React Hook Form with shadcn/ui form components
- **Code Quality:** Laravel Pint for automated code formatting
- **Static Analysis:** Larastan for PHP static analysis

### 10.3 Testing Strategy
- **Primary Testing Framework:** Pest 4.0 with elegant PHP testing syntax and browser testing capabilities
- **Backend Testing:** Feature and unit tests using Pest 4.0 (PHPUnit compatibility maintained)
- **Frontend Testing:** React Testing Library for component testing
- **Browser Testing:** Pest v4 browser testing for end-to-end tests (replaces Laravel Dusk)
- **API Testing:** Inertia.js response testing via Pest Laravel plugin
- **CI Testing:** GitHub Actions pipeline with fast-fail approach
- **Test Database:** Automated test database provisioning and cleanup via GitHub Actions
- **Coverage:** Code coverage reporting integrated in CI pipeline

### 10.4 CI/CD and Deployment Process
- **Version Control:** Git-based workflow with feature branches and pull requests
- **Continuous Integration:** GitHub Actions workflows with automated testing and quality checks
- **CI Pipeline Stages:**
  - PHP syntax validation
  - Code style formatting (Laravel Pint)
  - Static analysis (Larastan)
  - Security auditing (Composer audit)
  - Automated testing (PHPUnit/Pest)
  - Browser testing (Laravel Dusk)
- **Continuous Deployment:** Laravel Forge deployment triggered by successful CI pipeline
- **Environment Management:** Laravel's environment configuration system
- **Database Migrations:** Automated via Laravel's migration system
- **Asset Compilation:** Vite build process integrated with deployment pipeline
- **Deployment Strategy:** Zero-downtime deployments with rollback capability

### 10.5 Monitoring and Maintenance
- **Server Monitoring:** Laravel Forge server metrics and alerts
- **Application Monitoring:** Laravel's built-in logging and error tracking
- **Performance Monitoring:** Database query optimization and React component profiling
- **Security Updates:** Automated server security updates via Laravel Forge
- **CI/CD Monitoring:** GitHub Actions workflow execution monitoring and failure notifications
- **Dependency Management:** Automated security vulnerability scanning and dependency updates
- **Code Quality Monitoring:** Continuous code quality metrics and technical debt tracking

---

## Appendix A: Form 138
Official school record form required for student transfer between schools in the Philippines.

## Appendix B: Good Moral Certificate
Document certifying a student's good moral character and conduct from their previous school.

---

**Document Control:**
- Initial Version: 1.0 (January 2025) - Original requirements specification
- Version 2.0: (January 2025) - Updated with actual technology stack implementation
- Version 3.0: (January 2025) - Updated with Laravel 12 and GitHub Actions CI/CD pipeline
- Technology Stack: Laravel 12 + React 18 + Inertia.js + shadcn/ui + Tailwind CSS
- CI/CD Pipeline: GitHub Actions (CI) + Laravel Forge (CD)
- Development Environment: Docker Compose with Laravel Sail
- Deployment Platform: Linode Nanode managed by Laravel Forge
- Project Status: Core components pre-scaffolded, CI/CD pipeline configured
- Document Status: Updated Technical Specification with Modern CI/CD
- Next Review Date: February 2025
- Approved By: [To be completed]

---

*This document serves as the official Software Requirements Specification for the Web-Based Enrollment System for Christian Bible Heritage Learning Center. All stakeholders should review and approve this document before development commences.*