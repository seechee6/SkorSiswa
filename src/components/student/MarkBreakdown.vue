<template>
  <div class="marks-container">
    <div class="course-selector">
      <label for="course">Select Course:</label>
      <select id="course" v-model="selectedCourseId" @change="fetchCourseMarks">
        <option value="">Select a course</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.code }} - {{ course.name }}
        </option>
      </select>
    </div>

    <div v-if="selectedCourseId && courseDetails" class="course-details">
      <div class="course-header">
        <h2>{{ courseDetails.code }} - {{ courseDetails.name }}</h2>
        <div class="grade-badge" :class="getGradeClass(courseDetails.currentGrade)">
          {{ courseDetails.currentGrade || 'IP' }}
        </div>
      </div>

      <div class="overall-progress">
        <h3>Overall Progress</h3>
        <div class="progress-bar">
          <div 
            :style="{ width: courseDetails.overallProgress + '%' }" 
            class="progress-fill">
            {{ courseDetails.overallProgress }}%
          </div>
        </div>
      </div>

      <div class="assessment-breakdown">
        <h3>Assessment Components</h3>
        <div class="components-grid">
          <div v-for="component in courseDetails.components" 
               :key="component.id" 
               class="component-card">
            <div class="component-header">
              <h4>{{ component.name }}</h4>
              <span class="weight">{{ component.weight }}%</span>
            </div>
            <div class="marks-section">
              <div class="marks-row">
                <span>Your Score:</span>
                <span class="score">{{ component.obtainedMarks || 'N/A' }}/{{ component.maxMarks }}</span>
              </div>
              <div class="marks-row">
                <span>Class Average:</span>
                <span>{{ component.classAverage || 'N/A' }}</span>
              </div>
            </div>
            <div class="progress-bar">
              <div 
                :style="{ width: (component.obtainedMarks / component.maxMarks * 100) + '%' }" 
                class="progress-fill">
              </div>
            </div>
            <button 
              v-if="!component.remarkRequested && !component.isUpcoming" 
              @click="requestRemark(component)"
              class="remark-btn">
              Request Remark
            </button>
            <span v-if="component.remarkRequested" class="remark-status">
              Remark Requested
            </span>
            <span v-if="component.isUpcoming" class="upcoming-badge">
              Upcoming
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Remark Request Modal -->
    <div v-if="showRemarkModal" class="modal">
      <div class="modal-content">
        <h3>Request Remark</h3>
        <p>Component: {{ selectedComponent?.name }}</p>
        <div class="form-group">
          <label for="justification">Justification:</label>
          <textarea 
            id="justification" 
            v-model="remarkJustification" 
            rows="4"
            placeholder="Please provide your justification for the remark request">
          </textarea>
        </div>
        <div class="modal-actions">
          <button @click="submitRemarkRequest" :disabled="!remarkJustification">
            Submit Request
          </button>
          <button @click="closeRemarkModal" class="cancel-btn">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'MarkBreakdown',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      courseDetails: null,
      showRemarkModal: false,
      selectedComponent: null,
      remarkJustification: '',
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
      return gradeClasses[grade] || 'in-progress'
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
    async fetchCourseMarks() {
      if (!this.selectedCourseId) return
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(
          `/users/${user.id}/courses/${this.selectedCourseId}/marks`
        )
        this.courseDetails = response.data
      } catch (error) {
        console.error('Failed to fetch course marks:', error)
      }
    },
    requestRemark(component) {
      this.selectedComponent = component
      this.showRemarkModal = true
    },
    async submitRemarkRequest() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post('/remark-requests', {
          userId: user.id,
          componentId: this.selectedComponent.id,
          justification: this.remarkJustification
        })
        
        // Update the UI to show remark requested
        const component = this.courseDetails.components.find(
          c => c.id === this.selectedComponent.id
        )
        if (component) {
          component.remarkRequested = true
        }
        
        this.closeRemarkModal()
      } catch (error) {
        console.error('Failed to submit remark request:', error)
      }
    },
    closeRemarkModal() {
      this.showRemarkModal = false
      this.selectedComponent = null
      this.remarkJustification = ''
    }
  },
  mounted() {
    this.fetchCourses()
    
    // If course ID is provided in query params, select it
    const courseId = this.$route.query.course
    if (courseId) {
      this.selectedCourseId = courseId
      this.fetchCourseMarks()
    }
  }
}
</script>

<style scoped>
.marks-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
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

.course-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.grade-badge {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 500;
}

.overall-progress {
  margin-bottom: 2rem;
}

.progress-bar {
  height: 8px;
  background: #ecf0f1;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #3498db;
  transition: width 0.3s ease;
}

.components-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.component-card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
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

.marks-section {
  margin: 1rem 0;
}

.marks-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.score {
  font-weight: 500;
}

.remark-btn {
  width: 100%;
  padding: 0.75rem;
  margin-top: 1rem;
  background: transparent;
  border: 1px solid #3498db;
  color: #3498db;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
}

.remark-btn:hover {
  background: #3498db;
  color: white;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
}

textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-top: 0.5rem;
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

.cancel-btn {
  background: #95a5a6;
}

.remark-status {
  display: block;
  text-align: center;
  color: #e67e22;
  margin-top: 1rem;
}

.upcoming-badge {
  display: block;
  text-align: center;
  color: #7f8c8d;
  margin-top: 1rem;
}

.excellent { background: #e3fcef; color: #27ae60; }
.good { background: #e3f2fc; color: #3498db; }
.average { background: #ffeeba; color: #f1c40f; }
.poor { background: #ffe3e3; color: #e74c3c; }
.fail { background: #fee; color: #c0392b; }
.in-progress { background: #f0f3f6; color: #7f8c8d; }
</style>
