<template>
  <div>
    <h3>Manage Courses</h3>
    <div class="card">
      <table class="course-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courses" :key="course.id">
            <td>{{ course.code }}</td>
            <td>{{ course.name }}</td>
            <td>{{ course.semester }}</td>
            <td>{{ course.year }}</td>
            <td>
              <button @click="editCourse(course)">Edit</button>
              <button @click="deleteCourse(course.id)" class="danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="error" class="error">{{ error }}</div>
    </div>
    <div class="card add-form">
      <h4>Add New Course</h4>
      <form @submit.prevent="createCourse">
        <input v-model="newCourse.code" placeholder="Code" required />
        <input v-model="newCourse.name" placeholder="Name" required />
        <input v-model="newCourse.semester" placeholder="Semester" required />
        <input v-model="newCourse.year" placeholder="Year" required />
        <button type="submit">Add Course</button>
      </form>
    </div>
    <div v-if="editing" class="modal">
      <div class="modal-content">
        <h4>Edit Course</h4>
        <form @submit.prevent="updateCourse">
          <input v-model="editCourseData.code" placeholder="Code" required />
          <input v-model="editCourseData.name" placeholder="Name" required />
          <input v-model="editCourseData.semester" placeholder="Semester" required />
          <input v-model="editCourseData.year" placeholder="Year" required />
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
  name: 'ManageCourses',
  data() {
    return {
      courses: [],
      error: '',
      newCourse: { code: '', name: '', semester: '', year: '' },
      editing: false,
      editCourseData: { id: '', code: '', name: '', semester: '', year: '' }
    }
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        const res = await api.get('/courses')
        this.courses = res.data.filter(c => c.lecturer_id === user.id)
      } catch (e) {
        this.error = 'Failed to load courses.'
      }
    },
    async createCourse() {
      try {
        const user = JSON.parse(localStorage.getItem('user'))
        await api.post('/courses', {
          ...this.newCourse,
          lecturer_id: user.id
        })
        this.newCourse = { code: '', name: '', semester: '', year: '' }
        this.fetchCourses()
      } catch (e) {
        this.error = 'Failed to create course.'
      }
    },
    editCourse(course) {
      this.editing = true
      this.editCourseData = { ...course }
    },
    async updateCourse() {
      try {
        await api.put(`/courses/${this.editCourseData.id}`, this.editCourseData)
        this.editing = false
        this.fetchCourses()
      } catch (e) {
        this.error = 'Failed to update course.'
      }
    },
    async deleteCourse(id) {
      try {
        await api.delete(`/courses/${id}`)
        this.fetchCourses()
      } catch (e) {
        this.error = 'Failed to delete course.'
      }
    }
  },
  mounted() {
    this.fetchCourses()
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
.course-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 16px;
}
.course-table th, .course-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  text-align: left;
}
.course-table th {
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
