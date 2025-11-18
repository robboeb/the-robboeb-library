# KHLIBRARY - Library Management System

## Project Overview

KHLIBRARY is a comprehensive full-stack web application designed to streamline library operations and enhance user experience in book borrowing and management. The system provides a robust platform for managing book inventories, user accounts, loan transactions, and administrative oversight.

### Core Functionalities

- **User Management**: Role-based access control supporting administrators and patrons with distinct privileges
- **Book Catalog Management**: Comprehensive book inventory system with metadata, categorization, and availability tracking
- **Loan Processing**: Automated borrowing workflow with request submission, approval, and return mechanisms
- **Real-time Availability**: Dynamic book availability tracking with quantity management
- **Administrative Dashboard**: Centralized control panel for system oversight and reporting
- **Search and Discovery**: Advanced book search with filtering by title, author, and category
- **User Profile Management**: Self-service portal for viewing borrowed books and loan history

### System Capabilities

The application supports multi-user concurrent access, implements secure authentication mechanisms, and provides comprehensive audit trails for all transactions. The system enforces business rules including loan periods, overdue tracking, and fine calculations.

## Technology Stack

### Frontend Technologies

- **HTML5**: Semantic markup for structured content presentation
- **CSS3**: Modern styling with custom properties, flexbox, and grid layouts
- **JavaScript (ES6+)**: Client-side interactivity and asynchronous operations
- **Font Awesome 6.4.0**: Icon library for consistent visual elements

### Backend Technologies

- **PHP 7.4+**: Server-side scripting language for business logic implementation
- **MySQL 5.7+**: Relational database management system for data persistence
- **Apache HTTP Server**: Web server with mod_rewrite for URL routing

### Architecture Patterns

- **MVC (Model-View-Controller)**: Separation of concerns for maintainable codebase
- **RESTful API**: Standardized HTTP methods for resource manipulation
- **Service Layer Pattern**: Business logic encapsulation in dedicated service classes
- **Repository Pattern**: Data access abstraction through model classes

### Development Tools

- **Git**: Version control system for source code management
- **Hoppscotch**: API testing and documentation platform
- **Visual Studio Code**: Integrated development environment


## Application Architecture

### High-Level System Design

The application follows a three-tier architecture comprising presentation, application, and data layers. The presentation layer handles user interface rendering and client-side interactions. The application layer processes business logic through controllers and services. The data layer manages persistence through models and database abstraction.

### Component Interaction Flow

```
Client Browser → Apache Server → PHP Router → Controller → Service → Model → Database
                                      ↓
                                   View (PHP Templates)
```

### Directory Structure

```
the-robboeb-library/
├── api/                    # RESTful API endpoints
│   └── v1/
│       ├── controllers/    # API request handlers
│       └── index.php       # API router
├── config/                 # Configuration files
│   ├── constants.php       # Application constants
│   └── database.php        # Database configuration
├── database/               # SQL schema and migrations
├── logs/                   # Application error logs
├── public/                 # Publicly accessible files
│   ├── admin/             # Administrative interface
│   ├── assets/            # Static resources (CSS, JS, images)
│   ├── user/              # User interface
│   ├── browse.php         # Book catalog page
│   ├── login.php          # Authentication page
│   └── index.php          # Application entry point
└── src/                   # Application source code
    ├── controllers/       # Business logic controllers
    ├── helpers/           # Utility functions
    ├── middleware/        # Request/response interceptors
    ├── models/            # Data models and ORM
    └── services/          # Business service layer
```

### Security Architecture

- **Session Management**: Secure session handling with configurable timeout (1800 seconds)
- **Authentication Service**: Centralized authentication with role-based access control
- **Password Security**: Hashed password storage using bcrypt algorithm
- **SQL Injection Prevention**: Prepared statements with parameterized queries
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token-based request validation for state-changing operations


## Database Schema and Entity Relationships

### Entity Relationship Diagram

```mermaid
erDiagram
    USERS ||--o{ LOANS : creates
    BOOKS ||--o{ LOANS : "borrowed in"
    BOOKS }o--|| CATEGORIES : "belongs to"
    BOOKS }o--o{ AUTHORS : "written by"
    BOOK_AUTHORS }o--|| BOOKS : references
    BOOK_AUTHORS }o--|| AUTHORS : references

    USERS {
        int user_id PK
        string email UK
        string password_hash
        string first_name
        string last_name
        enum user_type
        enum status
        datetime created_at
        datetime updated_at
    }

    BOOKS {
        int book_id PK
        string isbn UK
        string title
        text description
        int category_id FK
        int total_quantity
        int available_quantity
        string cover_image
        int publication_year
        string publisher
        datetime created_at
        datetime updated_at
    }

    AUTHORS {
        int author_id PK
        string first_name
        string last_name
        text biography
        datetime created_at
    }

    CATEGORIES {
        int category_id PK
        string name UK
        text description
        datetime created_at
    }

    LOANS {
        int loan_id PK
        int user_id FK
        int book_id FK
        date loan_date
        date due_date
        date return_date
        enum status
        datetime created_at
        datetime updated_at
    }

    BOOK_AUTHORS {
        int book_id FK
        int author_id FK
    }
```


### Application Workflow Sequence

```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant Server
    participant AuthService
    participant Controller
    participant Model
    participant Database

    User->>Browser: Access Application
    Browser->>Server: HTTP Request
    Server->>AuthService: Check Session
    
    alt User Not Authenticated
        AuthService-->>Server: Redirect to Login
        Server-->>Browser: Login Page
        Browser-->>User: Display Login Form
        User->>Browser: Submit Credentials
        Browser->>Server: POST /login
        Server->>AuthService: Validate Credentials
        AuthService->>Model: Query User
        Model->>Database: SELECT user
        Database-->>Model: User Data
        Model-->>AuthService: User Object
        AuthService->>AuthService: Verify Password
        AuthService->>Server: Create Session
        Server-->>Browser: Redirect to Dashboard
    else User Authenticated
        Server->>Controller: Route Request
        Controller->>Model: Fetch Data
        Model->>Database: Execute Query
        Database-->>Model: Result Set
        Model-->>Controller: Data Objects
        Controller->>Server: Render View
        Server-->>Browser: HTML Response
        Browser-->>User: Display Content
    end
```


### Book Borrowing Workflow

```mermaid
flowchart TD
    A[User Browses Books] --> B{Book Available?}
    B -->|Yes| C[Submit Borrow Request]
    B -->|No| D[View Unavailable Status]
    C --> E[Request Pending Approval]
    E --> F{Admin Reviews}
    F -->|Approve| G[Set Due Date]
    F -->|Reject| H[Notify User - Rejected]
    G --> I[Update Book Quantity]
    I --> J[Create Active Loan]
    J --> K[Notify User - Approved]
    K --> L[User Has Book]
    L --> M{Return Book?}
    M -->|Yes| N[Submit Return Request]
    N --> O[Process Return]
    O --> P[Update Book Quantity]
    P --> Q[Calculate Fine if Overdue]
    Q --> R[Mark Loan as Returned]
    R --> S[Update User History]
    M -->|No| T{Check Due Date}
    T -->|Overdue| U[Calculate Daily Fine]
    T -->|Not Due| L
```


## User Flow Guide

### Public User Journey

#### 1. Initial Access
- User navigates to application URL
- System redirects to Browse Books page (homepage eliminated in redesign)
- Navigation bar displays: Browse Books, User Profile (if authenticated), Login/Logout

#### 2. Book Discovery
- User views book catalog in range-style grid layout
- Each book displays: cover image, title, author, availability status
- User can search by title or author
- User can filter by category
- Click on book card to view detailed information

#### 3. Authentication Flow
- Click Login button in navigation
- Enter email and password credentials
- System validates credentials
- Upon success, redirect to Browse Books or Admin Dashboard based on role
- Session created with 30-minute timeout

### Patron User Journey

#### 4. Book Borrowing Process
- Browse available books
- Click "Borrow" button on desired book
- Confirm borrowing request in dialog (displays 14-day loan period)
- Request submitted with "pending" status
- User redirected to profile to view pending requests

#### 5. Profile Management
- Navigate to User Profile from navigation bar
- View statistics: Currently Borrowed, Pending Requests, Overdue Books, Due Soon
- Pending Requests section displays books awaiting admin approval
- Currently Borrowed section shows active loans with:
  - Book cover, title, author
  - Borrowed date and due date
  - Days remaining or overdue status
  - Return button for each book

#### 6. Book Return Process
- In Currently Borrowed section, click "Return Book" button
- Confirm return action in dialog
- System processes return immediately
- Book quantity updated
- Loan marked as returned
- User receives success notification

