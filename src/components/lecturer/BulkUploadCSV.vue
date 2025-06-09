<template>
  <div>
    <h3>Bulk Upload via CSV</h3>
    <div class="card add-form">
      <input type="file" @change="handleFile" accept=".csv" />
      <div v-if="success" class="success">Upload complete!</div>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'BulkUploadCSV',
  data() {
    return { success: false, error: '' }
  },
  methods: {
    async handleFile(e) {
      this.success = false
      this.error = ''
      const file = e.target.files[0]
      if (!file) return
      const reader = new FileReader()
      reader.onload = async (evt) => {
        try {
          // Simple CSV parse: assumes header row and columns: enrollment_id,component_id,mark
          const lines = evt.target.result.split('\n').filter(Boolean)
          const [, ...rows] = lines // skip header
          for (const row of rows) {
            const [enrollment_id, component_id, mark] = row.split(',')
            await api.post(`/enrollments/${enrollment_id}/marks`, {
              component_id: Number(component_id),
              mark: Number(mark)
            })
          }
          this.success = true
        } catch (e) {
          this.error = 'Failed to upload CSV.'
        }
      }
      reader.readAsText(file)
    }
  }
}
</script>
<style scoped>
.card {
  background: #fff;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 32px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  max-width: 400px;
}
.add-form {
  max-width: 400px;
}
input[type="file"] {
  margin: 12px 0;
}
.success {
  color: #27ae60;
  margin-top: 8px;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}
</style>
