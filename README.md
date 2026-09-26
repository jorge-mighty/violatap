# violatap
Application Development and Emerging Technologies, and System Administration and Maintenance Project open for review

===========================================================================
||            PLEASE HELP ME DEVELOP THIS APPLICATION                    ||
===========================================================================

a Local area network (LAN)-based student violation tracking and management web application developed for partial fulfillment of the course ICT 110 - Application Development and Emerging Technologies.

Technology Stack
Backend: PHP 8+ (Procedural & Object-Oriented with MySQLi Prepared Statements)
Database: MySQL / MariaDB (Relational schema managed via local XAMPP instance)
Frontend: HTML5, CSS3 (Custom variables, glassmorphism UI components), Vanilla JavaScript
Assets & Libraries: Chart.js (Analytics visualization), Custom SVG icon pack
Environment: Apache HTTP Server (Strictly local LAN configuration)

ViolaTap/
│
├── assets/                  # Images, fonts, icons, and client-side JS plugins
├── pages/                   # Modular application view files
│   ├── analytics.php        # System metrics and statistical charts
│   ├── audit_logs.php       # Security and activity tracking trail
│   ├── code_of_discipline_admin.php # Offenses and progressive sanctions registry
│   ├── settings.php         # User profile, system configuration, and DB backup
│   ├── student_nfc_management.php   # Student registry and NFC card mapper
│   ├── user_management.php  # Staff account administration and role delegation
│   └── violation_records.php        # Violation logs, verification, and settlement
│
├── audit_helper.php         # Centralized activity logging helper
├── db_config.php            # Database connection, charset, and RBAC authorization engine
├── dashboard.php            # Secure master shell router and layout controller
├── index.php                # Authentication gateway portal
├── login_process.php        # Secure login controller and session initializer
├── logout.php               # Session termination handler
├── import_students.php      # Bulk CSV student registry processor
├── register_card.php        # NFC card mapping handler
├── update_status.php        # Violation record settlement handler
├── styles.css               # Unified design system and theme stylesheets
└── manifest.json            # Progressive Web App (PWA) manifest configuration
