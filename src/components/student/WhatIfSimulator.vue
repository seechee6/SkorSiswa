<template>
  <div class="simulator-container">
    <h2>What-If Grade Simulator</h2>
    
    <div class="course-selector">
      <label for="course">Select Course:</label>
      <select id="course" v-model="selectedCourseId" @change="fetchCourseDetails">
        <option value="">Select a course</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <div v-if="selectedCourseId && courseDetails" class="simulation-content">
      <div class="current-status">
        <div class="status-card">
          <h3>Current Grade</h3>
          <div class="grade" :class="getGradeClass(courseDetails.currentGrade)">
            {{ courseDetails.currentGrade || 'N/A' }}
          </div>
        </div>
        <div class="status-card">
          <h3>Projected Grade</h3>
          <div class="grade" :class="getGradeClass(projectedGrade)">
            {{ projectedGrade || 'N/A' }}
          </div>
        </div>
      </div>

      <div class="components-grid">
        <div v-for="component in courseDetails.components" 
             :key="component.id" 
             class="component-card">
          <div class="component-header">
            <h4>{{ component.name }}</h4>
            <span class="weight">{{ component.weight }}%</span>
          </div>
          
          <div class="marks-input" v-if="!component.isCompleted">
            <label :for="'marks-' + component.id">Expected Marks:</label>
            <input 
              :id="'marks-' + component.id"
              type="number" 
              v-model.number="simulatedMarks[component.id]"
              :min="0"
              :max="component.maxMarks"
              @input="calculateProjectedGrade"
            >
            <span class="max-marks">/ {{ component.maxMarks }}</span>
          </div>
          
          <div class="actual-marks" v-else>
            <span>Actual Marks:</span>
            <span class="marks">{{ component.obtainedMarks }}/{{ component.maxMarks }}</span>
          </div>

          <div class="progress-bar">
            <div 
              :style="{ width: getProgressWidth(component) + '%' }" 
              class="progress-fill"
              :class="{ 'simulated': !component.isCompleted }">
            </div>
          </div>
        </div>
      </div>

      <div class="grade-info">
        <h3>Grade Boundaries</h3>
        <div class="boundaries">
          <div class="boundary">
            <span class="grade">A</span>
            <span class="range">≥ 80%</span>
          </div>
          <div class="boundary">
            <span class="grade">B</span>
            <span class="range">70-79%</span>
          </div>
          <div class="boundary">
            <span class="grade">C</span>
            <span class="range">60-69%</span>
          </div>
          <div class="boundary">
            <span class="grade">D</span>
            <span class="range">50-59%</span>
          </div>
          <div class="boundary">
            <span class="grade">F</span>
            <span class="range">&lt; 50%</span>
          </div>
        </div>
      </div>
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
      courseDetails: null,
      simulatedMarks: {},
      projectedGrade: null
    }
  },
  methods: {
    getGradeClass(grade) {
      const gradeClasses = {
        'A': 'excellent',
        'B': 'good',
        'C': 'average',
        'D': 'poor',
        'F': 'fail'
      }
      return gradeClasses[grade] || 'pending'
    },
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/courses`)
        this.courses = response.data
      } catch (error) {
        console.error('Failed to fetch courses:', error)
      }
    },
    async fetchCourseDetails() {
      if (!this.selectedCourseId) return
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(
          `/users/${user.id}/courses/${this.selectedCourseId}/details`
        )
        this.courseDetails = response.data
        this.initializeSimulatedMarks()
        this.calculateProjectedGrade()
      } catch (error) {
        console.error('Failed to fetch course details:', error)
      }
    },
    initializeSimulatedMarks() {
      this.simulatedMarks = {}
      this.courseDetails.components.forEach(component => {
        if (!component.isCompleted) {
          this.simulatedMarks[component.id] = component.obtainedMarks || 0
        }
      })
    },
    calculateProjectedGrade() {
      if (!this.courseDetails) return

      let totalWeightedScore = 0
      let totalWeight = 0

      this.courseDetails.components.forEach(component => {
        const marks = component.isCompleted 
          ? component.obtainedMarks 
          : this.simulatedMarks[component.id] || 0
        
        const percentage = (marks / component.maxMarks) * 100
        totalWeightedScore += percentage * (component.weight / 100)
        totalWeight += component.weight / 100
      })

      const finalScore = totalWeightedScore / totalWeight
      this.projectedGrade = this.getGradeFromScore(finalScore)
    },
    getGradeFromScore(score) {
      if (score >= 80) return 'A'
      if (score >= 70) return 'B'
      if (score >= 60) return 'C'
      if (score >= 50) return 'D'
      return 'F'
    },
    getProgressWidth(component) {
      if (component.isCompleted) {
        return (component.obtainedMarks / component.maxMarks) * 100
      }
      return (this.simulatedMarks[component.id] / component.maxMarks) * 100
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
  padding: 2rem;
}

h2 {
  margin-bottom: 2rem;
  color: #2c3e50;
}

.course-selector {
  margin-bottom: 2rem;
}

select {
  width: 100%;
  max-width: 400px;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  margin-top: 0.5rem;
}

.current-status {
  display: flex;
  gap: 2rem;
  margin-bottom: 2rem;
}

.status-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  flex: 1;
  text-align: center;
}

.status-card h3 {
  font-size: 0.9rem;
  color: #7f8c8d;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.grade {
  font-size: 2rem;
  font-weight: 600;
  padding: 0.5rem;
  border-radius: 4px;
}

.components-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.component-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.component-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.weight {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.marks-input {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 1rem 0;
}

input[type="number"] {
  width: 80px;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.max-marks {
  color: #7f8c8d;
}

.progress-bar {
  height: 8px;
  background: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
  margin-top: 1rem;
}

.progress-fill {
  height: 100%;
  background: #3498db;
  transition: width 0.3s ease;
}

.progress-fill.simulated {
  background: #95a5a6;
}

.grade-info {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.boundaries {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.boundary {
  text-align: center;
}

.boundary .grade {
  display: block;
  font-size: 1.2rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.boundary .range {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.excellent { background: #e3fcef; color: #27ae60; }
.good { background: #e3f2fc; color: #3498db; }
.average { background: #ffeeba; color: #f1c40f; }
.poor { background: #ffe3e3; color: #e74c3c; }
.fail { background: #fee; color: #c0392b; }
.pending { background: #f0f3f6; color: #7f8c8d; }
</style>