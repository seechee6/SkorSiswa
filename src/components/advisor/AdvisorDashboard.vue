<template>
  <div class="advisor-dashboard">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Advisor Dashboard</h2>
        <p class="welcome-text">Welcome back, {{ advisorName }}!</p>
      </div>
      <div class="header-actions">
        <button @click="refreshDashboard" class="refresh-btn">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
            <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="dashboard-cards">
      <div class="card overview-card">
        <div class="card-icon advisees-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ totalAdvisees }}</div>
          <div class="card-label">Total Advisees</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon at-risk-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ atRiskStudents }}</div>
          <div class="card-label">At-Risk Students</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon meetings-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ upcomingMeetings }}</div>
          <div class="card-label">Upcoming Meetings</div>
        </div>
      </div>

      <div class="card overview-card">
        <div class="card-icon reports-icon">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="card-content">
          <div class="card-value">{{ pendingReports }}</div>
          <div class="card-label">Pending Reports</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card quick-actions">
      <h3>Quick Actions</h3>
      <div class="action-buttons">
        <router-link to="/advisor/advisee-list" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
          </svg>
          View Advisees
        </router-link>

        <router-link to="/advisor/at-risk-students" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          At-Risk Students
        </router-link>

        <router-link to="/advisor/meeting-notes" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
          </svg>
          Record Meeting Notes
        </router-link>

        <router-link to="/advisor/advisee-reports" class="action-btn">
          <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
          </svg>
          Generate Reports
        </router-link>
      </div>
    </div>

    <!-- Recent Activities and At-Risk Students Section -->
    <div class="dashboard-columns">
      <!-- Recent Activities -->
      <div class="card recent-activities">
        <h3>Recent Activities</h3>
        <div class="activity-list">
          <div class="activity-item" v-for="(activity, index) in recentActivities" :key="index">
            <div class="activity-icon" :class="activity.type">
              <svg v-if="activity.type === 'meeting'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <svg v-if="activity.type === 'note'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              <svg v-if="activity.type === 'report'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="activity-content">
              <p class="activity-text">{{ activity.text }}</p>
              <p class="activity-time">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- At-Risk Students -->
      <div class="card at-risk-students">
        <div class="card-header">
          <h3>At-Risk Students</h3>
          <router-link to="/advisor/at-risk-students" class="view-all">
            View All
          </router-link>
        </div>
        <div class="student-list">
          <div class="student-item" v-for="(student, index) in atRiskStudentsList" :key="index">
            <div class="student-avatar">{{ getInitials(student.name) }}</div>
            <div class="student-info">
              <p class="student-name">{{ student.name }}</p>
              <p class="student-details">{{ student.id }} | {{ student.program }}</p>
            </div>
            <div class="risk-indicator" :class="student.riskLevel">
              {{ student.riskLevel }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upcoming Meetings Section -->
    <div class="card upcoming-meetings">
      <div class="card-header">
        <h3>Upcoming Meetings</h3>
        <router-link to="/advisor/meeting-notes" class="view-all">
          Schedule New
        </router-link>
      </div>
      <table class="meetings-table">
        <thead>
          <tr>
            <th>Advisee</th>
            <th>Date</th>
            <th>Time</th>
            <th>Purpose</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(meeting, index) in upcomingMeetingsList" :key="index">
            <td>{{ meeting.advisee }}</td>
            <td>{{ meeting.date }}</td>
            <td>{{ meeting.time }}</td>
            <td>{{ meeting.purpose }}</td>
            <td><span class="meeting-status" :class="meeting.status.toLowerCase()">{{ meeting.status }}</span></td>
            <td class="actions-cell">
              <button class="action-icon edit">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
              </button>
              <button class="action-icon delete">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script>
export default {
  name: 'AdvisorDashboard',
  data() {
    return {
      advisorName: 'Dr. Sarah Johnson',
      totalAdvisees: 42,
      atRiskStudents: 5,
      upcomingMeetings: 3,
      pendingReports: 7,
      recentActivities: [
        {
          type: 'meeting',
          text: 'Scheduled meeting with John Smith',
          time: '2 hours ago'
        },
        {
          type: 'note',
          text: 'Added meeting notes for Alicia Wong',
          time: 'Yesterday'
        },
        {
          type: 'report',
          text: 'Generated performance report for David Lee',
          time: '2 days ago'
        },
        {
          type: 'meeting',
          text: 'Completed meeting with Michael Johnson',
          time: '3 days ago'
        },
        {
          type: 'note',
          text: 'Updated progress notes for Sarah Williams',
          time: '5 days ago'
        }
      ],
      atRiskStudentsList: [
        {
          name: 'John Smith',
          id: 'S12345',
          program: 'Computer Science',
          riskLevel: 'high'
        },
        {
          name: 'Alicia Wong',
          id: 'S23456',
          program: 'Data Science',
          riskLevel: 'medium'
        },
        {
          name: 'David Lee',
          id: 'S34567',
          program: 'Computer Science',
          riskLevel: 'high'
        },
        {
          name: 'Sarah Williams',
          id: 'S45678',
          program: 'Information Systems',
          riskLevel: 'medium'
        }
      ],
      upcomingMeetingsList: [
        {
          advisee: 'John Smith',
          date: '15 Jun 2025',
          time: '10:00 AM',
          purpose: 'Academic Progress Review',
          status: 'Confirmed'
        },
        {
          advisee: 'Alicia Wong',
          date: '16 Jun 2025',
          time: '2:00 PM',
          purpose: 'Course Registration Advice',
          status: 'Pending'
        },
        {
          advisee: 'David Lee',
          date: '18 Jun 2025',
          time: '11:30 AM',
          purpose: 'Career Guidance',
          status: 'Confirmed'
        }
      ]
    }
  },
  methods: {
    refreshDashboard() {
      // Implement refresh functionality
      alert('Dashboard refreshed!')
    },
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
    }
  }
}
</script>

<style scoped>
.advisor-dashboard {
  padding: 0 16px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-content h2 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  color: #1D3557;
}

.welcome-text {
  margin: 8px 0 0;
  color: #6c757d;
  font-size: 16px;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: #fff;
  border: 1px solid #dee2e6;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
  color: #1D3557;
}

.refresh-btn:hover {
  border-color: #457B9D;
  background-color: #f8f9fa;
  transform: translateY(-1px);
}

.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  overflow: hidden;
  border: 1px solid #f1f3f4;
}

.overview-card {
  display: flex;
  padding: 24px;
  align-items: center;
  gap: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon svg {
  width: 24px;
  height: 24px;
}

.advisees-icon {
  background: #e3f2fd;
  color: #1565c0;
}

.at-risk-icon {
  background: #fff3e0;
  color: #e65100;
}

.meetings-icon {
  background: #e8f5e9;
  color: #2e7d32;
}

.reports-icon {
  background: #f3e5f5;
  color: #6a1b9a;
}

.card-content {
  display: flex;
  flex-direction: column;
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: #1D3557;
  line-height: 1.2;
}

.card-label {
  font-size: 14px;
  color: #6c757d;
}

.quick-actions {
  padding: 24px;
  margin-bottom: 24px;
}

.quick-actions h3 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 18px;
  color: #1D3557;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  background: #f8f9fa;
  color: #1D3557;
  text-decoration: none;
  transition: all 0.2s;
  border: 1px solid #f1f3f4;
}

.action-btn:hover {
  background: #e8f4fd;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  border-color: #A8DADC;
}

.btn-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.dashboard-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

.recent-activities, .at-risk-students {
  padding: 24px;
}

.recent-activities h3, .at-risk-students h3, .upcoming-meetings h3 {
  margin-top: 0;
  margin-bottom: 16px;
  font-size: 18px;
  color: #1D3557;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.activity-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  flex-shrink: 0;
}

.activity-icon.meeting {
  background: #e8f5e9;
  color: #2e7d32;
}

.activity-icon.note {
  background: #e3f2fd;
  color: #1565c0;
}

.activity-icon.report {
  background: #f3e5f5;
  color: #6a1b9a;
}

.activity-icon svg {
  width: 16px;
  height: 16px;
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-text {
  font-size: 14px;
  font-weight: 500;
  color: #1D3557;
  margin-bottom: 4px;
  margin-top: 0;
  word-wrap: break-word;
}

.activity-time {
  font-size: 12px;
  color: #6c757d;
  margin: 0;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.view-all {
  font-size: 14px;
  color: #457B9D;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 4px;
}

.view-all:hover {
  text-decoration: underline;
}

.student-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.student-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #457B9D;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
}

.student-info {
  flex: 1;
  min-width: 0;
}

.student-name {
  font-size: 14px;
  font-weight: 500;
  color: #1D3557;
  margin: 0;
  margin-bottom: 4px;
}

.student-details {
  font-size: 12px;
  color: #6c757d;
  margin: 0;
}

.risk-indicator {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.risk-indicator.high {
  background: #ffebee;
  color: #c62828;
}

.risk-indicator.medium {
  background: #fff8e1;
  color: #ff8f00;
}

.risk-indicator.low {
  background: #e8f5e9;
  color: #2e7d32;
}

.upcoming-meetings {
  padding: 24px;
  margin-bottom: 24px;
}

.meetings-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.meetings-table th {
  text-align: left;
  padding: 12px 16px;
  border-bottom: 1px solid #e9ecef;
  color: #6c757d;
  font-weight: 600;
}

.meetings-table td {
  padding: 12px 16px;
  border-bottom: 1px solid #f1f3f4;
  color: #1D3557;
}

.meeting-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.meeting-status.confirmed {
  background: #e8f5e9;
  color: #2e7d32;
}

.meeting-status.pending {
  background: #fff8e1;
  color: #ff8f00;
}

.meeting-status.cancelled {
  background: #ffebee;
  color: #c62828;
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.action-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.action-icon svg {
  width: 16px;
  height: 16px;
}

.action-icon.edit {
  color: #457B9D;
}

.action-icon.delete {
  color: #e63946;
}

.action-icon:hover {
  background: #f1f3f4;
}

@media (max-width: 992px) {
  .dashboard-columns {
    grid-template-columns: 1fr;
  }
  
  .dashboard-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
}
</style>
