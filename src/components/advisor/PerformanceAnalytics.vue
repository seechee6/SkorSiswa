<template>
  <div class="performance-analytics">
    <div class="header-section">
      <div class="title-section">
        <h3>Performance Analytics & Trends</h3>
        <p class="page-description">Advanced analytics and performance insights for your advisees</p>
      </div>
      <div class="header-actions">
        <div class="time-range-selector">
          <select v-model="timeRange" @change="updateAnalytics" class="range-select">
            <option value="current">Current Semester</option>
            <option value="academic_year">Academic Year</option>
            <option value="all_time">All Time</option>
          </select>
        </div>
        <button @click="exportAnalytics" class="export-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Export Report
        </button>
        <button @click="refreshData" class="refresh-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Analytics Overview -->
    <div class="analytics-overview">
      <div class="overview-grid">
        <div class="overview-card">
          <div class="card-header">
            <h5>Total Advisees</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div class="card-value">{{ analyticsData.totalAdvisees }}</div>
          <div class="card-change positive">+{{ analyticsData.newAdvisees }} this semester</div>
        </div>

        <div class="overview-card">
          <div class="card-header">
            <h5>Average CGPA</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <div class="card-value">{{ analyticsData.averageCGPA.toFixed(2) }}</div>
          <div class="card-change" :class="cgpaChangeClass">
            {{ cgpaChangeText }}
          </div>
        </div>

        <div class="overview-card">
          <div class="card-header">
            <h5>At-Risk Students</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div class="card-value risk">{{ analyticsData.atRiskStudents }}</div>
          <div class="card-change neutral">{{ analyticsData.riskPercentage }}% of total</div>
        </div>

        <div class="overview-card">
          <div class="card-header">
            <h5>Graduation Rate</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
          </div>
          <div class="card-value">{{ analyticsData.graduationRate }}%</div>
          <div class="card-change positive">On-time graduation</div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
      <div class="charts-grid">
        <!-- GPA Trend Chart -->
        <div class="chart-card">
          <div class="chart-header">
            <h5>GPA Trend Analysis</h5>
            <div class="chart-controls">
              <select v-model="gpaChartType" class="chart-select">
                <option value="line">Line Chart</option>
                <option value="bar">Bar Chart</option>
              </select>
            </div>
          </div>
          <div class="chart-container">
            <div class="trend-chart">
              <div class="chart-legend">
                <div class="legend-item">
                  <div class="legend-color avg"></div>
                  <span>Average CGPA</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color target"></div>
                  <span>Target (3.0)</span>
                </div>
              </div>
              <div class="chart-area">
                <div class="y-axis">
                  <div class="y-label" v-for="val in [4.0, 3.5, 3.0, 2.5, 2.0]" :key="val">{{ val }}</div>
                </div>
                <div class="chart-plot">
                  <div class="target-line"></div>
                  <div v-for="(point, index) in gpaChartData" :key="index" class="chart-point">
                    <div 
                      class="point-bar" 
                      :style="{ height: (point.value / 4 * 100) + '%' }"
                      :title="`${point.period}: ${point.value.toFixed(2)}`"
                    ></div>
                    <div class="point-label">{{ point.period }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Distribution -->
        <div class="chart-card">
          <div class="chart-header">
            <h5>Performance Distribution</h5>
            <div class="chart-controls">
              <select v-model="distributionType" class="chart-select">
                <option value="cgpa">By CGPA</option>
                <option value="grade">By Grade</option>
                <option value="risk">By Risk Level</option>
              </select>
            </div>
          </div>
          <div class="chart-container">
            <div class="distribution-chart">
              <div class="pie-chart">
                <div class="pie-center">
                  <div class="center-value">{{ totalStudents }}</div>
                  <div class="center-label">Students</div>
                </div>
                <div class="pie-segments">
                  <div 
                    v-for="(segment, index) in distributionData" 
                    :key="index"
                    class="pie-segment"
                    :style="{ 
                      background: `conic-gradient(${segment.color} 0deg ${segment.angle}deg, transparent ${segment.angle}deg 360deg)`,
                      transform: `rotate(${segment.startAngle}deg)`
                    }"
                  ></div>
                </div>
              </div>
              <div class="distribution-legend">
                <div v-for="(item, index) in distributionData" :key="index" class="legend-item">
                  <div class="legend-color" :style="{ backgroundColor: item.color }"></div>
                  <span class="legend-text">{{ item.label }}</span>
                  <span class="legend-value">{{ item.count }} ({{ item.percentage }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Performance Heatmap -->
        <div class="chart-card full-width">
          <div class="chart-header">
            <h5>Course Performance Heatmap</h5>
            <div class="chart-controls">
              <select v-model="heatmapSemester" class="chart-select">
                <option value="all">All Semesters</option>
                <option v-for="sem in availableSemesters" :key="sem" :value="sem">
                  Semester {{ sem }}
                </option>
              </select>
            </div>
          </div>
          <div class="chart-container">
            <div class="heatmap-chart">
              <div class="heatmap-header">
                <div class="heatmap-label">Courses</div>
                <div class="heatmap-legend">
                  <div class="legend-gradient">
                    <div class="gradient-bar"></div>
                    <div class="gradient-labels">
                      <span>Poor</span>
                      <span>Average</span>
                      <span>Good</span>
                      <span>Excellent</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="heatmap-grid">
                <div v-for="course in coursePerformanceData" :key="course.code" class="heatmap-row">
                  <div class="course-label">{{ course.code }}</div>
                  <div class="performance-cells">
                    <div 
                      v-for="(score, index) in course.scores" 
                      :key="index"
                      class="performance-cell"
                      :class="getPerformanceClass(score)"
                      :title="`Average: ${score.toFixed(1)}`"
                    >
                      {{ score.toFixed(1) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="detailed-analytics">
      <div class="analytics-grid">
        <!-- Top Performers -->
        <div class="analytics-card">
          <div class="card-header">
            <h5>Top Performers</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
          </div>
          <div class="performers-list">
            <div v-for="student in topPerformers" :key="student.id" class="performer-item">
              <div class="performer-rank">{{ student.rank }}</div>
              <div class="performer-info">
                <div class="performer-name">{{ student.name }}</div>
                <div class="performer-program">{{ student.program }}</div>
              </div>
              <div class="performer-cgpa">{{ student.cgpa.toFixed(2) }}</div>
            </div>
          </div>
        </div>

        <!-- Improvement Tracking -->
        <div class="analytics-card">
          <div class="card-header">
            <h5>Most Improved</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
            </svg>
          </div>
          <div class="improvement-list">
            <div v-for="student in mostImproved" :key="student.id" class="improvement-item">
              <div class="improvement-info">
                <div class="improvement-name">{{ student.name }}</div>
                <div class="improvement-details">{{ student.program }}</div>
              </div>
              <div class="improvement-change">
                <div class="change-value positive">+{{ student.improvement.toFixed(2) }}</div>
                <div class="change-period">{{ student.period }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Analysis -->
        <div class="analytics-card">
          <div class="card-header">
            <h5>Challenging Courses</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div class="courses-list">
            <div v-for="course in challengingCourses" :key="course.code" class="course-item">
              <div class="course-info">
                <div class="course-code">{{ course.code }}</div>
                <div class="course-name">{{ course.name }}</div>
              </div>
              <div class="course-stats">
                <div class="course-avg">{{ course.average.toFixed(1) }}</div>
                <div class="course-fail-rate">{{ course.failRate }}% fail</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Meeting Statistics -->
        <div class="analytics-card">
          <div class="card-header">
            <h5>Meeting Statistics</h5>
            <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
          <div class="meeting-stats">
            <div class="stat-row">
              <span class="stat-label">Total Meetings</span>
              <span class="stat-value">{{ meetingStats.total }}</span>
            </div>
            <div class="stat-row">
              <span class="stat-label">This Month</span>
              <span class="stat-value">{{ meetingStats.thisMonth }}</span>
            </div>
            <div class="stat-row">
              <span class="stat-label">Average Duration</span>
              <span class="stat-value">{{ meetingStats.avgDuration }} min</span>
            </div>
            <div class="stat-row">
              <span class="stat-label">Completion Rate</span>
              <span class="stat-value">{{ meetingStats.completionRate }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recommendations -->
    <div class="recommendations-section">
      <div class="card">
        <div class="card-header">
          <h5>AI-Powered Recommendations</h5>
          <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
          </svg>
        </div>
        <div class="recommendations-list">
          <div v-for="rec in recommendations" :key="rec.id" class="recommendation-item">
            <div class="rec-icon" :class="rec.type">
              <svg v-if="rec.type === 'attention'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <svg v-else-if="rec.type === 'action'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
              </svg>
            </div>
            <div class="rec-content">
              <div class="rec-title">{{ rec.title }}</div>
              <div class="rec-description">{{ rec.description }}</div>
              <div class="rec-students" v-if="rec.students">
                <span class="students-label">Affected students:</span>
                <span class="students-list">{{ rec.students.join(', ') }}</span>
              </div>
            </div>
            <div class="rec-priority" :class="rec.priority">{{ rec.priority }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PerformanceAnalytics',
  data() {
    return {
      timeRange: 'current',
      gpaChartType: 'line',
      distributionType: 'cgpa',
      heatmapSemester: 'all',
      
      analyticsData: {
        totalAdvisees: 28,
        newAdvisees: 5,
        averageCGPA: 3.15,
        cgpaChange: 0.12,
        atRiskStudents: 6,
        riskPercentage: 21,
        graduationRate: 87
      },

      gpaChartData: [
        { period: 'S1', value: 2.8 },
        { period: 'S2', value: 3.0 },
        { period: 'S3', value: 3.1 },
        { period: 'S4', value: 3.15 },
        { period: 'S5', value: 3.2 }
      ],

      distributionData: [
        { label: 'Excellent (3.5+)', count: 8, percentage: 29, color: '#10b981', angle: 104, startAngle: 0 },
        { label: 'Good (3.0-3.5)', count: 12, percentage: 43, color: '#3b82f6', angle: 155, startAngle: 104 },
        { label: 'Average (2.5-3.0)', count: 6, percentage: 21, color: '#f59e0b', angle: 76, startAngle: 259 },
        { label: 'Poor (<2.5)', count: 2, percentage: 7, color: '#ef4444', angle: 25, startAngle: 335 }
      ],

      coursePerformanceData: [
        { code: 'CS201', scores: [3.2, 3.0, 3.5, 3.1, 2.8] },
        { code: 'CS202', scores: [2.9, 3.2, 3.0, 3.3, 3.1] },
        { code: 'MATH201', scores: [2.5, 2.7, 2.9, 2.6, 2.8] },
        { code: 'CS301', scores: [3.4, 3.1, 3.6, 3.2, 3.0] },
        { code: 'CS302', scores: [3.0, 3.3, 3.1, 3.4, 3.2] }
      ],

      topPerformers: [
        { id: 1, rank: 1, name: 'Alice Johnson', program: 'Computer Science', cgpa: 3.85 },
        { id: 2, rank: 2, name: 'David Wilson', program: 'Software Engineering', cgpa: 3.72 },
        { id: 3, rank: 3, name: 'Emma Brown', program: 'Information Systems', cgpa: 3.68 },
        { id: 4, rank: 4, name: 'John Smith', program: 'Computer Science', cgpa: 3.54 },
        { id: 5, rank: 5, name: 'Sarah Davis', program: 'Software Engineering', cgpa: 3.47 }
      ],

      mostImproved: [
        { id: 1, name: 'Bob Smith', program: 'Computer Science', improvement: 0.45, period: 'Last semester' },
        { id: 2, name: 'Carol Davis', program: 'Information Systems', improvement: 0.38, period: 'Last semester' },
        { id: 3, name: 'Mike Johnson', program: 'Software Engineering', improvement: 0.32, period: 'Last semester' },
        { id: 4, name: 'Lisa Wilson', program: 'Computer Science', improvement: 0.28, period: 'Last semester' }
      ],

      challengingCourses: [
        { code: 'MATH201', name: 'Discrete Mathematics', average: 2.6, failRate: 15 },
        { code: 'CS303', name: 'Computer Networks', average: 2.8, failRate: 12 },
        { code: 'CS401', name: 'Software Engineering', average: 2.9, failRate: 10 },
        { code: 'MATH301', name: 'Statistics', average: 3.0, failRate: 8 }
      ],

      meetingStats: {
        total: 142,
        thisMonth: 18,
        avgDuration: 45,
        completionRate: 89
      },

      recommendations: [
        {
          id: 1,
          type: 'attention',
          priority: 'high',
          title: 'Schedule urgent meetings',
          description: 'Several students showing declining performance need immediate attention.',
          students: ['Bob Smith', 'Carol Davis', 'Mike Johnson']
        },
        {
          id: 2,
          type: 'action',
          priority: 'medium',
          title: 'Review MATH201 support',
          description: 'High failure rate in Discrete Mathematics suggests need for additional tutoring support.',
          students: ['Alice Johnson', 'David Wilson']
        },
        {
          id: 3,
          type: 'insight',
          priority: 'low',
          title: 'Celebrate improvements',
          description: 'Acknowledge and celebrate students who have shown significant improvement this semester.'
        }
      ],

      availableSemesters: [1, 2, 3, 4, 5]
    }
  },

  computed: {
    cgpaChangeClass() {
      return this.analyticsData.cgpaChange >= 0 ? 'positive' : 'negative';
    },

    cgpaChangeText() {
      const change = Math.abs(this.analyticsData.cgpaChange);
      const direction = this.analyticsData.cgpaChange >= 0 ? '+' : '-';
      return `${direction}${change.toFixed(2)} from last period`;
    },

    totalStudents() {
      return this.distributionData.reduce((sum, item) => sum + item.count, 0);
    }
  },

  methods: {
    updateAnalytics() {
      // Mock function to update analytics based on time range
      console.log('Updating analytics for range:', this.timeRange);
    },

    refreshData() {
      // Mock function to refresh data
      console.log('Refreshing analytics data...');
    },

    exportAnalytics() {
      // Mock function to export analytics
      console.log('Exporting analytics report...');
    },

    getPerformanceClass(score) {
      if (score >= 3.5) return 'excellent';
      if (score >= 3.0) return 'good';
      if (score >= 2.5) return 'average';
      return 'poor';
    }
  }
}
</script>

<style scoped>
.performance-analytics {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.title-section h3 {
  color: #1f2937;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.page-description {
  color: #6b7280;
  font-size: 16px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.range-select, .chart-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  outline: none;
  cursor: pointer;
}

.range-select:focus, .chart-select:focus {
  border-color: #3b82f6;
}

.export-btn, .refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.export-btn {
  background: #10b981;
  color: white;
}

.export-btn:hover {
  background: #059669;
}

.refresh-btn {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
}

.refresh-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Analytics Overview */
.analytics-overview {
  margin-bottom: 32px;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.overview-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.card-header h5 {
  color: #1f2937;
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.header-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.card-value {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.card-value.risk {
  color: #ef4444;
}

.card-change {
  font-size: 14px;
  font-weight: 500;
}

.card-change.positive {
  color: #10b981;
}

.card-change.negative {
  color: #ef4444;
}

.card-change.neutral {
  color: #6b7280;
}

/* Charts Section */
.charts-section {
  margin-bottom: 32px;
}

.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.chart-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.chart-card.full-width {
  grid-column: 1 / -1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-controls {
  display: flex;
  gap: 8px;
}

/* Trend Chart */
.trend-chart {
  height: 300px;
}

.chart-legend {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.legend-color.avg {
  background: #3b82f6;
}

.legend-color.target {
  background: #ef4444;
}

.chart-area {
  display: flex;
  height: 240px;
}

.y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 40px;
  padding-right: 10px;
}

.y-label {
  font-size: 12px;
  color: #6b7280;
  text-align: right;
}

.chart-plot {
  flex: 1;
  position: relative;
  display: flex;
  align-items: end;
  justify-content: space-between;
  padding: 0 10px;
}

.target-line {
  position: absolute;
  top: 25%;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  opacity: 0.5;
}

.chart-point {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  width: 40px;
}

.point-bar {
  width: 20px;
  background: linear-gradient(to top, #3b82f6, #8b5cf6);
  border-radius: 2px 2px 0 0;
  min-height: 10px;
  transition: height 0.3s ease;
  cursor: pointer;
}

.point-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

/* Distribution Chart */
.distribution-chart {
  display: flex;
  gap: 32px;
  align-items: center;
}

.pie-chart {
  position: relative;
  width: 200px;
  height: 200px;
}

.pie-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  z-index: 10;
}

.center-value {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
}

.center-label {
  font-size: 14px;
  color: #6b7280;
}

.pie-segments {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.pie-segment {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.distribution-legend {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.legend-text {
  flex: 1;
  font-size: 14px;
  color: #374151;
}

.legend-value {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
}

/* Heatmap Chart */
.heatmap-chart {
  min-height: 300px;
}

.heatmap-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.heatmap-label {
  font-weight: 600;
  color: #374151;
}

.heatmap-legend {
  display: flex;
  align-items: center;
  gap: 8px;
}

.gradient-bar {
  width: 200px;
  height: 16px;
  background: linear-gradient(to right, #ef4444, #f59e0b, #3b82f6, #10b981);
  border-radius: 8px;
}

.gradient-labels {
  display: flex;
  justify-content: space-between;
  width: 200px;
  font-size: 12px;
  color: #6b7280;
}

.heatmap-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.heatmap-row {
  display: flex;
  align-items: center;
  gap: 16px;
}

.course-label {
  width: 80px;
  font-weight: 500;
  color: #374151;
  text-align: right;
}

.performance-cells {
  display: flex;
  gap: 4px;
}

.performance-cell {
  width: 60px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  color: white;
  cursor: pointer;
  transition: transform 0.2s;
}

.performance-cell:hover {
  transform: scale(1.1);
}

.performance-cell.excellent {
  background: #10b981;
}

.performance-cell.good {
  background: #3b82f6;
}

.performance-cell.average {
  background: #f59e0b;
}

.performance-cell.poor {
  background: #ef4444;
}

/* Detailed Analytics */
.detailed-analytics {
  margin-bottom: 32px;
}

.analytics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.analytics-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

/* Performers List */
.performers-list, .improvement-list, .courses-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.performer-item, .improvement-item, .course-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.performer-rank {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.performer-info, .improvement-info, .course-info {
  flex: 1;
}

.performer-name, .improvement-name, .course-code {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 2px;
}

.performer-program, .improvement-details, .course-name {
  font-size: 12px;
  color: #6b7280;
}

.performer-cgpa {
  font-weight: 600;
  color: #1f2937;
  font-size: 16px;
}

.improvement-change, .course-stats {
  text-align: right;
}

.change-value {
  font-weight: 600;
  font-size: 16px;
}

.change-value.positive {
  color: #10b981;
}

.change-period, .course-fail-rate {
  font-size: 12px;
  color: #6b7280;
}

.course-avg {
  font-weight: 600;
  color: #1f2937;
  font-size: 16px;
}

/* Meeting Statistics */
.meeting-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
}

.stat-row:last-child {
  border-bottom: none;
}

.stat-label {
  color: #6b7280;
  font-size: 14px;
}

.stat-value {
  font-weight: 600;
  color: #1f2937;
}

/* Recommendations */
.recommendations-section {
  margin-bottom: 32px;
}

.recommendations-section .card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.recommendations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.recommendation-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid transparent;
}

.recommendation-item:has(.rec-priority.high) {
  border-left-color: #ef4444;
}

.recommendation-item:has(.rec-priority.medium) {
  border-left-color: #f59e0b;
}

.recommendation-item:has(.rec-priority.low) {
  border-left-color: #10b981;
}

.rec-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rec-icon svg {
  width: 20px;
  height: 20px;
  color: white;
}

.rec-icon.attention {
  background: #ef4444;
}

.rec-icon.action {
  background: #f59e0b;
}

.rec-icon.insight {
  background: #10b981;
}

.rec-content {
  flex: 1;
}

.rec-title {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.rec-description {
  color: #6b7280;
  font-size: 14px;
  margin-bottom: 8px;
}

.rec-students {
  font-size: 12px;
}

.students-label {
  color: #374151;
  font-weight: 500;
}

.students-list {
  color: #6b7280;
}

.rec-priority {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  align-self: flex-start;
}

.rec-priority.high {
  background: #fee2e2;
  color: #dc2626;
}

.rec-priority.medium {
  background: #fef3c7;
  color: #b45309;
}

.rec-priority.low {
  background: #d1fae5;
  color: #047857;
}

/* Responsive Design */
@media (max-width: 768px) {
  .performance-analytics {
    padding: 16px;
  }

  .header-section {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .overview-grid {
    grid-template-columns: 1fr;
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }

  .distribution-chart {
    flex-direction: column;
    align-items: center;
    gap: 20px;
  }

  .analytics-grid {
    grid-template-columns: 1fr;
  }

  .recommendation-item {
    flex-direction: column;
    gap: 12px;
  }

  .rec-priority {
    align-self: flex-start;
  }
}
</style>
