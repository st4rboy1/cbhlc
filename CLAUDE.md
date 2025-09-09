# Software Requirements Specification (SRS)
## Web-Based Enrollment System for Christian Bible Heritage Learning Center

**Document Version:** 1.0  
**Date:** January 2025  
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
- **Client Side:** Web browsers (Chrome, Firefox, Safari, Edge)
- **Server Side:** Apache HTTP Server with PHP runtime
- **Database:** MySQL Server
- **Platform:** Cross-platform web-based solution
- **Internet Connectivity:** Required for system access

### 2.5 Assumptions and Dependencies
- Users have access to internet-connected devices with web browsers
- CBHLC will provide necessary hosting infrastructure
- Staff training will be provided for system usage
- Current enrollment policies and procedures remain applicable

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
- **UI-1:** Interface shall be responsive and accessible across devices
- **UI-2:** Design shall incorporate CBHLC branding and logo
- **UI-3:** Navigation shall be intuitive and consistent
- **UI-4:** Forms shall include appropriate input validation and error messaging
- **UI-5:** Interface shall support accessibility standards (WCAG 2.1)

#### 4.1.2 Specific Interface Components
- **Login Page:** Secure authentication interface
- **Dashboard:** Role-specific information display
- **Enrollment Form:** Multi-step form with validation
- **Document Upload:** File selection and upload interface
- **Administrative Panels:** Data management interfaces
- **Reporting Interface:** Report generation and viewing tools

### 4.2 Hardware Interfaces
- **HI-1:** System shall operate on standard web server hardware
- **HI-2:** Client access via standard computing devices (desktop, laptop, mobile)
- **HI-3:** File storage shall support document management requirements

### 4.3 Software Interfaces

#### 4.3.1 Web Server Interface
- **SI-1:** Apache HTTP Server 2.4.58 or compatible
- **SI-2:** PHP 8.2+ runtime environment
- **SI-3:** SSL/TLS encryption for secure communications

#### 4.3.2 Database Interface
- **SI-4:** MySQL 8.3.0 or compatible database management system
- **SI-5:** Database connection pooling for performance optimization
- **SI-6:** Backup and recovery capabilities

#### 4.3.3 Browser Compatibility
- **SI-7:** Google Chrome (latest version)
- **SI-8:** Mozilla Firefox (latest version)
- **SI-9:** Microsoft Edge (latest version)
- **SI-10:** Safari (latest version)

---

## 5. Non-Functional Requirements

### 5.1 Performance Requirements
- **NFR-1.1:** Page load times shall not exceed 3 seconds under normal conditions
- **NFR-1.2:** System shall support concurrent access by up to 100 users
- **NFR-1.3:** File uploads shall complete within 30 seconds for 50MB files
- **NFR-1.4:** Database queries shall execute within 2 seconds
- **NFR-1.5:** System downtime shall not exceed 1% annually

### 5.2 Security Requirements
- **NFR-2.1:** All user authentication shall be encrypted
- **NFR-2.2:** Personal data shall be protected according to privacy standards
- **NFR-2.3:** Role-based access control shall prevent unauthorized data access
- **NFR-2.4:** System shall maintain audit logs of all user activities
- **NFR-2.5:** File uploads shall be scanned for security threats

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
The system follows a three-tier architecture:
- **Presentation Layer:** Web browser interface (HTML/CSS/JavaScript)
- **Application Layer:** PHP-based business logic and processing
- **Data Layer:** MySQL database for data persistence

### 6.2 Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript ES2023
- **Backend:** PHP 8.2+
- **Database:** MySQL 8.3+
- **Web Server:** Apache HTTP Server 2.4+
- **Development Methodology:** Agile with iterative sprints

### 6.3 Security Architecture
- **Authentication:** Session-based with secure password hashing
- **Authorization:** Role-based access control (RBAC)
- **Data Protection:** SSL/TLS encryption for data transmission
- **File Security:** Upload validation and secure storage

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
- **CONS-12:** Standard web technologies for maintainability
- **CONS-13:** Single-school deployment (not multi-tenant)

### 8.4 Assumptions
- **ASS-1:** Users have basic computer and internet skills
- **ASS-2:** Reliable internet connectivity available
- **ASS-3:** School provides necessary hosting environment
- **ASS-4:** Current enrollment procedures remain applicable
- **ASS-5:** Staff will receive adequate system training

---

## 9. Glossary

| Term | Definition |
|------|------------|
| **CBHLC** | Christian Bible Heritage Learning Center |
| **RBAC** | Role-Based Access Control |
| **SRS** | Software Requirements Specification |
| **UI** | User Interface |
| **API** | Application Programming Interface |
| **SSL/TLS** | Secure Sockets Layer/Transport Layer Security |
| **CRUD** | Create, Read, Update, Delete operations |
| **MVP** | Minimum Viable Product |
| **UAT** | User Acceptance Testing |
| **GUI** | Graphical User Interface |

---

## Appendix A: Form 138
Official school record form required for student transfer between schools in the Philippines.

## Appendix B: Good Moral Certificate
Document certifying a student's good moral character and conduct from their previous school.

---

**Document Control:**
- Initial Version: 1.0 (January 2025)
- Document Status: Draft for Review
- Next Review Date: February 2025
- Approved By: [To be completed]

---

*This document serves as the official Software Requirements Specification for the Web-Based Enrollment System for Christian Bible Heritage Learning Center. All stakeholders should review and approve this document before development commences.*