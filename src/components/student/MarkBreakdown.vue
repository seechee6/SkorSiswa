&lt;template&gt;
  &lt;div class="marks-container"&gt;
    &lt;h2 class="page-title"&gt;Mark Breakdown&lt;/h2&gt;
    
    &lt;div class="course-select"&gt;
      &lt;select v-model="selectedCourseId" @change="fetchMarks"&gt;
        &lt;option value=""&gt;Select a Course&lt;/option&gt;
        &lt;option v-for="course in courses" :key="course.id" :value="course.id"&gt;
          {{ course.code }} - {{ course.name }}
        &lt;/option&gt;
      &lt;/select&gt;
    &lt;/div&gt;

    &lt;div v-if="selectedCourseId" class="marks-content"&gt;
      &lt;div class="marks-summary"&gt;
        &lt;div class="summary-card"&gt;
          &lt;h3&gt;Overall Grade&lt;/h3&gt;
          &lt;div class="summary-value grade"&gt;{{ overallGrade }}&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="summary-card"&gt;
          &lt;h3&gt;Total Score&lt;/h3&gt;
          &lt;div class="summary-value"&gt;{{ totalScore.toFixed(2) }}%&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class="summary-card"&gt;
          &lt;h3&gt;Class Average&lt;/h3&gt;
          &lt;div class="summary-value"&gt;{{ classAverage.toFixed(2) }}%&lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;

      &lt;div class="marks-table"&gt;
        &lt;table&gt;
          &lt;thead&gt;
            &lt;tr&gt;
              &lt;th&gt;Component&lt;/th&gt;
              &lt;th&gt;Weight&lt;/th&gt;
              &lt;th&gt;Your Score&lt;/th&gt;
              &lt;th&gt;Max Score&lt;/th&gt;
              &lt;th&gt;Weighted Score&lt;/th&gt;
              &lt;th&gt;Class Average&lt;/th&gt;
            &lt;/tr&gt;
          &lt;/thead&gt;
          &lt;tbody&gt;
            &lt;tr v-for="component in components" :key="component.id"&gt;
              &lt;td&gt;{{ component.name }}&lt;/td&gt;
              &lt;td&gt;{{ component.weight }}%&lt;/td&gt;
              &lt;td&gt;{{ component.score }}&lt;/td&gt;
              &lt;td&gt;{{ component.max_score }}&lt;/td&gt;
              &lt;td&gt;{{ ((component.score / component.max_score) * component.weight).toFixed(2) }}%&lt;/td&gt;
              &lt;td&gt;{{ component.classAverage.toFixed(2) }}&lt;/td&gt;
            &lt;/tr&gt;
          &lt;/tbody&gt;
          &lt;tfoot&gt;
            &lt;tr&gt;
              &lt;td colspan="4"&gt;&lt;strong&gt;Total&lt;/strong&gt;&lt;/td&gt;
              &lt;td&gt;&lt;strong&gt;{{ totalScore.toFixed(2) }}%&lt;/strong&gt;&lt;/td&gt;
              &lt;td&gt;&lt;strong&gt;{{ classAverage.toFixed(2) }}%&lt;/strong&gt;&lt;/td&gt;
            &lt;/tr&gt;
          &lt;/tfoot&gt;
        &lt;/table&gt;
      &lt;/div&gt;
    &lt;/div&gt;

    &lt;div v-if="error" class="error-message"&gt;
      {{ error }}
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import api from '../../api'

export default {
  name: 'MarkBreakdown',
  data() {
    return {
      courses: [],
      selectedCourseId: '',
      components: [],
      error: '',
      classAverage: 0,
      totalScore: 0,
      overallGrade: ''
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const response = await api.get(`/users/${user.id}/courses`)
        this.courses = response.data
        
        // If course ID is in URL params, select it
        const urlParams = new URLSearchParams(window.location.search)
        const courseId = urlParams.get('course')
        if (courseId) {
          this.selectedCourseId = courseId
          this.fetchMarks()
        }
      } catch (error) {
        this.error = 'Failed to load courses'
        console.error('Error fetching courses:', error)
      }
    },
    async fetchMarks() {
      if (!this.selectedCourseId) return
      
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const enrollment = await api.get(`/courses/${this.selectedCourseId}/enrollment/${user.id}`)
        const enrollmentId = enrollment.data.id

        // Get marks and components
        const [marksRes, componentsRes] = await Promise.all([
          api.get(`/enrollments/${enrollmentId}/marks`),
          api.get(`/courses/${this.selectedCourseId}/components`)
        ])

        // Calculate scores and averages
        this.processMarksData(marksRes.data, componentsRes.data)
      } catch (error) {
        this.error = 'Failed to load marks'
        console.error('Error fetching marks:', error)
      }
    },
    processMarksData(marks, components) {
      this.components = components.map(comp => {
        const mark = marks.find(m => m.component_id === comp.id)
        return {
          ...comp,
          score: mark ? mark.mark : 0,
          classAverage: marks.reduce((sum, m) => 
            m.component_id === comp.id ? sum + m.mark : sum, 0) / 
            marks.filter(m => m.component_id === comp.id).length || 0
        }
      })

      // Calculate total score
      this.totalScore = this.components.reduce((sum, comp) => 
        sum + ((comp.score / comp.max_score) * comp.weight), 0)

      // Calculate class average
      this.classAverage = this.components.reduce((sum, comp) => sum + comp.classAverage, 0) / 
        (this.components.length || 1)

      // Determine grade
      this.overallGrade = this.calculateGrade(this.totalScore)
    },
    calculateGrade(score) {
      if (score >= 80) return 'A'
      if (score >= 70) return 'B'
      if (score >= 60) return 'C'
      if (score >= 50) return 'D'
      return 'F'
    }
  },
  mounted() {
    this.fetchCourses()
  }
}
&lt;/script&gt;

&lt;style scoped&gt;
.marks-container {
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

.marks-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.summary-card h3 {
  font-size: 14px;
  color: #7f8c8d;
  margin: 0 0 12px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-value {
  font-size: 28px;
  font-weight: 500;
  color: #2c3e50;
}

.summary-value.grade {
  font-size: 36px;
  color: #3498db;
}

.marks-table {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ecf0f1;
}

th {
  font-weight: 500;
  color: #7f8c8d;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

td {
  color: #2c3e50;
}

tfoot td {
  border-bottom: none;
  font-weight: 500;
}

.error-message {
  margin-top: 16px;
  padding: 12px;
  background: #fee;
  color: #e74c3c;
  border-radius: 6px;
  border-left: 4px solid #e74c3c;
}
&lt;/style&gt;
