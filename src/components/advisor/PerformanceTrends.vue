<template>
  <div class="performance-trends">
    <div class="header-section">
      <div class="title-section">
        <h3>Performance Trend Graphs</h3>
        <p class="page-description">Visual trend analysis and performance tracking for advisees</p>
      </div>
      <div class="header-actions">
        <select v-model="selectedTimeframe" @change="updateTrends" class="timeframe-select">
          <option value="semester">Semester View</option>
          <option value="academic_year">Academic Year</option>
          <option value="all_time">All Time</option>
        </select>
        <select v-model="selectedStudent" @change="loadStudentTrends" class="student-select">
          <option value="">All Advisees</option>
          <option v-for="student in advisees" :key="student.id" :value="student.id">
            {{ student.name }}
          </option>
        </select>
        <button @click="exportTrends" class="export-btn">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
          </svg>
          Export Charts
        </button>
      </div>
    </div>

    <!-- Trend Overview Cards -->
    <div class="trend-overview">
      <div class="trend-cards">
        <div class="trend-card">
          <div class="card-header">
            <h5>Overall Trend</h5>
            <div class="trend-indicator" :class="overallTrend.direction">
              <svg v-if="overallTrend.direction === 'up'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
              </svg>
              <svg v-else-if="overallTrend.direction === 'down'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
              </svg>
              <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
              </svg>
            </div>
          </div>
          <div class="trend-value">{{ overallTrend.value }}%</div>
          <div class="trend-description">{{ overallTrend.description }}</div>
        </div>

        <div class="trend-card">
          <div class="card-header">
            <h5>Top Performer Trend</h5>
            <div class="performance-badge excellent">Excellent</div>
          </div>
          <div class="trend-value">{{ topPerformerTrend.value }}</div>
          <div class="trend-description">{{ topPerformerTrend.description }}</div>
        </div>

        <div class="trend-card">
          <div class="card-header">
            <h5>At-Risk Trend</h5>
            <div class="performance-badge warning">Warning</div>
          </div>
          <div class="trend-value">{{ atRiskTrend.value }}%</div>
          <div class="trend-description">{{ atRiskTrend.description }}</div>
        </div>

        <div class="trend-card">
          <div class="card-header">
            <h5>Improvement Rate</h5>
            <div class="performance-badge good">Good</div>
          </div>
          <div class="trend-value">{{ improvementTrend.value }}%</div>
          <div class="trend-description">{{ improvementTrend.description }}</div>
        </div>
      </div>
    </div>

    <!-- Main Trend Charts -->
    <div class="charts-section">
      <div class="charts-grid">
        <!-- GPA Trend Line Chart -->
        <div class="chart-container large">
          <div class="chart-header">
            <h5>CGPA Trends Over Time</h5>
            <div class="chart-controls">
              <div class="chart-legend">
                <div class="legend-item">
                  <div class="legend-color" style="background: #3b82f6;"></div>
                  <span>Individual CGPA</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color" style="background: #10b981;"></div>
                  <span>Cohort Average</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color" style="background: #ef4444;"></div>
                  <span>Risk Threshold</span>
                </div>
              </div>
            </div>
          </div>
          <div class="line-chart">
            <div class="chart-y-axis">
              <div v-for="tick in yAxisTicks" :key="tick" class="y-tick">{{ tick }}</div>
            </div>
            <div class="chart-plot-area">
              <div class="grid-lines">
                <div v-for="n in 5" :key="n" class="grid-line"></div>
              </div>
              <div class="risk-threshold-line"></div>
              
              <!-- Data Lines -->
              <svg class="trend-lines" viewBox="0 0 800 300">
                <!-- Individual CGPA Line -->
                <polyline
                  :points="generateLinePoints(cgpaData)"
                  fill="none"
                  stroke="#3b82f6"
                  stroke-width="3"
                  class="trend-line"
                />
                <!-- Cohort Average Line -->
                <polyline
                  :points="generateLinePoints(cohortAverageData)"
                  fill="none"
                  stroke="#10b981"
                  stroke-width="2"
                  stroke-dasharray="5,5"
                  class="trend-line"
                />
                
                <!-- Data Points -->
                <circle
                  v-for="(point, index) in cgpaData"
                  :key="`cgpa-${index}`"
                  :cx="(index / (cgpaData.length - 1)) * 780 + 10"
                  :cy="300 - (point / 4 * 280) - 10"
                  r="4"
                  fill="#3b82f6"
                  class="data-point"
                />
              </svg>
              
              <!-- X-axis labels -->
              <div class="x-axis-labels">
                <div v-for="(label, index) in xAxisLabels" :key="index" class="x-label">
                  {{ label }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Grade Distribution Trend -->
        <div class="chart-container">
          <div class="chart-header">
            <h5>Grade Distribution Trends</h5>
            <div class="chart-controls">
              <select v-model="gradeChartType" class="chart-select">
                <option value="stacked">Stacked Bars</option>
                <option value="grouped">Grouped Bars</option>
              </select>
            </div>
          </div>
          <div class="bar-chart">
            <div class="chart-y-axis">
              <div v-for="tick in [100, 75, 50, 25, 0]" :key="tick" class="y-tick">{{ tick }}%</div>
            </div>
            <div class="bars-container">
              <div v-for="(semester, index) in gradeDistributionData" :key="index" class="bar-group">
                <div class="stacked-bar">
                  <div 
                    v-for="grade in semester.grades" 
                    :key="grade.grade"
                    class="bar-segment"
                    :style="{ 
                      height: grade.percentage + '%',
                      backgroundColor: grade.color
                    }"
                    :title="`${grade.grade}: ${grade.percentage}%`"
                  ></div>
                </div>
                <div class="bar-label">S{{ semester.semester }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Performance Radar -->
        <div class="chart-container">
          <div class="chart-header">
            <h5>Course Performance Radar</h5>
            <div class="chart-controls">
              <select v-model="radarComparison" class="chart-select">
                <option value="current">Current vs Previous</option>
                <option value="target">Current vs Target</option>
                <option value="cohort">Individual vs Cohort</option>
              </select>
            </div>
          </div>
          <div class="radar-chart">
            <div class="radar-container">
              <svg class="radar-svg" viewBox="0 0 300 300">
                <!-- Background Grid -->
                <g class="radar-grid">
                  <circle cx="150" cy="150" r="120" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                  <circle cx="150" cy="150" r="90" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                  <circle cx="150" cy="150" r="60" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                  <circle cx="150" cy="150" r="30" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                  
                  <!-- Axis Lines -->
                  <line x1="150" y1="30" x2="150" y2="270" stroke="#e5e7eb" stroke-width="1"/>
                  <line x1="30" y1="150" x2="270" y2="150" stroke="#e5e7eb" stroke-width="1"/>
                  <line x1="74" y1="74" x2="226" y2="226" stroke="#e5e7eb" stroke-width="1"/>
                  <line x1="226" y1="74" x2="74" y2="226" stroke="#e5e7eb" stroke-width="1"/>
                </g>
                
                <!-- Data Polygon -->
                <polygon
                  :points="radarPolygonPoints"
                  fill="rgba(59, 130, 246, 0.2)"
                  stroke="#3b82f6"
                  stroke-width="2"
                />
                
                <!-- Data Points -->
                <circle
                  v-for="(point, index) in radarPoints"
                  :key="index"
                  :cx="point.x"
                  :cy="point.y"
                  r="4"
                  fill="#3b82f6"
                />
              </svg>
              
              <!-- Labels -->
              <div class="radar-labels">
                <div v-for="(label, index) in radarLabels" :key="index" class="radar-label" :style="getLabelPosition(index)">
                  {{ label }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Semester Comparison -->
        <div class="chart-container">
          <div class="chart-header">
            <h5>Semester-to-Semester Comparison</h5>
            <div class="chart-controls">
              <select v-model="comparisonMetric" class="chart-select">
                <option value="gpa">GPA Comparison</option>
                <option value="credits">Credits Earned</option>
                <option value="courses">Course Load</option>
              </select>
            </div>
          </div>
          <div class="comparison-chart">
            <div class="comparison-bars">
              <div v-for="comparison in semesterComparisons" :key="comparison.semester" class="comparison-row">
                <div class="semester-label">Semester {{ comparison.semester }}</div>
                <div class="comparison-bar-container">
                  <div class="comparison-bar current" :style="{ width: comparison.current + '%' }">
                    <span class="bar-value">{{ comparison.currentValue }}</span>
                  </div>
                  <div class="comparison-bar previous" :style="{ width: comparison.previous + '%' }">
                    <span class="bar-value">{{ comparison.previousValue }}</span>
                  </div>
                </div>
                <div class="change-indicator" :class="comparison.change >= 0 ? 'positive' : 'negative'">
                  {{ comparison.change >= 0 ? '+' : '' }}{{ comparison.change.toFixed(2) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Risk Factor Trends -->
        <div class="chart-container large">
          <div class="chart-header">
            <h5>Risk Factor Analysis</h5>
            <div class="chart-controls">
              <div class="risk-legend">
                <div class="legend-item">
                  <div class="legend-color high-risk"></div>
                  <span>High Risk</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color moderate-risk"></div>
                  <span>Moderate Risk</span>
                </div>
                <div class="legend-item">
                  <div class="legend-color low-risk"></div>
                  <span>Low Risk</span>
                </div>
              </div>
            </div>
          </div>
          <div class="risk-trend-chart">
            <div class="risk-timeline">
              <div v-for="(period, index) in riskTimelineData" :key="index" class="risk-period">
                <div class="risk-bars">
                  <div class="risk-bar high" :style="{ height: period.high + '%' }">
                    <span class="risk-count">{{ period.highCount }}</span>
                  </div>
                  <div class="risk-bar moderate" :style="{ height: period.moderate + '%' }">
                    <span class="risk-count">{{ period.moderateCount }}</span>
                  </div>
                  <div class="risk-bar low" :style="{ height: period.low + '%' }">
                    <span class="risk-count">{{ period.lowCount }}</span>
                  </div>
                </div>
                <div class="period-label">{{ period.period }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Trend Insights -->
    <div class="insights-section">
      <div class="card">
        <div class="card-header">
          <h5>Trend Insights & Predictions</h5>
          <svg class="header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
          </svg>
        </div>
        <div class="insights-grid">
          <div v-for="insight in trendInsights" :key="insight.id" class="insight-card">
            <div class="insight-icon" :class="insight.type">
              <svg v-if="insight.type === 'positive'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
              </svg>
              <svg v-else-if="insight.type === 'warning'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
              <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="insight-content">
              <h6>{{ insight.title }}</h6>
              <p>{{ insight.description }}</p>
              <div class="insight-confidence">
                <span class="confidence-label">Confidence:</span>
                <div class="confidence-bar">
                  <div class="confidence-fill" :style="{ width: insight.confidence + '%' }"></div>
                </div>
                <span class="confidence-value">{{ insight.confidence }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PerformanceTrends',
  data() {
    return {
      selectedTimeframe: 'semester',
      selectedStudent: '',
      gradeChartType: 'stacked',
      radarComparison: 'current',
      comparisonMetric: 'gpa',

      advisees: [
        { id: 'STU001', name: 'Alice Johnson' },
        { id: 'STU002', name: 'Bob Smith' },
        { id: 'STU003', name: 'Carol Davis' },
        { id: 'STU004', name: 'David Wilson' },
        { id: 'STU005', name: 'Emma Brown' }
      ],

      overallTrend: {
        direction: 'up',
        value: 8.5,
        description: 'Overall performance improving this semester'
      },

      topPerformerTrend: {
        value: '3.8',
        description: 'Average CGPA of top 20% performers'
      },

      atRiskTrend: {
        value: 15,
        description: 'Students below 2.5 CGPA threshold'
      },

      improvementTrend: {
        value: 72,
        description: 'Students showing positive trend'
      },

      yAxisTicks: ['4.0', '3.5', '3.0', '2.5', '2.0'],
      xAxisLabels: ['S1', 'S2', 'S3', 'S4', 'S5'],
      
      cgpaData: [2.8, 3.0, 3.1, 3.2, 3.3],
      cohortAverageData: [2.9, 3.1, 3.0, 3.1, 3.2],

      gradeDistributionData: [
        {
          semester: 1,
          grades: [
            { grade: 'A', percentage: 20, color: '#10b981' },
            { grade: 'B', percentage: 35, color: '#3b82f6' },
            { grade: 'C', percentage: 30, color: '#f59e0b' },
            { grade: 'D/F', percentage: 15, color: '#ef4444' }
          ]
        },
        {
          semester: 2,
          grades: [
            { grade: 'A', percentage: 25, color: '#10b981' },
            { grade: 'B', percentage: 40, color: '#3b82f6' },
            { grade: 'C', percentage: 25, color: '#f59e0b' },
            { grade: 'D/F', percentage: 10, color: '#ef4444' }
          ]
        },
        {
          semester: 3,
          grades: [
            { grade: 'A', percentage: 30, color: '#10b981' },
            { grade: 'B', percentage: 35, color: '#3b82f6' },
            { grade: 'C', percentage: 25, color: '#f59e0b' },
            { grade: 'D/F', percentage: 10, color: '#ef4444' }
          ]
        },
        {
          semester: 4,
          grades: [
            { grade: 'A', percentage: 28, color: '#10b981' },
            { grade: 'B', percentage: 38, color: '#3b82f6' },
            { grade: 'C', percentage: 24, color: '#f59e0b' },
            { grade: 'D/F', percentage: 10, color: '#ef4444' }
          ]
        }
      ],

      radarLabels: ['Math', 'Programming', 'Theory', 'Projects', 'Presentation', 'Teamwork'],
      radarData: [80, 85, 75, 90, 70, 85],

      semesterComparisons: [
        { semester: 2, current: 85, previous: 80, currentValue: 3.2, previousValue: 3.0, change: 0.2 },
        { semester: 3, current: 88, previous: 85, currentValue: 3.3, previousValue: 3.2, change: 0.1 },
        { semester: 4, current: 90, previous: 88, currentValue: 3.4, previousValue: 3.3, change: 0.1 },
        { semester: 5, current: 87, previous: 90, currentValue: 3.3, previousValue: 3.4, change: -0.1 }
      ],

      riskTimelineData: [
        { period: 'S1', high: 80, moderate: 60, low: 40, highCount: 4, moderateCount: 6, lowCount: 18 },
        { period: 'S2', high: 60, moderate: 70, low: 50, highCount: 3, moderateCount: 7, lowCount: 18 },
        { period: 'S3', high: 40, moderate: 65, low: 60, highCount: 2, moderateCount: 6, lowCount: 20 },
        { period: 'S4', high: 50, moderate: 55, low: 70, highCount: 2, moderateCount: 5, lowCount: 21 },
        { period: 'S5', high: 30, moderate: 45, low: 80, highCount: 1, moderateCount: 4, lowCount: 23 }
      ],

      trendInsights: [
        {
          id: 1,
          type: 'positive',
          title: 'Consistent Improvement Trend',
          description: 'Most advisees showing steady CGPA improvement over the past 3 semesters. Continue current support strategies.',
          confidence: 87
        },
        {
          id: 2,
          type: 'warning',
          title: 'Math Course Performance Declining',
          description: 'Mathematics-related courses showing declining trend. Recommend additional tutoring support.',
          confidence: 92
        },
        {
          id: 3,
          type: 'neutral',
          title: 'Semester 5 Plateau',
          description: 'Performance appears to plateau in semester 5, which is typical as courses become more challenging.',
          confidence: 78
        },
        {
          id: 4,
          type: 'positive',
          title: 'Risk Reduction Success',
          description: 'Significant reduction in high-risk students through targeted interventions.',
          confidence: 94
        }
      ]
    }
  },

  computed: {
    radarPoints() {
      const center = { x: 150, y: 150 };
      const maxRadius = 120;
      const angleStep = (2 * Math.PI) / this.radarData.length;
      
      return this.radarData.map((value, index) => {
        const angle = index * angleStep - Math.PI / 2;
        const radius = (value / 100) * maxRadius;
        return {
          x: center.x + Math.cos(angle) * radius,
          y: center.y + Math.sin(angle) * radius
        };
      });
    },

    radarPolygonPoints() {
      return this.radarPoints.map(point => `${point.x},${point.y}`).join(' ');
    }
  },

  methods: {
    updateTrends() {
      console.log('Updating trends for timeframe:', this.selectedTimeframe);
    },

    loadStudentTrends() {
      console.log('Loading trends for student:', this.selectedStudent);
    },

    exportTrends() {
      console.log('Exporting trend charts...');
    },

    generateLinePoints(data) {
      const width = 780;
      const height = 280;
      const maxValue = 4.0;
      
      return data.map((value, index) => {
        const x = (index / (data.length - 1)) * width + 10;
        const y = height - (value / maxValue * height) + 10;
        return `${x},${y}`;
      }).join(' ');
    },

    getLabelPosition(index) {
      const center = { x: 150, y: 150 };
      const radius = 140;
      const angleStep = (2 * Math.PI) / this.radarLabels.length;
      const angle = index * angleStep - Math.PI / 2;
      
      const x = center.x + Math.cos(angle) * radius;
      const y = center.y + Math.sin(angle) * radius;
      
      return {
        position: 'absolute',
        left: `${x - 150}px`,
        top: `${y - 150}px`,
        transform: 'translate(-50%, -50%)'
      };
    }
  }
}
</script>

<style scoped>
.performance-trends {
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

.timeframe-select, .student-select, .chart-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  outline: none;
  cursor: pointer;
}

.timeframe-select:focus, .student-select:focus, .chart-select:focus {
  border-color: #3b82f6;
}

.export-btn {
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
  background: #10b981;
  color: white;
}

.export-btn:hover {
  background: #059669;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Trend Overview */
.trend-overview {
  margin-bottom: 32px;
}

.trend-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
}

.trend-card {
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

.trend-indicator {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.trend-indicator.up {
  background: #d1fae5;
  color: #047857;
}

.trend-indicator.down {
  background: #fee2e2;
  color: #dc2626;
}

.trend-indicator.stable {
  background: #f3f4f6;
  color: #6b7280;
}

.trend-indicator svg {
  width: 16px;
  height: 16px;
}

.performance-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.performance-badge.excellent {
  background: #d1fae5;
  color: #047857;
}

.performance-badge.good {
  background: #dbeafe;
  color: #1d4ed8;
}

.performance-badge.warning {
  background: #fef3c7;
  color: #b45309;
}

.trend-value {
  font-size: 32px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.trend-description {
  color: #6b7280;
  font-size: 14px;
}

/* Charts Section */
.charts-section {
  margin-bottom: 32px;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.chart-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.chart-container.large {
  grid-column: 1 / -1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-header h5 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.chart-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.chart-legend, .risk-legend {
  display: flex;
  gap: 16px;
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

.legend-color.high-risk {
  background: #ef4444;
}

.legend-color.moderate-risk {
  background: #f59e0b;
}

.legend-color.low-risk {
  background: #10b981;
}

/* Line Chart */
.line-chart {
  height: 300px;
  display: flex;
}

.chart-y-axis {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  width: 40px;
  padding-right: 10px;
  height: 260px;
}

.y-tick {
  font-size: 12px;
  color: #6b7280;
  text-align: right;
}

.chart-plot-area {
  flex: 1;
  position: relative;
  height: 260px;
}

.grid-lines {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 40px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.grid-line {
  height: 1px;
  background: #f3f4f6;
}

.risk-threshold-line {
  position: absolute;
  top: 37.5%;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  opacity: 0.5;
}

.trend-lines {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 40px;
}

.trend-line {
  transition: stroke-width 0.2s;
}

.trend-line:hover {
  stroke-width: 4;
}

.data-point {
  transition: r 0.2s;
  cursor: pointer;
}

.data-point:hover {
  r: 6;
}

.x-axis-labels {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10px;
}

.x-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

/* Bar Chart */
.bar-chart {
  height: 250px;
  display: flex;
}

.bars-container {
  flex: 1;
  display: flex;
  align-items: end;
  justify-content: space-around;
  padding: 0 20px;
  height: 210px;
}

.bar-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.stacked-bar {
  width: 40px;
  height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: end;
  border-radius: 4px 4px 0 0;
  overflow: hidden;
}

.bar-segment {
  transition: opacity 0.2s;
  cursor: pointer;
}

.bar-segment:hover {
  opacity: 0.8;
}

.bar-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

/* Radar Chart */
.radar-chart {
  height: 350px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.radar-container {
  position: relative;
  width: 300px;
  height: 300px;
}

.radar-svg {
  width: 100%;
  height: 100%;
}

.radar-labels {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.radar-label {
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  white-space: nowrap;
}

/* Comparison Chart */
.comparison-chart {
  height: 300px;
}

.comparison-bars {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.comparison-row {
  display: flex;
  align-items: center;
  gap: 16px;
}

.semester-label {
  width: 80px;
  font-weight: 500;
  color: #374151;
  text-align: right;
}

.comparison-bar-container {
  flex: 1;
  position: relative;
  height: 40px;
}

.comparison-bar {
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  padding: 0 8px;
  margin: 1px 0;
  transition: width 0.3s ease;
}

.comparison-bar.current {
  background: #3b82f6;
  color: white;
}

.comparison-bar.previous {
  background: #93c5fd;
  color: #1e40af;
}

.bar-value {
  font-size: 12px;
  font-weight: 500;
}

.change-indicator {
  width: 60px;
  text-align: center;
  font-weight: 600;
  font-size: 14px;
}

.change-indicator.positive {
  color: #10b981;
}

.change-indicator.negative {
  color: #ef4444;
}

/* Risk Trend Chart */
.risk-trend-chart {
  height: 300px;
}

.risk-timeline {
  display: flex;
  justify-content: space-around;
  align-items: end;
  height: 260px;
  padding: 0 20px;
}

.risk-period {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.risk-bars {
  display: flex;
  gap: 4px;
  align-items: end;
  height: 220px;
}

.risk-bar {
  width: 20px;
  border-radius: 2px 2px 0 0;
  display: flex;
  align-items: end;
  justify-content: center;
  padding: 4px 0;
  transition: height 0.3s ease;
  cursor: pointer;
}

.risk-bar.high {
  background: #ef4444;
}

.risk-bar.moderate {
  background: #f59e0b;
}

.risk-bar.low {
  background: #10b981;
}

.risk-count {
  font-size: 10px;
  color: white;
  font-weight: 600;
}

.period-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
}

/* Insights Section */
.insights-section {
  margin-bottom: 32px;
}

.insights-section .card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.insights-section .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.insights-section .card-header h5 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.header-icon {
  width: 20px;
  height: 20px;
  color: #6b7280;
}

.insights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
}

.insight-card {
  display: flex;
  gap: 16px;
  padding: 20px;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid transparent;
}

.insight-card:has(.insight-icon.positive) {
  border-left-color: #10b981;
}

.insight-card:has(.insight-icon.warning) {
  border-left-color: #f59e0b;
}

.insight-card:has(.insight-icon.neutral) {
  border-left-color: #6b7280;
}

.insight-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.insight-icon svg {
  width: 20px;
  height: 20px;
  color: white;
}

.insight-icon.positive {
  background: #10b981;
}

.insight-icon.warning {
  background: #f59e0b;
}

.insight-icon.neutral {
  background: #6b7280;
}

.insight-content {
  flex: 1;
}

.insight-content h6 {
  color: #1f2937;
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.insight-content p {
  color: #6b7280;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 12px 0;
}

.insight-confidence {
  display: flex;
  align-items: center;
  gap: 8px;
}

.confidence-label {
  font-size: 12px;
  color: #374151;
  font-weight: 500;
}

.confidence-bar {
  flex: 1;
  height: 4px;
  background: #e5e7eb;
  border-radius: 2px;
  overflow: hidden;
}

.confidence-fill {
  height: 100%;
  background: #3b82f6;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.confidence-value {
  font-size: 12px;
  color: #374151;
  font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
  .performance-trends {
    padding: 16px;
  }

  .header-section {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .trend-cards {
    grid-template-columns: 1fr;
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }

  .line-chart, .bar-chart, .radar-chart, .comparison-chart, .risk-trend-chart {
    height: auto;
    min-height: 250px;
  }

  .insights-grid {
    grid-template-columns: 1fr;
  }

  .insight-card {
    flex-direction: column;
    gap: 12px;
  }
}
</style>
