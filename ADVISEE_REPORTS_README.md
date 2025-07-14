# Advisee Reports Feature

This feature allows advisors to generate comprehensive reports for their advisees with real backend data integration.

## Features Implemented

### 1. Student Selection
- **Real Data**: Fetches actual advisee data from the backend API
- **Search & Filter**: Filter by program (CS, IS, SE, IT) and academic year
- **Bulk Selection**: Select multiple students or select all visible students
- **Visual Indicators**: Shows GPA status with color coding

### 2. Report Configuration
- **Report Types**:
  - Comprehensive Report: Complete academic performance with all details
  - Summary Report: Key metrics and performance overview
  - At-Risk Analysis: Focus on struggling students and interventions
  - Progress Tracking: Semester-by-semester progress analysis

- **Customizable Sections**:
  - Academic Performance & GPA Trends
  - Course-by-Course Breakdown
  - Attendance Analysis
  - Advisor Meeting History
  - Risk Assessment & Interventions
  - Recommendations & Action Items
  - Peer Comparison Analysis
  - Visual Charts & Graphs

- **Output Formats**: PDF, Excel, HTML

### 3. Report Generation
- **Real Backend Integration**: Uses actual API endpoints
- **Progress Indicators**: Shows loading states during generation
- **Automatic Download**: Downloads generated reports
- **History Tracking**: Saves report records for future access

### 4. Recent Reports
- **Report History**: View previously generated reports
- **Download & Share**: Easy access to past reports
- **Report Details**: Shows type, student count, and generation date

## Backend Endpoints

### Get Advisees
```
GET /advisors/{advisor_id}/advisees
```
Returns list of advisees with GPA, program, year, and status information.

### Generate Report
```
POST /advisors/{advisor_id}/reports/generate
```
Generates a new report based on selected students and configuration.

### Get Recent Reports
```
GET /advisors/{advisor_id}/reports/recent
```
Returns list of recently generated reports.

### Download Report
```
GET /advisors/{advisor_id}/reports/{report_id}/download
```
Downloads a specific report file.

## Database Schema

### advisor_reports Table
- `id`: Primary key
- `advisor_id`: Foreign key to users table
- `title`: Report title
- `type`: Report type (comprehensive, summary, at-risk, progress)
- `student_count`: Number of students included
- `format`: Output format (pdf, excel, html)
- `report_data`: JSON data of the report
- `created_at`: Timestamp

## User Experience Improvements

1. **Loading States**: Shows spinners during data fetching
2. **Error Handling**: Displays user-friendly error messages
3. **Notifications**: Toast notifications instead of browser alerts
4. **Responsive Design**: Works on mobile and desktop
5. **Smart Defaults**: Pre-selects common report options

## Setup Instructions

1. **Database Setup**: Run the SQL script to create the `advisor_reports` table:
   ```bash
   php backend/skorsiswa-api/setup_reports_table.php
   ```

2. **Backend**: The API endpoints are automatically available in `index.php`

3. **Frontend**: The Vue component is ready to use in the advisor dashboard

## Usage

1. **Select Students**: Choose advisees from the list using checkboxes
2. **Configure Report**: Select report type, sections, and format
3. **Generate**: Click "Generate Report" to create and download
4. **Access History**: View and download previous reports from the "Recent Reports" section

## Benefits

- **Real Data Integration**: No more mock data - uses actual student information
- **Comprehensive Reporting**: Multiple report types for different needs
- **Easy to Use**: Simplified interface with clear steps
- **Professional Output**: Properly formatted reports for sharing
- **Audit Trail**: Keeps history of all generated reports
