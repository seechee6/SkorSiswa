<template>
  <div>
    <h3>Manage Assessments (70%)</h3>
    <div class="card add-form">
      <h4>Add Assessment Component</h4>
      <form @submit.prevent="addComponent">
        <input v-model="course_id" placeholder="Course ID" required />
        <input v-model="name" placeholder="Component Name" required />
        <input v-model="weight" placeholder="Weight (%)" required />
        <input v-model="max_mark" placeholder="Max Mark" required />
        <button type="submit">Add Component</button>
      </form>
    </div>
    <div class="card">
      <table class="component-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Weight (%)</th>
            <th>Max Mark</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="component in components" :key="component.id">
            <td>{{ component.name }}</td>
            <td>{{ component.weight }}</td>
            <td>{{ component.max_mark }}</td>
            <td>
              <button @click="editComponent(component)">Edit</button>
              <button @click="deleteComponent(component.id)" class="danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
    <div v-if="editing" class="modal">
      <div class="modal-content">
        <h4>Edit Assessment Component</h4>
        <form @submit.prevent="updateComponent">
          <input v-model="editComponentData.name" placeholder="Component Name" required />
          <input v-model="editComponentData.weight" placeholder="Weight (%)" required />
          <input v-model="editComponentData.max_mark" placeholder="Max Mark" required />
          <button type="submit">Save</button>
          <button type="button" @click="editing=false">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>
<script>
import api from '../../api'
export default {
  name: 'ManageAssessments',
  data() {
    return {
      components: [],
      error: '',
      course_id: '',
      name: '',
      weight: '',
      max_mark: '',
      editing: false,
      editComponentData: { id: '', name: '', weight: '', max_mark: '' }
    }
  },
  methods: {
    async fetchComponents() {
      try {
        if (!this.course_id) return
        const res = await api.get(`/courses/${this.course_id}/components`)
        this.components = res.data
      } catch (e) {
        this.error = 'Failed to load assessment components.'
      }
    },
    async addComponent() {
      try {
        await api.post(`/courses/${this.course_id}/components`, {
          name: this.name,
          weight: this.weight,
          max_mark: this.max_mark
        })
        this.name = ''
        this.weight = ''
        this.max_mark = ''
        this.fetchComponents()
      } catch (e) {
        this.error = 'Failed to add component.'
      }
    },
    editComponent(component) {
      this.editing = true
      this.editComponentData = { ...component }
    },
    async updateComponent() {
      try {
        await api.put(`/components/${this.editComponentData.id}`, this.editComponentData)
        this.editing = false
        this.fetchComponents()
      } catch (e) {
        this.error = 'Failed to update component.'
      }
    },
    async deleteComponent(id) {
      try {
        await api.delete(`/components/${id}`)
        this.fetchComponents()
      } catch (e) {
        this.error = 'Failed to delete component.'
      }
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
}
.component-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 16px;
}
.component-table th, .component-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.component-table th {
  background: #f0f3f6;
}
button {
  margin-right: 8px;
  padding: 6px 14px;
  border: none;
  border-radius: 4px;
  background: #3498db;
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
}
button.danger {
  background: #e74c3c;
}
button:hover {
  background: #2980b9;
}
button.danger:hover {
  background: #c0392b;
}
.add-form {
  max-width: 400px;
}
input {
  margin: 6px 0;
  padding: 8px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.error {
  color: #e74c3c;
  margin-top: 8px;
}
.modal {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal-content {
  background: #fff;
  padding: 32px;
  border-radius: 8px;
  min-width: 320px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.12);
}
</style>
