# SkorSiswa - Academic Management System

## 📚 Overview

SkorSiswa is a comprehensive web-based Academic Management System designed for universities to manage student marks, course enrollments, and academic performance tracking. The system provides role-based access for administrators, lecturers, students, and academic advisors.

## 🏗️ System Architecture

### Frontend
- **Framework**: Vue.js 3.2.13 with Vue Router 4.5.1
- **UI Components**: Custom responsive components with modern design
- **Charts**: Chart.js 4.4.9 for data visualization
- **HTTP Client**: Axios 1.9.0 for API communication
- **Styling**: Custom CSS with responsive design

### Backend
- **Language**: PHP with Slim Framework
- **Database**: MySQL/MariaDB
- **API**: RESTful API architecture
- **Authentication**: Session-based authentication with role management

## 👥 User Roles & Features

### 🔧 Administrator
- **Dashboard**: System overview with statistics
- **User Management**: Create, edit, deactivate users
- **Course Management**: Create and manage courses
- **Student Enrollments**: Manage student course enrollments
- **Lecturer Assignments**: Assign lecturers to courses
- **Password Management**: Reset user passwords
- **System Logs**: Monitor system activities

### 👨‍🏫 Lecturer
- **Dashboard**: Course overview and statistics
- **Course Management**: Create and manage own courses
- **Enrollment Management**: Enroll/remove students
- **Assessment Management**: Create and manage assessments
- **Mark Entry**: Enter and update student marks
- **Bulk Operations**: CSV upload/export for marks
- **Analytics**: Performance analysis and trends
- **Remark Reviews**: Handle student remark requests
- **Feedback & Remarks**: Provide student feedback
- **Notifications**: Mark update notifications

### 🎓 Student
- **Dashboard**: Personal academic overview
- **Mark Breakdown**: Detailed mark analysis by course
- **Performance Trends**: GPA and academic progress tracking
- **Course Comparison**: Compare performance with classmates
- **Ranking**: View class rankings
- **Remark Requests**: Submit mark review requests
- **Notifications**: Receive grade and course updates

### 👩‍🏫 Academic Advisor
- **Dashboard**: Overview of advised students
- **Student Monitoring**: Track student performance
- **Advisory Notes**: Maintain student consultation records

## 🗄️ Database Schema

The system uses a comprehensive MySQL database with the following main tables:

- **users**: User accounts with role-based access
- **roles**: System roles (admin, lecturer, student, advisor)
- **courses**: Course information and assignments
- **enrollments**: Student-course relationships
- **assessments**: Course assessment components
- **assessment_marks**: Individual assessment scores
- **final_exam_marks**: Final examination results
- **remark_requests**: Student mark review requests
- **notifications**: System notifications
- **feedback_remarks**: Lecturer feedback to students
- **advisors**: Advisor-student relationships
- **advisor_notes**: Advisory consultation records
- **system_logs**: System activity tracking

## 🚀 Installation & Setup

### Prerequisites
- Node.js (v14 or higher)
- PHP (v7.4 or higher)
- MySQL/MariaDB
- Web server (Apache/Nginx)

### Frontend Setup
```bash
# Clone the repository
git clone <repository-url>
cd SkorSiswa

# Install dependencies
npm install

# Development server
npm run serve

# Production build
npm run build
```

### Backend Setup
```bash
# Navigate to backend directory
cd backend/skorsiswa-api

# Install PHP dependencies
composer install

# Configure database
# Update database connection in config files

# Import database schema
mysql -u username -p database_name < database.sql
```

### Environment Configuration
```bash
# Frontend (vue.config.js)
# Configure API endpoint

# Backend
# Set up database credentials
# Configure CORS settings
```

## 📱 Features Breakdown

### Academic Management
- **Multi-semester support**: Handle different academic periods
- **Weighted assessments**: Flexible grading schemes
- **Grade calculations**: Automatic GPA computation
- **Performance tracking**: Longitudinal academic monitoring

### User Experience
- **Responsive design**: Mobile-friendly interface
- **Real-time notifications**: Instant updates
- **Interactive charts**: Visual data representation
- **Export capabilities**: CSV data export
- **Bulk operations**: Efficient data management

### Security & Administration
- **Role-based access control**: Secure user permissions
- **Password management**: Administrative password resets
- **Activity logging**: Comprehensive audit trails
- **Data validation**: Input sanitization and validation

## 🎯 Key Functionalities

### Mark Management
- Individual assessment mark entry
- Bulk CSV upload/download
- Automatic grade calculations
- Mark update notifications
- Remark request system

### Analytics & Reporting
- Student performance trends
- Class performance distribution
- Course analytics for lecturers
- Comparative analysis tools

### Communication
- In-app notification system
- Remark request workflow
- Feedback and remarks system
- Advisory notes management

## 📊 Technical Specifications

### Frontend Dependencies
```json
{
  "vue": "^3.2.13",
  "vue-router": "^4.5.1",
  "axios": "^1.9.0",
  "chart.js": "^4.4.9",
  "vue-chartjs": "^5.3.2"
}
```

### API Endpoints
- Authentication: `/login`, `/logout`
- Users: `/api/admin/users`
- Courses: `/courses`, `/api/admin/courses`
- Enrollments: `/enrollments`, `/api/admin/enrollments`
- Marks: `/student/marks`, `/lecturer/marks`
- Notifications: `/notifications`
- Analytics: `/analytics`, `/performance`

## 🔧 Development

### Project Structure
```
SkorSiswa/
├── src/
│   ├── components/          # Vue components
│   │   ├── admin/          # Admin interface
│   │   ├── lecturer/       # Lecturer interface
│   │   ├── student/        # Student interface
│   │   └── advisor/        # Advisor interface
│   ├── router/             # Vue Router configuration
│   ├── api.js              # API client configuration
│   └── main.js             # Application entry point
├── backend/
│   └── skorsiswa-api/      # PHP backend
│       ├── src/            # PHP source code
│       ├── public/         # Public API endpoints
│       └── database.sql    # Database schema
└── public/                 # Static assets
```

### Development Commands
```bash
# Start development server
npm run serve

# Lint code
npm run lint

# Build for production
npm run build
```

## 🛡️ Security Features

- Password hashing with bcrypt
- SQL injection prevention
- XSS protection
- Role-based authorization
- Session management
- Input validation and sanitization

## 📈 Performance Features

- Lazy loading of components
- Optimized database queries
- Responsive caching strategies
- Efficient data pagination
- Minimized API calls

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check documentation for troubleshooting

## 🔄 Version History

- **v0.1.0**: Initial release with core functionality
- Multi-role support
- Basic academic management features
- Responsive design implementation

---

**SkorSiswa** - Empowering Academic Excellence Through Technology
