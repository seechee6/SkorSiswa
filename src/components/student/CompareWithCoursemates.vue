<template>  <div class="mark-breakdown">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <h2>Compare with Coursemates</h2>
        <p class="subtitle">Compare your performance with class averages and statistics</p>
      </div>
      <div class="header-actions">
        <div class="course-select">
          <select v-model="selectedCourseId" @change="fetchComparison">
            <option value="">Select a Course</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.code }} - {{ course.name }}
            </option>
          </select>
          <button @click="fetchCourses" class="reload-btn" title="Reload courses">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
              <path d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading comparison data...</p>
    </div>

    <div v-else-if="!selectedCourseId" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <h3>No Course Selected</h3>
      <p>Please select a course from the dropdown to view comparison data</p>
    </div>

    <div v-else-if="components.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
      </div>
      <h3>No Comparison Data Available</h3>
      <p>There are no assessment components or comparison data available for this course yet</p>
    </div>

    <div v-else class="mark-content">
      <!-- Summary Cards -->
      <div class="dashboard-cards">
        <div class="card overview-card">
          <div class="card-icon percentile-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ percentile }}%</div>
            <div class="card-label">Your Percentile</div>
          </div>
        </div>
        
        <div class="card overview-card">
          <div class="card-icon score-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ yourScore.toFixed(2) }}%</div>
            <div class="card-label">Your Score</div>
          </div>
        </div>
        
        <div class="card overview-card">
          <div class="card-icon average-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ classAverage.toFixed(2) }}%</div>
            <div class="card-label">Class Average</div>
          </div>
        </div>
        
        <div class="card overview-card">
          <div class="card-icon class-icon">
            <svg fill="currentColor" viewBox="0 0 20 20">
              <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
            </svg>
          </div>
          <div class="card-content">
            <div class="card-value">{{ classSize }}</div>
            <div class="card-label">Class Size</div>
          </div>
        </div>
      </div>
        <!-- Chart Cards -->
      <div class="charts-container">
        <div class="card chart-card">
          <div class="card-header">
            <h3>Score Distribution</h3>
          </div>
          <div class="card-body">
            <div v-if="hasDistributionData" class="chart-wrapper">
              <canvas ref="distributionChart"></canvas>
            </div>
            <div v-else class="empty-chart">
              <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="3" y1="9" x2="21" y2="9"></line>
                  <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
              </div>
              <p>Not enough class data to generate distribution chart</p>
            </div>
          </div>
        </div>
        
        <div class="card chart-card">
          <div class="card-header">
            <h3>Component Comparison</h3>
          </div>
          <div class="card-body">
            <div class="chart-legend">
              <div class="legend-item">
                <span class="legend-color your-score"></span>
                <span>Your Score</span>
              </div>
              <div class="legend-item">
                <span class="legend-color class-avg"></span>
                <span>Class Average</span>
              </div>
            </div>
            <div v-if="components.length > 0" class="chart-wrapper">
              <canvas ref="radarChart"></canvas>
            </div>
            <div v-else class="empty-chart">
              <div class="empty-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"></circle>
                  <path d="M12 16v-4"></path>
                  <path d="M12 8h.01"></path>
                </svg>
              </div>
              <p>No graded components available for comparison</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Component Table -->
      <div class="card table-card">
        <div class="card-header">
          <h3>Detailed Component Comparison</h3>
          <div class="table-actions">
            <button class="action-button" @click="sortBy('name')" title="Sort by component name">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18M3 12h18M3 18h18"></path>
              </svg>
              Sort by Name
            </button>
            <button class="action-button" @click="sortBy('difference')" title="Sort by difference">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
              </svg>
              Sort by Difference
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="mark-table">
              <thead>
                <tr>
                  <th>Component</th>
                  <th>Weight</th>
                  <th>Your Score</th>
                  <th>Class Average</th>
                  <th>Difference</th>
                  <th>Your Position</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="comp in sortedComponents" :key="comp.id" :class="{'highlight-row': comp.difference > 5}">
                  <td class="component-name">{{ comp.name }}</td>
                  <td>{{ comp.weight }}%</td>
                  <td class="your-score-cell">{{ comp.yourScore.toFixed(2) }}%</td>
                  <td>{{ comp.average.toFixed(2) }}%</td>
                  <td>
                    <span :class="['difference', comp.difference > 0 ? 'positive' : comp.difference < 0 ? 'negative' : 'neutral']">
                      {{ comp.difference > 0 ? '+' : '' }}{{ comp.difference.toFixed(2) }}%
                    </span>
                  </td>
                  <td class="position-cell">
                    <span class="position">{{ comp.position }}</span> 
                    <span class="of">of</span> 
                    <span class="total">{{ classSize }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-if="error" class="error-message">
      <div class="empty-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
      </div>
      <h3>Something went wrong</h3>
      <p>{{ error }}</p>
      <button class="retry-button" @click="fetchCourses">
        Try Again
      </button>
    </div>
  </div>
</template>

<script>
import api from '../../api'
import Chart from 'chart.js/auto'

export default {
  name: 'CompareWithCoursemates',  data() {
    return {
      courses: [],
      selectedCourseId: '',
      components: [],
      percentile: 0,
      classAverage: 0,
      yourScore: 0,
      classSize: 0,
      distribution: [0, 0, 0, 0, 0],
      error: '',
      loading: false,
      distributionChart: null,
      radarChart: null,
      sortField: 'name',
      sortDirection: 'asc'
    }
  },  computed: {
    hasDistributionData() {
      // Check if there's any non-zero value in the distribution array
      console.log('Distribution data:', this.distribution);
      return Array.isArray(this.distribution) && this.distribution.some(value => value > 0);
    },
    
    sortedComponents() {
      // Return components sorted according to current sort field and direction
      if (!this.components || this.components.length === 0) {
        return [];
      }
      
      return [...this.components].sort((a, b) => {
        let comparison = 0;
        
        if (this.sortField === 'name') {
          comparison = a.name.localeCompare(b.name);
        } else if (this.sortField === 'difference') {
          comparison = b.difference - a.difference;
        }
        
        return this.sortDirection === 'asc' ? comparison : -comparison;
      });
    }
  },
  methods: {    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/student/courses/${user.id}`)
        this.courses = response.data.courses
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },    async fetchComparison() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        console.log(`Fetching comparison data for student ${user.id} and course ${this.selectedCourseId}`)
        const response = await api.get(`/student/compare/${user.id}/${this.selectedCourseId}`)
        const data = response.data
        console.log('Comparison data received:', data)
        
        if (data.success) {
          this.components = data.components || []
          this.percentile = data.percentile || 0
          this.classAverage = data.classAverage || 0
          this.yourScore = data.yourScore || 0
          this.classSize = data.classSize || 0
          this.distribution = data.distribution || [0, 0, 0, 0, 0]
          
          this.updateCharts()
        } else {
          this.error = data.error || 'Failed to load comparison data'
        }
      } catch (error) {
        this.error = 'Failed to load comparison data'
        console.error('Error fetching comparison:', error)
      } finally {
        this.loading = false
      }
    },    sortBy(field) {
      this.sortField = field;
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
    },
    
    updateCharts() {
      if (this.distributionChart) this.distributionChart.destroy();
      if (this.radarChart) this.radarChart.destroy();
      
      // Ensure the DOM has updated before accessing the canvas
      this.$nextTick(() => {
        this.renderCharts();
      });
    },
    
    renderCharts() {      // Update distribution chart
      const distCtx = this.$refs.distributionChart;
      if (distCtx && this.hasDistributionData) {
        console.log('Rendering distribution chart with data:', this.distribution);
        try {
          this.distributionChart = new Chart(distCtx, {
          type: 'bar',
          data: {
            labels: ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'],
            datasets: [{
              label: 'Number of Students',
              data: this.distribution,
              backgroundColor: ['rgba(246, 71, 71, 0.7)', 'rgba(249, 151, 93, 0.7)', 
                               'rgba(252, 196, 25, 0.7)', 'rgba(88, 189, 134, 0.7)', 
                               'rgba(59, 131, 189, 0.7)'],
              borderColor: ['rgba(246, 71, 71, 1)', 'rgba(249, 151, 93, 1)', 
                           'rgba(252, 196, 25, 1)', 'rgba(88, 189, 134, 1)', 
                           'rgba(59, 131, 189, 1)'],
              borderWidth: 1,
              borderRadius: 4,
              hoverOffset: 4
            }]
          },          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  title: function(tooltipItems) {
                    return tooltipItems[0].label + ' Score Range';
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Number of Students'
                },
                ticks: {
                  precision: 0
                }
              },
              x: {
                title: {
                  display: true,
                  text: 'Score Range'
                }
              }            }
          }
        });
        } catch (error) {
          console.error('Error creating distribution chart:', error);
        }      }

      // Update radar chart
      const radarCtx = this.$refs.radarChart
      if (radarCtx && this.components && this.components.length > 0) {
        console.log('Rendering radar chart with components:', this.components);
        try {
          const componentNames = this.components.map(c => c.name)
          const yourScores = this.components.map(c => c.yourScore)
          const classAverages = this.components.map(c => c.average)
            this.radarChart = new Chart(radarCtx, {
          type: 'radar',
          data: {
            labels: componentNames,
            datasets: [{
              label: 'Your Scores',
              data: yourScores,
              borderColor: '#4C6EF5',
              backgroundColor: 'rgba(76, 110, 245, 0.2)',
              pointBackgroundColor: '#4C6EF5',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: '#4C6EF5',
              borderWidth: 3,
              pointRadius: 6,
              pointHoverRadius: 8
            }, {
              label: 'Class Average',
              data: classAverages,
              borderColor: '#FA5252',
              backgroundColor: 'rgba(250, 82, 82, 0.2)',
              pointBackgroundColor: '#FA5252',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: '#FA5252',
              borderWidth: 3,
              pointRadius: 6,
              pointHoverRadius: 8
            }]
          },          options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
              padding: {
                top: 20,
                bottom: 20,
                left: 20,
                right: 20
              }
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return context.dataset.label + ': ' + context.raw.toFixed(2) + '%';
                  }
                }
              }
            },
            scales: {
              r: {
                min: 0,
                max: 100,
                ticks: {
                  stepSize: 20,
                  font: {
                    size: 12
                  }
                },
                pointLabels: {
                  font: {
                    size: 14,
                    weight: 'bold'
                  },
                  color: '#2d3748'
                },
                grid: {
                  color: 'rgba(0, 0, 0, 0.1)'
                },
                angleLines: {
                  color: 'rgba(0, 0, 0, 0.1)'
                }
              }            }
          }
        });
        } catch (error) {
          console.error('Error creating radar chart:', error);
        }
      }
    }
  },  mounted() {
    this.fetchCourses()
  },
  updated() {
    // Try to render the charts again when the component updates
    if ((this.distribution && this.hasDistributionData) || 
        (this.components && this.components.length > 0)) {
      this.$nextTick(() => {
        this.renderCharts();
      });
    }
  },
  beforeUnmount() {
    if (this.distributionChart) this.distributionChart.destroy()
    if (this.radarChart) this.radarChart.destroy()
  }
}
</script>

<style scoped>
.compare-container {
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
}

.subtitle {
  font-size: 0.95rem;
  color: #718096;
  margin-top: 4px;
}

.course-select {
  display: flex;
  align-items: center;
}

.course-select select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background-color: #f8fafc;
  font-size: 0.9rem;
  min-width: 240px;
  outline: none;
  transition: all 0.2s;
  cursor: pointer;
}

.reload-btn {
  margin-left: 8px;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #cbd5e0;
  background-color: #f8fafc;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.reload-btn:hover {
  background-color: #edf2f7;
  color: #2d3748;
}

.course-select select:hover {
  border-color: #a0aec0;
}

.course-select select:focus {
  border-color: #4c51bf;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
}

.comparison-content {
  animation: fadeIn 0.4s ease-out;
}

.comparison-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.chart-container {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: var(--shadow);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.chart-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 6px 0;
}

.chart-legend {
  display: flex;
  gap: 16px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #718096;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}

.legend-color.your-score {
  background-color: #4C6EF5;
}

.legend-color.class-avg {
  background-color: #FA5252;
}

.legend-color.distribution {
  background-color: #4C6EF5;
}

.chart-description {
  color: var(--text-secondary);
  margin-bottom: 16px;
  font-size: 14px;
}

.chart-wrapper {
  flex: 1;
  position: relative;
  min-height: 250px;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-wrapper canvas {
  max-height: none !important;
  width: 100% !important;
  height: 100% !important;
}

.empty-chart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  background-color: #f8fafc;
  border-radius: 8px;
  border: 1px dashed #cbd5e0;
}

.empty-chart .empty-icon {
  width: 48px;
  height: 48px;
  margin-bottom: 16px;
  color: #a0aec0;
}

.empty-chart p {
  color: #718096;
  font-size: 0.9rem;
  text-align: center;
  margin: 0;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: flex-start;
  gap: 16px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-card::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #4C6EF5, #6387FF);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.stat-card:hover::after {
  opacity: 1;
}

.stat-card.highlight::after {
  opacity: 1;
  background: linear-gradient(90deg, #51CF66, #69DB7C);
}

.stat-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  color: white;
  flex-shrink: 0;
}

.percentile-icon {
  background-color: #4C6EF5;
}

.score-icon {
  background-color: #20C997;
}

.average-icon {
  background-color: #FA5252;
}

.class-icon {
  background-color: #7950F2;
}

.stat-content {
  flex: 1;
}

.stat-card h3 {
  font-size: 16px;
  color: #718096;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.stat-value {
  font-size: 36px;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 8px;
  line-height: 1;
}

.stat-description {
  font-size: 13px;
  color: #a0aec0;
  margin: 0;
}

.table-container {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 32px;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.table-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.table-actions {
  display: flex;
  gap: 12px;
}

.action-button {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: white;
  border: 1px solid #cbd5e0;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 14px;
  color: #718096;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-button:hover {
  background-color: #f8fafc;
  border-color: #a0aec0;
}

.table-scroll {
  overflow-x: auto;
}

.comparison-table {
  width: 100%;
  border-collapse: collapse;
}

.comparison-table th, .comparison-table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #cbd5e0;
}

.comparison-table th {
  color: #718096;
  font-weight: 600;
  background-color: #f8fafc;
  padding: 14px 16px;
  position: sticky;
  top: 0;
}

.comparison-table tr:hover {
  background-color: rgba(76, 110, 245, 0.03);
}

.comparison-table tr.highlight-row {
  background-color: rgba(76, 110, 245, 0.05);
}

.component-name {
  font-weight: 500;
  color: #2d3748;
}

.your-score-cell {
  font-weight: 500;
  color: #4C6EF5;
}

.position-cell {
  display: flex;
  align-items: baseline;
}

.position {
  font-weight: 600;
  color: #2d3748;
  font-size: 16px;
}

.of {
  margin: 0 6px;
  color: #a0aec0;
  font-size: 14px;
}

.total {
  color: #718096;
}

.difference {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 6px;
  font-weight: 500;
}

.difference.positive {
  background: rgba(81, 207, 102, 0.1);
  color: #2B8A3E;
}

.difference.negative {
  background: rgba(250, 82, 82, 0.1);
  color: #C92A2A;
}

.difference.neutral {
  background: rgba(173, 181, 189, 0.1);
  color: #495057;
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

.percentile-icon {
  background-color: #e9d8fd;
  color: #6b46c1;
}

.score-icon {
  background-color: #c4f1f9;
  color: #0987a0;
}

.average-icon {
  background-color: #c6f6d5;
  color: #2f855a;
}

.class-icon {
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

.card-body {
  padding: 16px;
}

/* Table Styles */
.table-responsive {
  overflow-x: auto;
}

.mark-table {
  width: 100%;
  border-collapse: collapse;
}

.mark-table th, .mark-table td {
  padding: 12px 16px;
  text-align: left;
}

.mark-table th {
  background-color: #f7fafc;
  font-size: 0.85rem;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.mark-table tbody tr {
  border-top: 1px solid #edf2f7;
}

.mark-table tbody tr:hover {
  background-color: #f7fafc;
}

.mark-table tbody td {
  font-size: 0.9rem;
  color: #4a5568;
  vertical-align: middle;
}

.mark-table tfoot tr {
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

.mini-progress {
  height: 8px;
  background-color: #edf2f7;
  border-radius: 4px;
  overflow: hidden;
  width: 100%;
  position: relative;
}

.mini-progress-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s ease;
}

.progress-done {
  background-color: #48bb78;
}

.progress-status {
  font-size: 0.75rem;
  color: #a0aec0;
  text-align: center;
  position: absolute;
  width: 100%;
  top: -4px;
}

.progress-percent {
  font-size: 0.75rem;
  color: #4a5568;
  margin-top: 6px;
  text-align: center;
}

/* Chart Container */
.charts-container {
  display: grid;
  grid-template-columns: 400px 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

/* Chart Card */
.chart-card .card-body {
  height: 320px;
  display: flex;
  flex-direction: column;
}

/* Make component comparison chart (radar/pentagon chart) larger */
.chart-card:nth-child(2) .card-body {
  height: 500px;
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
  
  .course-select select {
    width: 100%;
  }
  
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .mark-table th, .mark-table td {
    padding: 8px;
  }
  
  .card-header h3 {
    font-size: 1rem;
  }
}
</style>
