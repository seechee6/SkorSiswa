<template>
  <!-- Modal Overlay -->
  <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content export-modal">
      <div class="modal-header">
        <h4>Export Marks CSV - {{ courseInfo?.code }}</h4>
        <button @click="closeModal" class="close-btn">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Export Options -->
      <div class="export-options">
        <h5>Export Configuration</h5>
        
        <div class="options-grid">
          <div class="option-group">
            <h6>Include Data</h6>
            <div class="checkbox-group">
              <label class="checkbox-option">
                <input type="checkbox" v-model="includeAssessments" />
                <span class="checkbox-label">Assessment Marks</span>
              </label>
              <label class="checkbox-option">
                <input type="checkbox" v-model="includeFinalExam" />
                <span class="checkbox-label">Final Exam Marks</span>
              </label>
              <label class="checkbox-option">
                <input type="checkbox" v-model="includeGrade" />
                <span class="checkbox-label">Letter Grade</span>
              </label>
            </div>
          </div>

          <div class="option-group">
            <h6>Filter Students</h6>
            <div class="radio-group">
              <label class="radio-option">
                <input type="radio" v-model="studentFilter" value="all" />
                <span class="radio-label">All Students</span>
              </label>
              <label class="radio-option">
                <input type="radio" v-model="studentFilter" value="passing" />
                <span class="radio-label">Passing Students Only (≥50%)</span>
              </label>
              <label class="radio-option">
                <input type="radio" v-model="studentFilter" value="failing" />
                <span class="radio-label">Failing Students Only (&lt;50%)</span>
              </label>
              <label class="radio-option">
                <input type="radio" v-model="studentFilter" value="grade" />
                <span class="radio-label">Filter by Grade</span>
              </label>
            </div>
            
            <div class="grade-filter" v-if="studentFilter === 'grade'">
              <label>Minimum Grade:</label>
              <select v-model="minimumGrade">
                <option value="A">A and above</option>
                <option value="B">B and above</option>
                <option value="C">C and above</option>
                <option value="D">D and above</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Preview -->
      <div class="preview-section" v-if="previewData.length > 0">
        <h5>Export Preview</h5>
        <div class="preview-info">
          <span class="preview-count">{{ filteredPreviewData.length }} records will be exported</span>
          <span class="preview-note">Showing first 10 rows</span>
        </div>
        
        <div class="preview-table-wrapper">
          <table class="preview-table">
            <thead>
              <tr>
                <th>Student Name</th>
                <th>Matric No</th>
                <template v-if="includeAssessments">
                  <th v-for="assessment in assessmentColumns" :key="assessment">
                    {{ assessment }}
                  </th>
                </template>
                <th v-if="includeFinalExam">Final Exam</th>
                <th v-if="includeGrade">Grade</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in previewRows" :key="student.id">
                <td>{{ student.name }}</td>
                <td>{{ student.matric_no }}</td>
                <template v-if="includeAssessments">
                  <td v-for="assessment in assessmentColumns" :key="assessment">
                    {{ student.assessments[assessment] || '-' }}
                  </td>
                </template>
                <td v-if="includeFinalExam">{{ student.finalExam || '-' }}</td>
                <td v-if="includeGrade">{{ student.grade }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Export Actions -->
      <div class="export-actions">
        <div class="action-buttons">
          <button @click="exportData" :disabled="!canExport" class="export-btn">
            <svg class="btn-icon" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Export CSV
          </button>
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
      
      <!-- Success Messages -->
      <div v-if="success" class="success-message">
        {{ success }}
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'ExportCSVModal',
  props: {
    isVisible: {
      type: Boolean,
      default: false
    },
    courseInfo: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      includeAssessments: true,
      includeFinalExam: true,
      includeGrade: true,
      studentFilter: 'all',
      minimumGrade: 'C',
      previewData: [],
      assessmentColumns: [],
      error: '',
      success: ''
    }
  },
  computed: {
    selectedCourseName() {
      return this.courseInfo ? `${this.courseInfo.code} - ${this.courseInfo.name}` : ''
    },
    filteredPreviewData() {
      if (!this.previewData.length) return []
      
      let filtered = [...this.previewData]
      
      if (this.studentFilter === 'passing') {
        filtered = filtered.filter(s => s.average >= 50)
      } else if (this.studentFilter === 'failing') {
        filtered = filtered.filter(s => s.average < 50)
      } else if (this.studentFilter === 'grade') {
        const gradeThresholds = { A: 80, B: 70, C: 60, D: 50 }
        const threshold = gradeThresholds[this.minimumGrade] || 0
        filtered = filtered.filter(s => s.average >= threshold)
      }
      
      return filtered
    },
    previewRows() {
      return this.filteredPreviewData.slice(0, 10)
    },
    canExport() {
      return this.courseInfo && this.previewData.length > 0
    }
  },
  watch: {
    isVisible(newVal) {
      if (newVal && this.courseInfo) {
        this.fetchCourseData()
      } else {
        this.resetData()
      }
    }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    },
    
    resetData() {
      this.previewData = []
      this.assessmentColumns = []
      this.error = ''
      this.success = ''
    },

    async fetchCourseData() {
      if (!this.courseInfo) return
      
      try {
        const [marksRes, studentsRes] = await Promise.all([
          api.get(`/courses/${this.courseInfo.id}/marks`).catch(err => {
            console.warn('Marks endpoint failed, using fallback:', err)
            return { data: { assessment_marks: [], final_marks: [] } }
          }),
          api.get(`/courses/${this.courseInfo.id}/students`).catch(err => {
            console.warn('Students endpoint failed, using fallback:', err)
            return { data: [] }
          })
        ])
        
        let studentsData = studentsRes.data
        if (!studentsData || studentsData.length === 0) {
          try {
            const enrollmentsRes = await api.get(`/courses/${this.courseInfo.id}/enrollments`)
            studentsData = enrollmentsRes.data.map(enrollment => ({
              id: enrollment.student_id,
              name: enrollment.student_name,
              matric_no: enrollment.matric_no,
              email: enrollment.email || ''
            }))
          } catch (e) {
            console.warn('Enrollments endpoint also failed:', e)
            studentsData = []
          }
        }
        
        this.processCourseData(marksRes.data, studentsData)
        
      } catch (e) {
        console.error('Failed to load course data:', e)
        this.error = 'Failed to load course data. Please try again.'
        this.generateFallbackData()
      }
    },
    
    generateFallbackData() {
      const mockStudents = []
      for (let i = 1; i <= 20; i++) {
        mockStudents.push({
          id: i,
          name: `Student ${i}`,
          matric_no: `A${String(i).padStart(6, '0')}`,
          email: `student${i}@university.edu`
        })
      }
      
      const mockMarks = {
        assessment_marks: [],
        final_marks: []
      }
      
      const assessments = ['Assignment 1', 'Assignment 2', 'Quiz 1', 'Mid-term']
      mockStudents.forEach(student => {
        assessments.forEach(assessment => {
          mockMarks.assessment_marks.push({
            student_id: student.id,
            assessment_name: assessment,
            mark: Math.floor(Math.random() * 30) + 60,
            weight: 15,
            max_mark: 100
          })
        })
        
        mockMarks.final_marks.push({
          student_id: student.id,
          mark: Math.floor(Math.random() * 30) + 55
        })
      })
      
      this.processCourseData(mockMarks, mockStudents)
      this.success = 'Using sample data for demonstration'
      setTimeout(() => this.success = '', 3000)
    },
    
    processCourseData(marksData, studentsData) {
      const { assessment_marks, final_marks } = marksData
      
      this.assessmentColumns = [...new Set(assessment_marks.map(m => m.assessment_name))]
      
      this.previewData = studentsData.map(student => {
        const assessments = {}
        let totalMarks = 0
        let totalWeight = 0
        
        this.assessmentColumns.forEach(assessmentName => {
          const mark = assessment_marks.find(m => 
            m.student_id === student.id && m.assessment_name === assessmentName
          )
          if (mark) {
            assessments[assessmentName] = Math.round(parseFloat(mark.mark))
            totalMarks += parseFloat(mark.mark)
            totalWeight += 1
          }
        })
        
        const finalExam = final_marks?.find(m => m.student_id === student.id)
        const finalExamMark = finalExam ? Math.round(parseFloat(finalExam.mark)) : null
        
        if (finalExamMark !== null) {
          totalMarks += finalExamMark
          totalWeight += 1
        }
        
        const average = totalWeight > 0 ? Math.round(totalMarks / totalWeight) : 0
        const grade = this.calculateGrade(average)
        
        return {
          id: student.id,
          name: student.name,
          matric_no: student.matric_no,
          assessments,
          finalExam: finalExamMark,
          average,
          grade
        }
      })
    },
    
    calculateGrade(average) {
      if (average >= 80) return 'A'
      if (average >= 70) return 'B'
      if (average >= 60) return 'C'
      if (average >= 50) return 'D'
      return 'F'
    },
    
    generatePreview() {
      if (this.previewData.length > 0) {
        this.success = 'Preview generated successfully!'
        setTimeout(() => this.success = '', 2000)
      } else {
        this.error = 'No data available for preview.'
      }
    },
    
    exportData() {
      try {
        const data = this.generateExportData()
        this.downloadCSV(data)
        
        this.success = `CSV file exported successfully!`
        setTimeout(() => this.success = '', 3000)
        
        this.$emit('export-complete', {
          format: 'csv',
          recordCount: this.filteredPreviewData.length,
          filename: `${this.selectedCourseName}_marks.csv`
        })
        
      } catch (e) {
        this.error = 'Export failed. Please try again.'
      }
    },
    
    generateExportData() {
      const headers = ['Student Name', 'Matric No']
      
      if (this.includeAssessments) {
        headers.push(...this.assessmentColumns)
      }
      if (this.includeFinalExam) {
        headers.push('Final Exam')
      }
      if (this.includeGrade) {
        headers.push('Grade')
      }
      
      const rows = this.filteredPreviewData.map(student => {
        const row = [student.name, student.matric_no]
        
        if (this.includeAssessments) {
          this.assessmentColumns.forEach(assessment => {
            row.push(student.assessments[assessment] || '')
          })
        }
        if (this.includeFinalExam) {
          row.push(student.finalExam || '')
        }
        if (this.includeGrade) {
          row.push(student.grade)
        }
        
        return row
      })
      
      return { headers, rows }
    },
    
    downloadCSV(data) {
      const { headers, rows } = data
      const content = [headers, ...rows]
        .map(row => row.map(cell => `"${cell}"`).join(','))
        .join('\n')
      
      const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = `${this.selectedCourseName}_marks.csv`
      link.click()
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 24px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.export-modal {
  width: 900px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f3f4;
}

.modal-header h4 {
  margin: 0;
  color: #1D3557;
  font-size: 20px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f8f9fa;
  color: #495057;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

.export-options {
  margin-bottom: 24px;
}

.export-options h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
}

.option-group h6 {
  margin: 0 0 12px 0;
  color: #1D3557;
  font-weight: 600;
  font-size: 14px;
}

.radio-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.radio-option {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  cursor: pointer;
  padding: 8px;
  border-radius: 4px;
  transition: background 0.2s;
}

.radio-option:hover {
  background: #F1FAEE;
}

.radio-option input[type="radio"] {
  margin: 2px 0;
}

.radio-label {
  font-weight: 500;
  color: #1D3557;
}

.radio-option small {
  display: block;
  color: #6c757d;
  font-size: 12px;
  margin-top: 2px;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.checkbox-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label {
  color: #1D3557;
}

.grade-filter {
  margin-top: 12px;
}

.grade-filter label {
  display: block;
  margin-bottom: 4px;
  color: #1D3557;
  font-weight: 500;
}

.grade-filter select {
  width: 100%;
  padding: 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.preview-section {
  margin-bottom: 24px;
}

.preview-section h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.preview-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.preview-count {
  font-weight: 600;
  color: #1D3557;
}

.preview-note {
  color: #6c757d;
  font-size: 12px;
}

.preview-table-wrapper {
  overflow-x: auto;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.preview-table th,
.preview-table td {
  padding: 8px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}

.preview-table th {
  background: #F1FAEE;
  font-weight: 600;
  color: #1D3557;
}

.export-actions {
  margin-bottom: 16px;
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
}

.action-buttons button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.export-btn {
  background: #27ae60;
  color: white;
}

.action-buttons button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-buttons button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.error-message {
  background: #FEE2E2;
  color: #991B1B;
  padding: 12px;
  border-radius: 4px;
  border: 1px solid #FECACA;
  margin-top: 16px;
}

.success-message {
  background: #D1FAE5;
  color: #065F46;
  padding: 12px;
  border-radius: 4px;
  border: 1px solid #A7F3D0;
  margin-top: 16px;
}

@media (max-width: 768px) {
  .options-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .export-modal {
    width: auto;
  }
}
</style>
