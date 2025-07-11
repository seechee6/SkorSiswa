<template>
  <!-- Modal Overlay -->
  <div v-if="isVisible" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content upload-modal">
      <div class="modal-header">
        <h4>Bulk Upload CSV - {{ courseInfo?.code }}</h4>
        <button @click="closeModal" class="close-btn">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Upload Guidelines -->
      <div class="guidelines-section">
        <h5>CSV Format Guidelines</h5>
        <div class="guideline-content">
          <div class="format-info">
            <h6>Required CSV Format:</h6>
            <div class="format-example">
              <code>matric_no,assessment_name,mark</code><br>
              <code>STU001,Quiz 1,85.5</code><br>
              <code>STU002,Quiz 1,92.0</code><br>
              <code>STU001,Assignment 1,78.0</code>
            </div>
          </div>
          
          <div class="format-rules">
            <h6>Important Rules:</h6>
            <ul>
              <li>First row must contain headers: <strong>matric_no,assessment_name,mark</strong></li>
              <li>Assessment names must match existing components exactly</li>
              <li>Marks should be numeric values (decimals allowed)</li>
              <li>Students must be enrolled in the selected course</li>
              <li>Maximum file size: 5MB</li>
            </ul>
          </div>

          <div class="available-assessments" v-if="assessments.length > 0">
            <h6>Available Assessment Components:</h6>
            <div class="assessment-tags">
              <span 
                v-for="assessment in assessments" 
                :key="assessment.id"
                class="assessment-tag"
              >
                {{ assessment.name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- File Upload -->
      <div class="upload-section">
        <h5>Upload CSV File</h5>
        
        <div class="upload-area" :class="{ 'drag-over': isDragOver }">
          <input 
            ref="fileInput"
            type="file" 
            accept=".csv"
            @change="handleFileSelect"
            class="file-input"
          />
          
          <div 
            class="drop-zone"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
            @drop.prevent="handleFileDrop"
            @click="$refs.fileInput.click()"
          >
            <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <div class="upload-text">
              <p><strong>Click to browse</strong> or drag and drop your CSV file here</p>
              <p class="upload-hint">Supported format: .csv (max 5MB)</p>
            </div>
          </div>
        </div>

        <!-- Selected File Info -->
        <div v-if="selectedFile" class="file-info">
          <div class="file-details">
            <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div class="file-meta">
              <div class="file-name">{{ selectedFile.name }}</div>
              <div class="file-size">{{ formatFileSize(selectedFile.size) }}</div>
            </div>
            <button @click="removeFile" class="remove-file-btn">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="upload-actions">
            <button 
              @click="previewFile" 
              class="preview-btn"
              :disabled="isProcessing"
            >
              Preview Data
            </button>
            <button 
              @click="uploadFile" 
              class="upload-btn"
              :disabled="isProcessing || !isValidFile"
            >
              {{ isProcessing ? 'Processing...' : 'Upload Marks' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Preview Data -->
      <div class="preview-section" v-if="previewData.length > 0">
        <h5>Data Preview</h5>
        <div class="preview-info">
          <span class="preview-count">{{ previewData.length }} records found</span>
          <span class="preview-note">Showing first 10 rows</span>
        </div>
        
        <div class="preview-table-wrapper">
          <table class="preview-table">
            <thead>
              <tr>
                <th>Matric No</th>
                <th>Assessment</th>
                <th>Mark</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in previewData.slice(0, 10)" :key="index">
                <td>{{ row.matric_no }}</td>
                <td>{{ row.assessment_name }}</td>
                <td>{{ row.mark }}</td>
                <td>
                  <span 
                    class="status-badge"
                    :class="getPreviewStatusClass(row.status)"
                  >
                    {{ row.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Validation Summary -->
        <div class="validation-summary" v-if="validationSummary">
          <div class="summary-stats">
            <div class="stat-item valid">
              <span class="stat-number">{{ validationSummary.valid }}</span>
              <span class="stat-label">Valid</span>
            </div>
            <div class="stat-item invalid">
              <span class="stat-number">{{ validationSummary.invalid }}</span>
              <span class="stat-label">Invalid</span>
            </div>
          </div>
          
          <div v-if="validationErrors.length > 0" class="validation-errors">
            <h6>Validation Issues:</h6>
            <ul class="error-list">
              <li v-for="error in validationErrors.slice(0, 5)" :key="error.row">
                <strong>Row {{ error.row }}:</strong> {{ error.message }}
              </li>
            </ul>
            <p v-if="validationErrors.length > 5" class="more-errors">
              And {{ validationErrors.length - 5 }} more issues...
            </p>
          </div>
        </div>
      </div>

      <!-- Upload Progress -->
      <div class="progress-section" v-if="uploadProgress.show">
        <h5>Upload Progress</h5>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: uploadProgress.percentage + '%' }"
          ></div>
        </div>
        <div class="progress-info">
          <span>{{ uploadProgress.current }} / {{ uploadProgress.total }} records processed</span>
          <span>{{ uploadProgress.percentage }}%</span>
        </div>
        <p class="progress-status">{{ uploadProgress.status }}</p>
      </div>

      <!-- Upload Results -->
      <div class="results-section" v-if="uploadResults">
        <h5>Upload Results</h5>
        <div class="results-summary">
          <div class="result-stats">
            <div class="stat-item success">
              <span class="stat-number">{{ uploadResults.successful }}</span>
              <span class="stat-label">Successful</span>
            </div>
            <div class="stat-item failed">
              <span class="stat-number">{{ uploadResults.failed }}</span>
              <span class="stat-label">Failed</span>
            </div>
          </div>
          
          <div class="results-actions">
            <button @click="downloadErrorReport" v-if="uploadResults.errors.length > 0" class="secondary-btn">
              Download Error Report
            </button>
            <button @click="resetUpload" class="primary-btn">
              Upload Another File
            </button>
          </div>
        </div>

        <div v-if="uploadResults.errors.length > 0" class="upload-errors">
          <h6>Failed Records (showing first 5):</h6>
          <div class="error-table-wrapper">
            <table class="error-table">
              <thead>
                <tr>
                  <th>Row</th>
                  <th>Matric No</th>
                  <th>Assessment</th>
                  <th>Mark</th>
                  <th>Error</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="error in uploadResults.errors.slice(0, 5)" :key="error.row">
                  <td>{{ error.row }}</td>
                  <td>{{ error.matric_no }}</td>
                  <td>{{ error.assessment_name }}</td>
                  <td>{{ error.mark }}</td>
                  <td class="error-message">{{ error.error }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Error Messages -->
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api'

export default {
  name: 'BulkUploadCSVModal',
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
      assessments: [],
      selectedFile: null,
      previewData: [],
      validationSummary: null,
      validationErrors: [],
      uploadProgress: {
        show: false,
        percentage: 0,
        current: 0,
        total: 0,
        status: ''
      },
      uploadResults: null,
      isDragOver: false,
      isProcessing: false,
      error: ''
    }
  },
  computed: {
    isValidFile() {
      return this.selectedFile && this.selectedFile.type === 'text/csv' && this.selectedFile.size <= 5 * 1024 * 1024
    }
  },
  watch: {
    isVisible(newVal) {
      if (newVal && this.courseInfo) {
        this.fetchAssessments()
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
      this.selectedFile = null
      this.previewData = []
      this.validationSummary = null
      this.validationErrors = []
      this.uploadResults = null
      this.error = ''
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },

    async fetchAssessments() {
      if (!this.courseInfo) {
        this.assessments = []
        return
      }
      
      try {
        const res = await api.get(`/courses/${this.courseInfo.id}/assessments`)
        this.assessments = res.data
      } catch (e) {
        this.error = 'Failed to load assessments.'
      }
    },

    handleFileSelect(event) {
      const file = event.target.files[0]
      if (file) {
        this.selectedFile = file
        this.resetPreview()
      }
    },

    handleFileDrop(event) {
      this.isDragOver = false
      const file = event.dataTransfer.files[0]
      if (file && file.type === 'text/csv') {
        this.selectedFile = file
        this.resetPreview()
      } else {
        this.error = 'Please select a valid CSV file.'
      }
    },

    removeFile() {
      this.selectedFile = null
      this.resetPreview()
      this.$refs.fileInput.value = ''
    },

    resetPreview() {
      this.previewData = []
      this.validationSummary = null
      this.validationErrors = []
      this.uploadResults = null
    },

    async previewFile() {
      if (!this.selectedFile) return
      
      this.isProcessing = true
      this.error = ''
      
      try {
        const csvText = await this.readFileAsText(this.selectedFile)
        const parsedData = this.parseCSV(csvText)
        const validatedData = await this.validateData(parsedData)
        this.previewData = validatedData
        this.generateValidationSummary()
      } catch (e) {
        this.error = e.message || 'Failed to preview file.'
      } finally {
        this.isProcessing = false
      }
    },

    async uploadFile() {
      if (!this.selectedFile || this.previewData.length === 0) return
      
      this.isProcessing = true
      this.uploadProgress.show = true
      this.uploadProgress.percentage = 0
      this.uploadProgress.current = 0
      this.uploadProgress.total = this.previewData.filter(row => row.status === 'Valid').length
      this.uploadProgress.status = 'Starting upload...'
      
      const results = {
        successful: 0,
        failed: 0,
        errors: []
      }
      
      try {
        const validRecords = this.previewData.filter(row => row.status === 'Valid')
        
        for (let i = 0; i < validRecords.length; i++) {
          const record = validRecords[i]
          
          this.uploadProgress.current = i + 1
          this.uploadProgress.percentage = Math.round((i + 1) / validRecords.length * 100)
          this.uploadProgress.status = `Processing ${record.matric_no} - ${record.assessment_name}...`
          
          try {
            // Find student enrollment
            const enrollmentsRes = await api.get(`/courses/${this.courseInfo.id}/enrollments`)
            const enrollment = enrollmentsRes.data.find(e => e.matric_no === record.matric_no)
            
            if (!enrollment) {
              throw new Error('Student not enrolled in course')
            }
            
            // Find assessment
            const assessment = this.assessments.find(a => a.name === record.assessment_name)
            if (!assessment) {
              throw new Error('Assessment not found')
            }
            
            // Submit mark
            await api.post(`/enrollments/${enrollment.id}/assessment-marks`, {
              assessment_id: assessment.id,
              mark: record.mark
            })
            
            results.successful++
            
          } catch (e) {
            results.failed++
            results.errors.push({
              row: record.originalRow,
              matric_no: record.matric_no,
              assessment_name: record.assessment_name,
              mark: record.mark,
              error: e.response?.data?.error || e.message
            })
          }
          
          // Small delay to show progress
          await new Promise(resolve => setTimeout(resolve, 100))
        }
        
        this.uploadProgress.status = 'Upload completed!'
        this.uploadResults = results
        
        // Emit success event to parent
        this.$emit('upload-complete', results)
        
      } catch (e) {
        this.error = 'Upload failed: ' + e.message
      } finally {
        this.isProcessing = false
        setTimeout(() => {
          this.uploadProgress.show = false
        }, 2000)
      }
    },

    readFileAsText(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(reader.result)
        reader.onerror = () => reject(new Error('Failed to read file'))
        reader.readAsText(file)
      })
    },

    parseCSV(csvText) {
      const lines = csvText.trim().split('\n')
      if (lines.length < 2) {
        throw new Error('CSV must contain at least a header and one data row')
      }
      
      const headers = lines[0].split(',').map(h => h.trim())
      const requiredHeaders = ['matric_no', 'assessment_name', 'mark']
      
      if (!requiredHeaders.every(h => headers.includes(h))) {
        throw new Error(`CSV must contain headers: ${requiredHeaders.join(', ')}`)
      }
      
      const data = []
      for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',').map(v => v.trim())
        if (values.length >= 3) {
          data.push({
            matric_no: values[headers.indexOf('matric_no')],
            assessment_name: values[headers.indexOf('assessment_name')],
            mark: values[headers.indexOf('mark')],
            originalRow: i + 1
          })
        }
      }
      
      return data
    },

    async validateData(data) {
      this.validationErrors = []
      
      // Get enrolled students
      const enrollmentsRes = await api.get(`/courses/${this.courseInfo.id}/enrollments`)
      const enrolledMatricNos = enrollmentsRes.data.map(e => e.matric_no)
      const assessmentNames = this.assessments.map(a => a.name)
      
      return data.map(row => {
        const errors = []
        
        // Validate matric number
        if (!enrolledMatricNos.includes(row.matric_no)) {
          errors.push('Student not enrolled in course')
        }
        
        // Validate assessment name
        if (!assessmentNames.includes(row.assessment_name)) {
          errors.push('Assessment not found')
        }
        
        // Validate mark
        const mark = parseFloat(row.mark)
        if (isNaN(mark) || mark < 0 || mark > 100) {
          errors.push('Invalid mark (must be 0-100)')
        }
        
        if (errors.length > 0) {
          this.validationErrors.push({
            row: row.originalRow,
            message: errors.join(', ')
          })
        }
        
        return {
          ...row,
          mark: mark,
          status: errors.length === 0 ? 'Valid' : 'Invalid',
          errors
        }
      })
    },

    generateValidationSummary() {
      const valid = this.previewData.filter(row => row.status === 'Valid').length
      const invalid = this.previewData.filter(row => row.status === 'Invalid').length
      
      this.validationSummary = {
        valid,
        invalid
      }
    },

    getPreviewStatusClass(status) {
      return {
        'status-valid': status === 'Valid',
        'status-invalid': status === 'Invalid'
      }
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },

    downloadErrorReport() {
      if (!this.uploadResults || this.uploadResults.errors.length === 0) return
      
      const csv = ['Row,Matric No,Assessment,Mark,Error']
      this.uploadResults.errors.forEach(error => {
        csv.push(`${error.row},${error.matric_no},${error.assessment_name},${error.mark},"${error.error}"`)
      })
      
      const blob = new Blob([csv.join('\n')], { type: 'text/csv' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = 'upload_errors.csv'
      a.click()
      window.URL.revokeObjectURL(url)
    },

    resetUpload() {
      this.selectedFile = null
      this.resetPreview()
      this.$refs.fileInput.value = ''
    }
  }
}
</script>

<style scoped>
/* Modal Styles */
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

.upload-modal {
  width: 800px;
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

/* Guidelines Section */
.guidelines-section {
  background: linear-gradient(135deg, #FFF3CD 0%, #F1FAEE 100%);
  border-left: 4px solid #FFC107;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
}

.guidelines-section h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.guideline-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.format-example {
  background: #1e1e1e;
  color: #fff;
  padding: 12px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 12px;
  margin-top: 8px;
}

.format-rules ul {
  margin: 8px 0;
  padding-left: 20px;
}

.assessment-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.assessment-tag {
  background: #457B9D;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

/* Upload Section */
.upload-section {
  margin-bottom: 24px;
}

.upload-section h5 {
  margin: 0 0 16px 0;
  color: #1D3557;
  font-size: 16px;
  font-weight: 600;
}

.upload-area {
  position: relative;
  margin-bottom: 20px;
}

.file-input {
  display: none;
}

.drop-zone {
  border: 2px dashed #ccc;
  border-radius: 8px;
  padding: 40px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.drop-zone:hover,
.upload-area.drag-over .drop-zone {
  border-color: #457B9D;
  background: rgba(69, 123, 157, 0.05);
}

.upload-icon {
  width: 48px;
  height: 48px;
  color: #6c757d;
  margin: 0 auto 16px;
}

.upload-text p {
  margin: 8px 0;
}

.upload-hint {
  color: #6c757d;
  font-size: 14px;
}

.file-info {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 16px;
}

.file-details {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.file-icon {
  width: 24px;
  height: 24px;
  color: #457B9D;
}

.file-meta {
  flex: 1;
}

.file-name {
  font-weight: 600;
  color: #1D3557;
}

.file-size {
  color: #6c757d;
  font-size: 12px;
}

.remove-file-btn {
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.remove-file-btn svg {
  width: 12px;
  height: 12px;
}

.upload-actions {
  display: flex;
  gap: 12px;
}

.preview-btn, .upload-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
}

.preview-btn {
  background: #6c757d;
  color: white;
}

.upload-btn {
  background: #27ae60;
  color: white;
}

.upload-btn:disabled, .preview-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

/* Other sections */
.preview-section, .progress-section, .results-section {
  margin-bottom: 24px;
}

.preview-section h5, .progress-section h5, .results-section h5 {
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
  margin-bottom: 20px;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
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

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-valid {
  background: #D1FAE5;
  color: #065F46;
}

.status-invalid {
  background: #FEE2E2;
  color: #991B1B;
}

.validation-summary {
  background: #F8F9FA;
  border-radius: 8px;
  padding: 16px;
}

.summary-stats {
  display: flex;
  gap: 24px;
  margin-bottom: 16px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-item.valid .stat-number { color: #065F46; }
.stat-item.invalid .stat-number { color: #991B1B; }

.stat-label {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
}

.error-list {
  margin: 8px 0;
  padding-left: 20px;
}

.progress-bar {
  height: 8px;
  background: #E5E5E5;
  border-radius: 4px;
  overflow: hidden;
  margin: 16px 0;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #457B9D 0%, #27ae60 100%);
  transition: width 0.3s ease;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: #6c757d;
}

.progress-status {
  color: #1D3557;
  font-weight: 500;
  margin-top: 8px;
}

.result-stats {
  display: flex;
  gap: 24px;
  margin-bottom: 16px;
}

.stat-item.success .stat-number { color: #065F46; }
.stat-item.failed .stat-number { color: #991B1B; }

.results-actions {
  display: flex;
  gap: 12px;
}

.primary-btn, .secondary-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-weight: 500;
  cursor: pointer;
}

.primary-btn {
  background: #457B9D;
  color: white;
}

.secondary-btn {
  background: #6c757d;
  color: white;
}

.error-table-wrapper {
  overflow-x: auto;
  margin: 16px 0;
}

.error-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.error-table th,
.error-table td {
  padding: 6px 8px;
  border-bottom: 1px solid #eee;
  text-align: left;
}

.error-table th {
  background: #F8F9FA;
}

.error-message {
  color: #991B1B;
  max-width: 200px;
  word-wrap: break-word;
}

.error-message {
  background: #FEE2E2;
  color: #991B1B;
  padding: 12px;
  border-radius: 4px;
  margin-top: 16px;
  border: 1px solid #FECACA;
}

@media (max-width: 768px) {
  .guideline-content {
    grid-template-columns: 1fr;
  }
  
  .summary-stats {
    flex-direction: column;
    gap: 12px;
  }
  
  .upload-actions {
    flex-direction: column;
  }
  
  .upload-modal {
    width: auto;
  }
}
</style>
