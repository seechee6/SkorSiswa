<template>
  <div class="simulator-container">
    <h2 class="page-title">What-If Simulator</h2>
    
    <div class="course-select">
      <select v-model="selectedCourseId" @change="fetchCourseData">
        <option value="">Select a Course</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>
    
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading course data...</p>
    </div>

    <div v-else-if="selectedCourseId && components.length > 0" class="simulator-content">
      <div class="simulator-cards">
        <div class="simulator-card result">
          <h3>Projected Grade</h3>
          <div class="grade-circle" :class="getGradeClass(projectedGrade)">
            {{ projectedGrade }}
          </div>
          <div class="score-value">{{ projectedScore.toFixed(2) }}%</div>
        </div>
        <div class="simulator-card summary">
          <h3>Score Summary</h3>
          <div class="summary-item">
            <span>Current Score:</span>
            <span>{{ currentScore.toFixed(2) }}%</span>
          </div>
          <div class="summary-item">
            <span>After Adjustments:</span>
            <span>{{ projectedScore.toFixed(2) }}%</span>
          </div>
          <div class="summary-item">
            <span>Difference:</span>
            <span :class="scoreDifference >= 0 ? 'positive' : 'negative'">
              {{ scoreDifference >= 0 ? '+' : '' }}{{ scoreDifference.toFixed(2) }}%
            </span>
          </div>
          <div class="summary-item grade-change" v-if="currentGrade !== projectedGrade">
            <span>Grade Change:</span>
            <span>{{ currentGrade }} → {{ projectedGrade }}</span>
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="simulator-table">
          <thead>
            <tr>
              <th>Component</th>
              <th>Weight</th>
              <th>Max Mark</th>
              <th>Current Mark</th>
              <th>Adjusted Mark</th>
              <th>Weighted Score</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(component, index) in components" :key="component.id">
              <td>{{ component.name }}</td>
              <td>{{ component.weight }}%</td>
              <td>{{ component.max_mark }}</td>
              <td>{{ component.current_mark || 'Not Set' }}</td>
              <td>
                <input 
                  type="number"
                  v-model.number="adjustedMarks[index]"
                  :min="0"
                  :max="component.max_mark"
                  @input="updateSimulation"
                  class="mark-input"
                />
              </td>
              <td>{{ calculateWeighted(component, index).toFixed(2) }}%</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5"><strong>Total</strong></td>
              <td><strong>{{ projectedScore.toFixed(2) }}%</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="goal-calculator">
        <h3>Grade Goal Calculator</h3>
        <div class="goal-inputs">
          <div class="goal-field">
            <label>Target Grade</label>
            <select v-model="targetGrade" @change="calculateRequired">
              <option value="A">A (80%+)</option>
              <option value="B">B (70-79%)</option>
              <option value="C">C (60-69%)</option>
              <option value="D">D (50-59%)</option>
            </select>
          </div>
          <div class="goal-field" v-if="targetComponent !== null">
            <label>Component to Adjust</label>
            <select v-model="targetComponent" @change="calculateRequired">
              <option v-for="(component, index) in remainingComponents" :key="index" :value="index">
                {{ component.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="goal-result" v-if="requiredMark !== null">
          <p>
            To achieve a grade of <strong>{{ targetGrade }}</strong>, 
            you need to score at least <strong>{{ requiredMark.toFixed(1) }}</strong> 
            in <strong>{{ components[targetComponent].name }}</strong>.
          </p>
          <button @click="applyRequiredMark" class="apply-btn">Apply to Simulation</button>
        </div>
      </div>
    </div>

    <div v-else-if="selectedCourseId" class="no-data">
      <p>No assessment components found for this course.</p>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'WhatIfSimulator',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      components: [],
      adjustedMarks: [],
      loading: false,
      error: '',
      currentScore: 0,
      projectedScore: 0,
      currentGrade: '',
      projectedGrade: '',
      targetGrade: 'A',
      targetComponent: null,
      requiredMark: null
    }
  },
  computed: {
    scoreDifference() {
      return this.projectedScore - this.currentScore
    },    remainingComponents() {
      // Only components that haven't been completed yet
      return this.components.filter(component => {
        return !component.current_mark || component.current_mark < component.max_mark
      })
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/courses`)
        this.courses = response.data
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },
    async fetchCourseData() {
      if (!this.selectedCourseId) return
      
      this.loading = true
      this.error = ''
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const enrollment = await api.get(`/courses/${this.selectedCourseId}/enrollment/${user.id}`)
        const enrollmentId = enrollment.data.id

        // Get marks and components
        const [marksRes, componentsRes] = await Promise.all([
          api.get(`/enrollments/${enrollmentId}/marks`),
          api.get(`/courses/${this.selectedCourseId}/components`)
        ])

        // Combine components with marks
        this.components = componentsRes.data.map(component => {
          const markEntry = marksRes.data.find(m => m.component_id === component.id)
          return {
            ...component,
            current_mark: markEntry ? markEntry.mark : null
          }
        })

        // Initialize adjusted marks with current values
        this.adjustedMarks = this.components.map(component => component.current_mark || 0)
        
        // Calculate initial scores
        this.calculateScores()
        
        // Set initial target component
        if (this.remainingComponents.length > 0) {
          this.targetComponent = this.components.indexOf(this.remainingComponents[0])
          this.calculateRequired()
        } else {
          this.targetComponent = null
        }
      } catch (error) {
        this.error = 'Failed to load course data'
        console.error('Error fetching course data:', error)
      } finally {
        this.loading = false
      }
    },
    calculateScores() {
      // Calculate current score (based on entered marks)
      this.currentScore = this.components.reduce((total, component) => {
        if (component.current_mark) {
          return total + ((component.current_mark / component.max_mark) * component.weight)
        }
        return total
      }, 0)
      
      // Calculate projected score (based on adjusted marks)
      this.projectedScore = this.components.reduce((total, component, index) => {
        const adjustedMark = this.adjustedMarks[index] || 0
        return total + ((adjustedMark / component.max_mark) * component.weight)
      }, 0)
      
      // Determine grades
      this.currentGrade = this.calculateGrade(this.currentScore)
      this.projectedGrade = this.calculateGrade(this.projectedScore)
    },
    calculateWeighted(component, index) {
      const adjustedMark = this.adjustedMarks[index] || 0
      return (adjustedMark / component.max_mark) * component.weight
    },
    calculateGrade(score) {
      if (score >= 80) return 'A'
      if (score >= 70) return 'B'
      if (score >= 60) return 'C'
      if (score >= 50) return 'D'
      return 'F'
    },
    getGradeClass(grade) {
      const gradeClasses = {
        'A': 'excellent',
        'B': 'good',
        'C': 'average',
        'D': 'poor',
        'F': 'fail'
      }
      return gradeClasses[grade] || 'in-progress'
    },
    updateSimulation() {
      this.calculateScores()
      this.calculateRequired()
    },
    calculateRequired() {
      if (this.targetComponent === null || this.targetComponent === undefined) return
      
      const minScore = this.getMinScoreForGrade(this.targetGrade)
      
      // Calculate current score without the target component
      const currentWithoutTarget = this.components.reduce((total, component, index) => {
        if (index !== this.targetComponent) {
          const adjustedMark = this.adjustedMarks[index] || 0
          return total + ((adjustedMark / component.max_mark) * component.weight)
        }
        return total
      }, 0)
      
      // Calculate what's needed for the target component
      const targetComponent = this.components[this.targetComponent]
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
    },
    getMinScoreForGrade(grade) {
      switch (grade) {
        case 'A': return 80
        case 'B': return 70
        case 'C': return 60
        case 'D': return 50
        default: return 40
      }
    },
    applyRequiredMark() {
      if (this.targetComponent !== null && this.requiredMark !== null) {
        // Round up to nearest integer
        this.adjustedMarks[this.targetComponent] = Math.ceil(this.requiredMark)
        this.updateSimulation()
      }
    }
  },
  mounted() {
    this.fetchCourses()
  }
}
</script>

<style scoped>
.simulator-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 32px;
  font-weight: 300;
  margin-bottom: 32px;
  color: #2c3e50;
}

.course-select {
  margin-bottom: 32px;
}

.course-select select {
  width: 100%;
  max-width: 400px;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  color: #2c3e50;
  background: white;
}

.simulator-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.simulator-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.simulator-card h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0 0 16px 0;
  text-align: center;
}

.result {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.grade-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: 700;
  margin: 16px 0;
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
  font-size: 24px;
  font-weight: 600;
  color: #2c3e50;
}

.summary {
  display: flex;
  flex-direction: column;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.summary-item:last-child {
  border-bottom: none;
}

.summary-item span:first-child {
  color: #7f8c8d;
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

.grade-change {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px dashed #eee;
}

.table-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
  overflow-x: auto;
}

.simulator-table {
  width: 100%;
  border-collapse: collapse;
}

.simulator-table th, .simulator-table td {
  padding: 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.simulator-table th {
  color: #7f8c8d;
  font-weight: 500;
}

.simulator-table tfoot {
  font-weight: 600;
}

.mark-input {
  width: 70px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  text-align: center;
}

.goal-calculator {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  margin-bottom: 32px;
}

.goal-calculator h3 {
  font-size: 18px;
  color: #2c3e50;
  margin: 0 0 16px 0;
}

.goal-inputs {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.goal-field {
  display: flex;
  flex-direction: column;
}

.goal-field label {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 8px;
}

.goal-field select {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  color: #2c3e50;
}

.goal-result {
  background: #f8f9fa;
  border-radius: 6px;
  padding: 16px;
  margin-top: 16px;
}

.goal-result p {
  margin: 0 0 16px 0;
  line-height: 1.5;
}

.apply-btn {
  background: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 10px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.3s;
}

.apply-btn:hover {
  background: #2980b9;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top: 4px solid #3498db;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-data, .error-message {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  text-align: center;
  color: #7f8c8d;
}

.error-message {
  background: #fdeded;
  color: #e74c3c;
}
</style>