<template>
  <div class="performance-trends">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Performance Trends</h2>
        <p class="subtitle">Track your academic progress across semesters</p>
      </div>
      <div class="header-actions">
        <div class="term-selector">
          <button 
            v-for="semester in semesters" 
            :key="semester" 
            :class="['term-btn', selectedSemester === semester ? 'active' : '']"
            @click="selectSemester(semester)"
          >
            {{ semester }}
          </button>
          <button 
            :class="['term-btn', selectedSemester === 'all' ? 'active' : '']"
            @click="selectSemester('all')"
          >
            All Semesters
          </button>
        </div>
      </div>
    </div>
      <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading performance data...</p>
    </div>

    <div v-else-if="!performanceData || performanceData.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
      </div>
      <h3>No Performance Data Available</h3>
      <p>There are no performance records to display at this time</p>
    </div>

    <div v-else class="trends-content">
      <!-- Overview Cards -->
      <div class="dashboard-cards">
        <div class="card overview-card">
          <div class="card-icon grade-icon" :class="getGradeIconClass(getOverallGrade())">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ currentCGPA }}</div>
            <div class="card-label">Current CGPA</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon score-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ highestGPA }}</div>
            <div class="card-label">Highest GPA</div>
            <div class="card-detail">{{ highestGPASemester }}</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon completed-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ completedCourses }}</div>
            <div class="card-label">Courses Completed</div>
          </div>
        </div>

        <div class="card overview-card">
          <div class="card-icon credits-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ totalCredits }}</div>
            <div class="card-label">Credits Earned</div>
          </div>
        </div>      </div>      <!-- Charts Section -->
      <div class="charts-container">
        <div class="card chart-card">
          <div class="card-header">
            <h3>GPA Trends</h3>
          </div>
          <div class="card-body">
            <canvas ref="gpaChart"></canvas>
          </div>
        </div>
        
        <div class="card chart-card">
          <div class="card-header">
            <h3>Course Performance Comparison</h3>
          </div>
          <div class="card-body">
            <canvas ref="courseChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Performance Table -->
      <div class="card table-card">
        <div class="card-header">
          <h3>Course Performance</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="trends-table">
              <thead>
                <tr>
                  <th>Semester</th>
                  <th>Course Code</th>
                  <th>Course Name</th>
                  <th>Credit Hours</th>
                  <th>Grade</th>
                  <th>Percentage</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(course, index) in filteredCourses" :key="index">
                  <td>{{ course.semester }}</td>
                  <td>{{ course.code }}</td>
                  <td>{{ course.name }}</td>
                  <td>{{ course.creditHours }}</td>
                  <td>
                    <span class="grade-badge" :class="getGradeClass(course.grade)">
                      {{ course.grade }}
                    </span>
                  </td>
                  <td>{{ course.percentage }}%</td>
                  <td>
                    <span :class="['status-badge', course.status.toLowerCase().replace(/\s+/g, '')]">
                      {{ course.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>      <!-- WhatIf Simulator Section -->
      <div class="card simulator-card">
        <div class="card-header">
          <h3>What-If Simulator</h3>
          <p class="card-subtitle">Simulate different scenarios to see projected grades</p>
        </div>
        <div class="card-body">
          <div class="course-select">
            <label>Select Course</label>
            <select v-model="selectedSimulatorCourse" @change="fetchSimulatorData">
              <option value="">Select a Course to Simulate</option>
              <option v-for="course in performanceData" :key="course.code" :value="course.code">
                {{ course.code }} - {{ course.name }}
              </option>
            </select>
          </div>

          <div v-if="simulatorLoading" class="loading-container">
            <div class="spinner"></div>
            <p>Loading course data...</p>
          </div>

          <div v-else-if="selectedSimulatorCourse && simulatorComponents.length > 0" class="simulator-content">
            <!-- Simulator Results -->
            <div class="dashboard-cards">
              <div class="card overview-card result-card">
                <div class="card-icon grade-icon" :class="getGradeIconClass(projectedGrade)">
                  <svg fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                </div>
                <div class="card-content">
                  <div class="card-value">{{ projectedGrade }}</div>
                  <div class="card-label">Projected Grade</div>
                  <div class="card-detail">{{ projectedScore.toFixed(2) }}%</div>
                </div>
              </div>

              <div class="card overview-card summary-card">
                <div class="card-icon score-icon">
                  <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="card-content">
                  <div class="card-value">{{ currentSimulatorScore.toFixed(2) }}%</div>
                  <div class="card-label">Current Score</div>
                </div>
              </div>

              <div class="card overview-card difference-card">
                <div class="card-icon" :class="scoreDifference >= 0 ? 'positive-icon' : 'negative-icon'">
                  <svg fill="currentColor" viewBox="0 0 20 20">
                    <path v-if="scoreDifference >= 0" fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    <path v-else fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div class="card-content">
                  <div class="card-value" :class="scoreDifference >= 0 ? 'positive' : 'negative'">
                    {{ scoreDifference >= 0 ? '+' : '' }}{{ scoreDifference.toFixed(2) }}%
                  </div>
                  <div class="card-label">Difference</div>
                </div>
              </div>
            </div>

            <!-- Assessment Components Table -->
            <div class="card table-card">
              <div class="card-header">
                <h3>Assessment Components</h3>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="simulator-table">
                    <thead>
                      <tr>
                        <th>Component</th>
                        <th>Weight (%)</th>
                        <th>Max Mark</th>
                        <th>Current Mark</th>
                        <th>Adjusted Mark</th>
                        <th>Weighted Score</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(component, index) in simulatorComponents" :key="component.id">
                        <td>{{ component.name }}</td>
                        <td>{{ component.weight }}%</td>
                        <td>{{ component.max_mark }}</td>
                        <td>{{ component.mark || 'Not Set' }}</td>
                        <td>
                          <input 
                            class="mark-input"
                            type="number"
                            :min="0"
                            :max="component.max_mark"
                            v-model="adjustedMarks[index]"
                            @input="updateSimulation"
                          />
                        </td>
                        <td>{{ calculateWeighted(component, index).toFixed(2) }}%</td>
                      </tr>
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="5" class="total-label">Total</td>
                        <td class="total-value">{{ projectedScore.toFixed(2) }}%</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            <!-- Grade Goal Calculator -->
            <div class="card goal-card">
              <div class="card-header">
                <h3>Grade Goal Calculator</h3>
                <p class="card-subtitle">Calculate what you need to achieve your target grade</p>
              </div>
              <div class="card-body">
                <div class="goal-inputs">
                  <div class="goal-field">
                    <label>Target Grade</label>
                    <select v-model="targetGrade" @change="calculateRequired">
                      <option value="A+">A+ (90%+)</option>
                      <option value="A">A (80-89%)</option>
                      <option value="A-">A- (75-79%)</option>
                      <option value="B+">B+ (70-74%)</option>
                      <option value="B">B (65-69%)</option>
                      <option value="B-">B- (60-64%)</option>
                      <option value="C+">C+ (55-59%)</option>
                      <option value="C">C (50-54%)</option>
                      <option value="C-">C- (45-49%)</option>
                      <option value="D+">D+ (40-44%)</option>
                      <option value="D">D (35-39%)</option>
                      <option value="F">F (Below 35%)</option>
                    </select>
                  </div>
                  <div class="goal-field" v-if="remainingComponents.length > 0">
                    <label>Component to Adjust</label>
                    <select v-model="targetComponent" @change="calculateRequired">
                      <option v-for="component in remainingComponents" :key="component.index" :value="component.index">
                        {{ component.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="goal-result" v-if="requiredMark !== null">
                  <p>
                    To achieve a grade of <strong>{{ targetGrade }}</strong>, 
                    you need to score at least <strong>{{ requiredMark.toFixed(1) }}</strong> 
                    in <strong>{{ simulatorComponents[targetComponent]?.name }}</strong>.
                  </p>
                  <button @click="applyRequiredMark" class="apply-btn">Apply to Simulation</button>
                </div>
              </div>
            </div>
          </div>

          <div v-else-if="selectedSimulatorCourse" class="empty-state">
            <div class="empty-icon">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <h3>No Assessment Data</h3>
            <p>No assessment components found for this course</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api'
import Chart from 'chart.js/auto'

export default {
  name: 'PerformanceTrends',  data() {
    return {
      performanceData: [],
      semesters: [],
      selectedSemester: 'all',
      loading: false,
      error: '',
      gpaChart: null,
      courseChart: null,
      // Simulator data
      selectedSimulatorCourse: '',
      simulatorComponents: [],
      adjustedMarks: [],
      simulatorLoading: false,
      currentSimulatorScore: 0,
      projectedScore: 0,
      projectedGrade: '',
      targetGrade: 'B',
      targetComponent: null,
      requiredMark: null
    }
  },
  computed: {    currentCGPA() {
      if (!this.performanceData.length) return '0.00'
      
      const totalPoints = this.performanceData.reduce((sum, course) => {
        return sum + (this.getGradePoint(course.grade) * course.creditHours)
      }, 0)
      
      const totalCredits = this.performanceData.reduce((sum, course) => {
        return sum + course.creditHours
      }, 0)
      
      return totalCredits > 0 ? (totalPoints / totalCredits).toFixed(2) : '0.00'
    },    highestGPA() {
      if (!this.semesters.length) return '0.00'
      
      const gpaByTerm = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return { semester, gpa: 0 }
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return { 
          semester, 
          gpa: totalCredits > 0 ? totalPoints / totalCredits : 0
        }
      })
      
      const highest = gpaByTerm.reduce((max, term) => {
        return term.gpa > max.gpa ? term : max
      }, { gpa: 0 })
      
      return highest.gpa.toFixed(2)
    },    highestGPASemester() {
      if (!this.semesters.length) return 'N/A'
      
      const gpaByTerm = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return { semester, gpa: 0 }
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return { 
          semester, 
          gpa: totalCredits > 0 ? totalPoints / totalCredits : 0
        }
      })
      
      const highest = gpaByTerm.reduce((max, term) => {
        return term.gpa > max.gpa ? term : max
      }, { gpa: 0, semester: 'N/A' })
      
      return highest.semester
    },
    completedCourses() {
      return this.performanceData.filter(course => 
        course.status === 'Completed'
      ).length
    },
    totalCredits() {
      return this.performanceData
        .filter(course => course.status === 'Completed')
        .reduce((sum, course) => sum + course.creditHours, 0)
    },    filteredCourses() {
      if (this.selectedSemester === 'all') {
        return this.performanceData
      } else {
        return this.performanceData.filter(course => 
          course.semester === this.selectedSemester
        )
      }
    },
    // Simulator computed properties
    scoreDifference() {
      return this.projectedScore - this.currentSimulatorScore
    },    remainingComponents() {
      // Only components that haven't been completed yet or can be improved
      return this.simulatorComponents.map((component, index) => ({ ...component, index }))
        .filter(component => {
          return !component.mark || component.mark < component.max_mark
        })
    }
  },
  methods: {    async fetchPerformanceData() {
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        if (!user || !user.id) {
          this.error = 'User not logged in'
          return
        }
        
        console.log('Fetching performance data for user:', user.id)
        const response = await api.get(`/student/performance/${user.id}`)
        console.log('Performance API response:', response.data)
        
        if (response.data.success) {
          this.performanceData = response.data.data || []
          
          // Extract unique semesters
          this.semesters = [...new Set(this.performanceData.map(course => course.semester))]
          console.log('Semesters found:', this.semesters)
            // Only render charts if we have data
          if (this.performanceData.length > 0) {
            // Wait for the DOM to update before rendering charts
            setTimeout(() => {
              this.renderCharts()
            }, 100)
          }
        } else {
          this.error = response.data.error || 'Failed to load performance data'
        }
      } catch (error) {
        this.error = 'Failed to load performance data'
        console.error('Error fetching performance:', error)
        console.error('Error details:', error.response?.data)
      } finally {
        this.loading = false
      }
    },
    selectSemester(semester) {
      this.selectedSemester = semester
    },    renderCharts() {
      // Ensure DOM is ready and charts container exists
      this.$nextTick(() => {
        try {
          if (this.$refs.gpaChart) {
            this.renderGPAChart()
          }
          if (this.$refs.courseChart) {
            this.renderCourseChart()
          }
        } catch (error) {
          console.error('Error rendering charts:', error)
        }
      })
    },renderGPAChart() {
      if (this.gpaChart) {
        this.gpaChart.destroy()
        this.gpaChart = null
      }
      
      const ctx = this.$refs.gpaChart
      if (!ctx || !this.semesters.length || this.semesters.length === 0) return
      
      // Calculate GPA for each term
      const gpaData = this.semesters.map(semester => {
        const coursesInTerm = this.performanceData.filter(c => c.semester === semester)
        if (!coursesInTerm.length) return 0
        
        const totalPoints = coursesInTerm.reduce((sum, course) => {
          return sum + (this.getGradePoint(course.grade) * course.creditHours)
        }, 0)
        
        const totalCredits = coursesInTerm.reduce((sum, course) => {
          return sum + course.creditHours
        }, 0)
        
        return totalCredits > 0 ? totalPoints / totalCredits : 0
      })
      
      // Ensure we have valid data
      if (!gpaData.length || gpaData.every(val => val === 0)) return
      
      try {
        this.gpaChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: this.semesters,
            datasets: [{
              label: 'Semester GPA',
              data: gpaData,
              borderColor: '#4c51bf',
              backgroundColor: 'rgba(76, 81, 191, 0.1)',
              tension: 0.3,
              fill: true,
              pointBackgroundColor: '#4c51bf',
              pointBorderColor: '#4c51bf',
              pointRadius: 5,
              pointHoverRadius: 7
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              intersect: false,
              mode: 'index'
            },
            scales: {
              y: {
                beginAtZero: true,
                max: 4,
                grid: {
                  color: '#e2e8f0'
                },
                title: {
                  display: true,
                  text: 'GPA',
                  color: '#4a5568'
                },
                ticks: {
                  color: '#4a5568'
                }
              },
              x: {
                grid: {
                  color: '#e2e8f0'
                },
                ticks: {
                  color: '#4a5568'
                }
              }
            },
            plugins: {
              title: {
                display: false
              },
              legend: {
                display: false
              },
              tooltip: {
                backgroundColor: '#2d3748',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#4c51bf',
                borderWidth: 1
              }
            }
          }
        })
      } catch (error) {
        console.error('Error creating GPA chart:', error)
      }
    },    renderCourseChart() {
      if (this.courseChart) {
        this.courseChart.destroy()
        this.courseChart = null
      }
      
      const ctx = this.$refs.courseChart
      if (!ctx || !this.performanceData.length) return
      
      // Group courses by category and calculate average grade
      const categories = [...new Set(this.performanceData.map(course => this.getCourseCategory(course.code)))]
      
      if (!categories.length) return
      
      const courseData = categories.map(category => {
        const coursesInCategory = this.performanceData.filter(c => 
          this.getCourseCategory(c.code) === category
        )
        
        if (!coursesInCategory.length) return 0
        
        const totalPoints = coursesInCategory.reduce((sum, course) => {
          return sum + this.getGradePoint(course.grade)
        }, 0)
        
        return (totalPoints / coursesInCategory.length) * 25 // Scale to 0-100
      })
      
      // Ensure we have valid data
      if (!courseData.length || courseData.every(val => val === 0)) return
      
      try {
        this.courseChart = new Chart(ctx, {
          type: 'radar',
          data: {
            labels: categories,
            datasets: [{
              label: 'Performance by Course Category',
              data: courseData,
              borderColor: '#4c51bf',
              backgroundColor: 'rgba(76, 81, 191, 0.2)',
              pointBackgroundColor: '#4c51bf',
              pointBorderColor: '#4c51bf',
              pointRadius: 4,
              pointHoverRadius: 6,
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
              intersect: false
            },
            scales: {
              r: {
                min: 0,
                max: 100,
                angleLines: {
                  color: '#e2e8f0'
                },
                grid: {
                  color: '#e2e8f0'
                },
                pointLabels: {
                  color: '#4a5568',
                  font: {
                    size: 12
                  }
                },
                ticks: {
                  stepSize: 20,
                  color: '#718096',
                  backdropColor: 'transparent'
                }
              }
            },
            plugins: {
              title: {
                display: false
              },
              legend: {
                display: false
              },
              tooltip: {
                backgroundColor: '#2d3748',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#4c51bf',
                borderWidth: 1
              }
            }
          }
        })
      } catch (error) {
        console.error('Error creating course chart:', error)
      }
    },
    getGradePoint(grade) {
      switch(grade) {
        case 'A+': return 4.0
        case 'A': return 4.0
        case 'A-': return 3.7
        case 'B+': return 3.3
        case 'B': return 3.0
        case 'B-': return 2.7
        case 'C+': return 2.3
        case 'C': return 2.0
        case 'C-': return 1.7
        case 'D+': return 1.3
        case 'D': return 1.0
        case 'F': return 0.0
        default: return 0.0
      }
    },
    getCourseCategory(courseCode) {
      // Extract the subject area from course code (e.g., "SECJ" from "SECJ3303")
      const match = courseCode.match(/^([A-Z]+)/)
      return match ? match[1] : 'Other'
    },    getGradeClass(grade) {
      if (grade.startsWith('A')) return 'excellent'
      if (grade.startsWith('B')) return 'good'
      if (grade.startsWith('C')) return 'average'
      if (grade.startsWith('D')) return 'poor'
      return 'fail'
    },
    getGradeIconClass(grade) {
      if (!grade || grade === 'N/A') return 'grade-na-icon';
      
      if (grade.startsWith('A')) return 'grade-a-icon';
      if (grade.startsWith('B')) return 'grade-b-icon';
      if (grade.startsWith('C')) return 'grade-c-icon';
      if (grade.startsWith('D')) return 'grade-d-icon';
      if (grade === 'F') return 'grade-f-icon';
      
      return 'grade-na-icon';
    },
    getOverallGrade() {
      const cgpa = parseFloat(this.currentCGPA);
      if (cgpa >= 3.7) return 'A';
      if (cgpa >= 3.0) return 'B';
      if (cgpa >= 2.0) return 'C';
      if (cgpa >= 1.0) return 'D';
      return 'F';
    },
    // ...existing methods...
    async fetchSimulatorData() {
      if (!this.selectedSimulatorCourse) {
        this.simulatorComponents = []
        return
      }

      this.simulatorLoading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        
        // Find the course object from selectedSimulatorCourse (which is the course code)
        const selectedCourse = this.performanceData.find(course => course.code === this.selectedSimulatorCourse)
        if (!selectedCourse) {
          this.error = 'Course not found'
          return
        }

        const courseId = selectedCourse.id
        
        // Get detailed marks for this course
        const response = await api.get(`/student/marks/${user.id}/${courseId}`)
        
        if (response.data.success) {
          this.simulatorComponents = response.data.components || []
          
          // Initialize adjusted marks with current values
          this.adjustedMarks = this.simulatorComponents.map(component => component.mark || 0)
          
          // Calculate initial scores
          this.calculateSimulatorScores()
            // Set initial target component
          if (this.remainingComponents.length > 0) {
            this.targetComponent = this.remainingComponents[0].index
            this.calculateRequired()
          } else {
            this.targetComponent = null
          }
        } else {
          this.error = response.data.error || 'Failed to load course data'
        }
      } catch (error) {
        this.error = 'Failed to load course data'
        console.error('Error fetching simulator data:', error)
      } finally {
        this.simulatorLoading = false
      }
    },

    calculateSimulatorScores() {
      // Calculate current score (based on entered marks)
      this.currentSimulatorScore = this.simulatorComponents.reduce((total, component) => {
        if (component.mark) {
          return total + ((component.mark / component.max_mark) * component.weight)
        }
        return total
      }, 0)
      
      // Calculate projected score (based on adjusted marks)
      this.projectedScore = this.simulatorComponents.reduce((total, component, index) => {
        const adjustedMark = this.adjustedMarks[index] || 0
        return total + ((adjustedMark / component.max_mark) * component.weight)
      }, 0)
      
      // Determine projected grade
      this.projectedGrade = this.calculateGrade(this.projectedScore)
    },

    calculateWeighted(component, index) {
      const adjustedMark = this.adjustedMarks[index] || 0
      return (adjustedMark / component.max_mark) * component.weight
    },

    calculateGrade(score) {
      if (score >= 90) return 'A+'
      if (score >= 80) return 'A'
      if (score >= 75) return 'A-'
      if (score >= 70) return 'B+'
      if (score >= 65) return 'B'
      if (score >= 60) return 'B-'
      if (score >= 55) return 'C+'
      if (score >= 50) return 'C'
      if (score >= 45) return 'D+'
      if (score >= 40) return 'D'
      return 'F'
    },

    updateSimulation() {
      this.calculateSimulatorScores()
      this.calculateRequired()
    },

    calculateRequired() {
      if (this.targetComponent === null || this.targetComponent === undefined) return
      
      const minScore = this.getMinScoreForGrade(this.targetGrade)
      
      // Calculate current score without the target component
      const currentWithoutTarget = this.simulatorComponents.reduce((total, component, index) => {
        if (index !== this.targetComponent) {
          const adjustedMark = this.adjustedMarks[index] || 0
          return total + ((adjustedMark / component.max_mark) * component.weight)
        }
        return total
      }, 0)
      
      // Calculate what's needed for the target component
      const targetComponent = this.simulatorComponents[this.targetComponent]
      const required = ((minScore - currentWithoutTarget) / targetComponent.weight) * targetComponent.max_mark
      
      // Make sure the required mark is achievable
      if (required <= targetComponent.max_mark && required >= 0) {
        this.requiredMark = required
      } else if (required > targetComponent.max_mark) {
        // Not possible to achieve with this component alone
        this.requiredMark = targetComponent.max_mark
      } else {
        // Already achieved
        this.requiredMark = 0
      }
    },    getMinScoreForGrade(grade) {
      switch (grade) {
        case 'A+': return 90
        case 'A': return 80
        case 'A-': return 75
        case 'B+': return 70
        case 'B': return 65
        case 'B-': return 60
        case 'C+': return 55
        case 'C': return 50
        case 'C-': return 45
        case 'D+': return 40
        case 'D': return 35
        case 'F': return 0
        default: return 35
      }
    },

    applyRequiredMark() {
      if (this.targetComponent !== null && this.requiredMark !== null) {
        // Round up to nearest integer
        this.adjustedMarks[this.targetComponent] = Math.ceil(this.requiredMark)
        this.updateSimulation()
      }
    },

    // Handle window resize
    handleResize() {
      if (this.gpaChart) {
        this.gpaChart.resize()
      }
      if (this.courseChart) {
        this.courseChart.resize()
      }
    },
  },  mounted() {
    this.fetchPerformanceData()
    // Add resize listener
    window.addEventListener('resize', this.handleResize)
  },  beforeUnmount() {
    // Remove resize listener
    window.removeEventListener('resize', this.handleResize)
    
    try {
      if (this.gpaChart) {
        this.gpaChart.destroy()
        this.gpaChart = null
      }
      if (this.courseChart) {
        this.courseChart.destroy()
        this.courseChart = null
      }
    } catch (error) {
      console.error('Error destroying charts:', error)
    }
  }
}
</script>

<style scoped>
.performance-trends {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

/* Page Header Styles */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.header-content h2 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  text-align: left;
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
}

.header-actions {
  display: flex;
  align-items: center;
}

.term-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.term-btn {
  padding: 8px 16px;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  background: #f8fafc;
  color: #4a5568;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.term-btn:hover {
  border-color: #4c51bf;
  color: #4c51bf;
  background: #edf2f7;
}

.term-btn.active {
  background: #4c51bf;
  color: white;
  border-color: #4c51bf;
}

/* Loading & Empty State Styles */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 0;
  color: #718096;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(66, 153, 225, 0.3);
  border-radius: 50%;
  border-top-color: #4c51bf;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 0;
  color: #718096;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e0;
}

.empty-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #a0aec0;
}

.empty-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
  color: #4a5568;
}

.empty-state p {
  font-size: 0.95rem;
  text-align: center;
  max-width: 400px;
  margin: 0;
}

/* Card Styles */
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  margin-bottom: 20px;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.overview-card {
  display: flex;
  align-items: center;
  padding: 16px;
}

.card-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  margin-right: 16px;
}

.card-icon svg {
  width: 28px;
  height: 28px;
}

.grade-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.score-icon {
  background-color: #c4f1f9;
  color: #0987a0;
}

.completed-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.credits-icon {
  background-color: #feebc8;
  color: #c05621;
}

.positive-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.negative-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.grade-a-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.grade-b-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.grade-c-icon {
  background-color: #feebc8;
  color: #c05621;
}

.grade-d-icon {
  background-color: #fed7d7;
  color: #e53e3e;
}

.grade-f-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.grade-na-icon {
  background-color: #edf2f7;
  color: #a0aec0;
}

.average-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.status-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.pass-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.fail-icon {
  background-color: #fed7d7;
  color: #c53030;
}

.card-content {
  flex: 1;
}

.card-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 4px;
}

.card-label {
  font-size: 0.85rem;
  color: #718096;
}

.card-detail {
  font-size: 0.8rem;
  color: #a0aec0;
  margin-top: 2px;
}

.result-card .card-detail {
  font-weight: 600;
  color: #4a5568;
}

.summary-card .card-value {
  font-size: 1.2rem;
}

.difference-card .card-value.positive {
  color: #2f855a;
}

.difference-card .card-value.negative {
  color: #c53030;
}

.card-header {
  padding: 16px;
  border-bottom: 1px solid #edf2f7;
}

.card-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.card-subtitle {
  font-size: 0.85rem;
  color: #718096;
  margin: 4px 0 0 0;
}

.card-body {
  padding: 16px;
}

/* Charts Container */
.charts-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

/* Chart Card */
.chart-card .card-body {
  height: 300px;
  padding: 16px;
}

.chart-card canvas {
  max-height: 250px;
}

/* Table Styles */
.table-responsive {
  overflow-x: auto;
}

.trends-table, .simulator-table {
  width: 100%;
  border-collapse: collapse;
}

.trends-table th, .trends-table td,
.simulator-table th, .simulator-table td {
  padding: 12px 16px;
  text-align: left;
}

.trends-table th, .simulator-table th {
  background-color: #f7fafc;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.trends-table tbody tr, .simulator-table tbody tr {
  border-top: 1px solid #edf2f7;
}

.trends-table tbody tr:hover, .simulator-table tbody tr:hover {
  background-color: #f7fafc;
}

.trends-table tbody td, .simulator-table tbody td {
  font-size: 0.9rem;
  color: #4a5568;
  vertical-align: middle;
}

.trends-table tfoot tr, .simulator-table tfoot tr {
  border-top: 2px solid #e2e8f0;
}

.total-label {
  font-weight: 600;
  text-align: right;
  color: #2d3748;
}

.total-value {
  font-weight: 700;
  color: #2d3748;
}

.grade-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
}

.grade-badge.excellent { 
  background: #e3fcef; 
  color: #27ae60; 
}

.grade-badge.good { 
  background: #e3f2fc; 
  color: #3498db; 
}

.grade-badge.average { 
  background: #fef9e7; 
  color: #f39c12; 
}

.grade-badge.poor { 
  background: #fde3dd; 
  color: #e67e22; 
}

.grade-badge.fail { 
  background: #fdeded; 
  color: #e74c3c; 
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 500;
}

.status-badge.completed {
  background: #e3fcef;
  color: #27ae60;
}

.status-badge.inprogress {
  background: #e3f2fc;
  color: #3498db;
}

.status-badge.pending {
  background: #fef9e7;
  color: #f39c12;
}

.status-badge.withdrawn {
  background: #fdeded;
  color: #e74c3c;
}

/* Simulator Styles */
.simulator-card {
  margin-top: 20px;
}

.simulator-content {
  margin-top: 20px;
}

.simulator-content .dashboard-cards {
  margin-bottom: 24px;
}

.simulator-section {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
}

.simulator-section h3 {
  font-size: 1.5rem;
  color: #2d3748;
  margin: 0 0 24px 0;
  padding-bottom: 12px;
  border-bottom: 2px solid #4c51bf;
}

.course-select {
  margin-bottom: 20px;
}

.course-select label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 6px;
}

.course-select select {
  width: 100%;
  max-width: 400px;
  padding: 12px;
  border: 1px solid #cbd5e0;
  border-radius: 6px;
  font-size: 0.9rem;
  color: #2d3748;
  background: white;
}

.course-select select:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 3px rgba(76, 81, 191, 0.1);
  outline: none;
}

.simulator-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.simulator-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.simulator-card h4 {
  font-size: 1rem;
  color: #2d3748;
  margin: 0 0 16px 0;
  text-align: center;
}

.result {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.grade-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  margin: 12px 0;
}

.grade-circle.excellent {
  background: #e3fcef;
  color: #27ae60;
}

.grade-circle.good {
  background: #e3f2fc;
  color: #3498db;
}

.grade-circle.average {
  background: #fef9e7;
  color: #f39c12;
}

.grade-circle.poor {
  background: #fde3dd;
  color: #e67e22;
}

.grade-circle.fail {
  background: #fdeded;
  color: #e74c3c;
}

.score-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
}

.summary {
  display: flex;
  flex-direction: column;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #edf2f7;
}

.summary-item:last-child {
  border-bottom: none;
}

.summary-item span:first-child {
  color: #718096;
}

.summary-item span:last-child {
  font-weight: 600;
}

.positive {
  color: #27ae60;
}

.negative {
  color: #e74c3c;
}

.mark-input {
  width: 70px;
  padding: 6px;
  border: 1px solid #cbd5e0;
  border-radius: 4px;
  text-align: center;
  font-size: 14px;
}

.mark-input:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 2px rgba(76, 81, 191, 0.1);
  outline: none;
}

.goal-calculator {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  margin-top: 24px;
  border: 1px solid #e2e8f0;
}

.goal-calculator h4 {
  font-size: 1rem;
  color: #2d3748;
  margin: 0 0 16px 0;
  font-weight: 600;
}

.goal-inputs {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.goal-field {
  display: flex;
  flex-direction: column;
}

.goal-field label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #4a5568;
  margin-bottom: 6px;
}

.goal-field select {
  padding: 8px 12px;
  border: 1px solid #cbd5e0;
  border-radius: 4px;
  font-size: 0.9rem;
  color: #2d3748;
  background: white;
}

.goal-field select:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 2px rgba(76, 81, 191, 0.1);
  outline: none;
}

.goal-result {
  background: white;
  border-radius: 6px;
  padding: 16px;
  border-left: 4px solid #4c51bf;
}

.goal-result p {
  margin: 0 0 12px 0;
  line-height: 1.5;
  color: #2d3748;
}

.apply-btn {
  background: #4c51bf;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.3s;
}

.apply-btn:hover {
  background: #434190;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  margin-top: 20px;
}

.error-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  color: #e53e3e;
}

.error-icon svg {
  width: 100%;
  height: 100%;
}

.error-message h3 {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.error-message p {
  font-size: 0.95rem;
  margin: 0;
}

.no-data {
  background: #f8fafc;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  color: #718096;
  border: 1px dashed #cbd5e0;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  color: #718096;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .header-actions {
    margin-top: 16px;
    width: 100%;
  }
  
  .term-selector {
    width: 100%;
  }
  
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .trends-table th, .trends-table td,
  .simulator-table th, .simulator-table td {
    padding: 8px;
  }
  
  .card-header h3 {
    font-size: 1rem;
  }
  
  .goal-inputs {
    grid-template-columns: 1fr;
  }
  
  .simulator-cards {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) and (min-width: 769px) {
  .charts-container {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .chart-card .card-body {
    height: 350px;
  }
}
</style>