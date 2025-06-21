<template>
  <div class="manage-courses">
    <div class="header">
      <h2>Manage Courses</h2>
      <button @click="showAddCourseModal = true" class="btn-primary">Add Course</button>
    </div>    <div class="filters">
      <input v-model="search" placeholder="Search courses..." class="search-input">
      <select v-model="semesterFilter" class="semester-filter">
        <option value="">All Semesters</option>
        <option v-for="semester in semesters" :key="semester" :value="semester">
          {{ semester }}
        </option>
      </select>
      <select v-model="yearFilter" class="year-filter">
        <option value="">All Years</option>
        <option v-for="year in availableYears" :key="year" :value="year">
          {{ year }}
        </option>
      </select>
    </div>

    <div class="courses-table">
      <table>
        <thead>
          <tr>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Semester</th>
            <th>Year</th>
            <th>Lecturer</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in filteredCourses" :key="course.id">
            <td>{{ course.code }}</td>
            <td>
              <div class="tooltip-wrapper" :data-tooltip="course.name">
                {{ course.name }}
              </div>
            </td>
            <td>{{ course.semester }}</td>
            <td>{{ course.year }}</td>
            <td>
              <div class="tooltip-wrapper" :data-tooltip="course.lecturer_name || 'Not Assigned'">
                {{ course.lecturer_name || 'Not Assigned' }}
              </div>
            </td>
            <td>
              <span class="status active">Active</span>
            </td>
            <td class="actions">
              <button @click="editCourse(course)" class="btn-edit">Edit</button>
              <button @click="deleteCourse(course)" class="btn-delete">Delete</button>
            </td>
          </tr>
          
          <!-- Empty state -->
          <tr v-if="filteredCourses.length === 0">
            <td colspan="7" class="table-empty">
              <i class="fas fa-book-open"></i>
              <div>
                <p v-if="search || semesterFilter">No courses match your search criteria.</p>
                <p v-else>No courses available. Click "Add Course" to create your first course.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit Course Modal -->
    <div v-if="showAddCourseModal" class="modal">
      <div class="modal-content">
        <h3>{{ editingCourse ? 'Edit Course' : 'Add New Course' }}</h3>
        <form @submit.prevent="saveCourse">
          <div class="form-group">
            <label>Course Code</label>
            <input v-model="courseForm.code" required pattern="[A-Z]{3}[0-9]{4}" 
                   title="Course code must be 3 letters followed by 4 numbers">
          </div>
          <div class="form-group">
            <label>Course Name</label>
            <input v-model="courseForm.name" required>
          </div>
          <div class="form-group">
            <label>Semester</label>
            <select v-model="courseForm.semester" required>
              <option v-for="semester in semesters" :key="semester" :value="semester">
                {{ semester }}
              </option>
            </select>
          </div>          <div class="form-group">
            <label>Year</label>
            <input type="text" v-model="courseForm.year" placeholder="e.g., 2024/2025, 2025/2026" required>
          </div>
          <div class="form-group">
            <label>Lecturer</label>
            <SearchableDropdown
              :items="availableLecturers"
              :selectedItem="selectedLecturerForForm"
              @select="onFormLecturerSelect"
              placeholder="Type to search lecturers..."
              itemType="lecturer"
              displayField="name"
              subDisplayField="email"
              :searchFields="['name', 'email']"
              :pageSize="8"
            />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save</button>
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api';
import SearchableDropdown from '../SearchableDropdown.vue';

export default {
  name: 'ManageCourses',
  components: {
    SearchableDropdown
  },
  data() {
    return {      courses: [],
      search: '',
      semesterFilter: '',
      yearFilter: '',
      showAddCourseModal: false,
      editingCourse: null,
      selectedLecturerForForm: null,
      availableLecturers: [],      courseForm: {
        code: '',
        name: '',
        semester: '',
        year: '',
        lecturer_id: null
      },      semesters: [
        'SEM 1',
        'SEM 2'
      ],
      availableYears: []
    }
  },
  computed: {    filteredCourses() {
      return this.courses.filter(course => {
        const matchesSearch = 
          course.code.toLowerCase().includes(this.search.toLowerCase()) ||
          course.name.toLowerCase().includes(this.search.toLowerCase());
        const matchesSemester = !this.semesterFilter || course.semester === this.semesterFilter;
        const matchesYear = !this.yearFilter || course.year === this.yearFilter;
        return matchesSearch && matchesSemester && matchesYear;
      });
    }
  },
  created() {
    this.fetchCourses();
    this.fetchLecturers();
  },
  methods: {    async fetchCourses() {
      try {
        const response = await api.get('/api/admin/courses');
        this.courses = response.data;
        this.extractAvailableYears();
      } catch (error) {
        console.error('Error fetching courses:', error);
        alert('Error loading courses. Please try again.');
      }
    },
    
    extractAvailableYears() {
      // Extract unique years from courses and sort them
      const years = [...new Set(this.courses.map(course => course.year))];
      this.availableYears = years.sort();
    },

    async fetchLecturers() {
      try {
        // Fetch users with lecturer role
        const response = await api.get('/api/admin/users');
        this.availableLecturers = response.data.filter(user => user.role === 'lecturer');
      } catch (error) {
        console.error('Error fetching lecturers:', error);
        alert('Error loading lecturers. Please try again.');
      }
    },

    editCourse(course) {
      this.editingCourse = course;
      this.courseForm = { 
        code: course.code,
        name: course.name,
        semester: course.semester,
        year: course.year,
        lecturer_id: course.lecturer_id
      };
      // Set the selected lecturer for the form dropdown
      this.selectedLecturerForForm = course.lecturer_id 
        ? this.availableLecturers.find(lecturer => lecturer.id == course.lecturer_id) || null
        : null;
      this.showAddCourseModal = true;
    },

    onFormLecturerSelect(lecturer) {
      this.selectedLecturerForForm = lecturer;
      this.courseForm.lecturer_id = lecturer ? lecturer.id : null;
    },

    async saveCourse() {
      try {
        if (this.editingCourse) {
          await api.put(`/api/admin/courses/${this.editingCourse.id}`, this.courseForm);
          alert('Course updated successfully!');
        } else {
          await api.post('/api/admin/courses', this.courseForm);
          alert('Course created successfully!');
        }
        this.closeModal();
        this.fetchCourses();
      } catch (error) {
        console.error('Error saving course:', error);
        alert('Error saving course. Please try again.');
      }
    },

    async toggleCourseStatus(course) {
      try {
        // Since there's no status field in the current backend, this will be a placeholder
        console.log('Toggle course status not implemented in backend yet', course);
        // For now, just refresh the courses
        this.fetchCourses();
      } catch (error) {
        console.error('Error toggling course status:', error);
      }
    },

    async deleteCourse(course) {
      if (confirm(`Are you sure you want to delete the course "${course.code} - ${course.name}"?`)) {
        try {
          await api.delete(`/api/admin/courses/${course.id}`);
          alert('Course deleted successfully!');
          this.fetchCourses();
        } catch (error) {
          console.error('Error deleting course:', error);
          if (error.response?.status === 400) {
            alert('Cannot delete course with existing enrollments.');
          } else {
            alert('Error deleting course. Please try again.');
          }
        }
      }
    },

    closeModal() {
      this.showAddCourseModal = false;
      this.editingCourse = null;
      this.selectedLecturerForForm = null;      this.courseForm = {
        code: '',
        name: '',
        semester: '',
        year: '2024/2025',
        lecturer_id: null
      };
    }
  }
}
</script>

<style scoped>
.manage-courses {
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.search-input, .semester-filter, .year-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.courses-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

/* Text overflow for long content */
td:nth-child(2), td:nth-child(5) {
  max-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Tooltip for truncated text */
.tooltip-wrapper {
  position: relative;
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.tooltip-wrapper:hover::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 1000;
  pointer-events: none;
}

/* Action buttons */
.actions {
  display: flex;
  gap: 8px;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-edit {
  background-color: #2196F3;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-delete {
  background-color: #f44336;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.9em;
}

.status.active {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.status.inactive {
  background-color: #FFEBEE;
  color: #C62828;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  min-width: 400px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

/* Basic table enhancements */
tr:hover {
  background-color: #f8f9fa;
}

.table-empty {
  text-align: center;
  padding: 40px;
  color: #999;
}

.table-empty i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #ddd;
}

/* Modal styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
}

.modal-content {
  background-color: white;
  padding: 25px;
  border-radius: 8px;
  min-width: 500px;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  z-index: 1001;
  overflow-x: hidden;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 2px solid #e1e5e9;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-secondary:hover {
  background-color: #757575;
}

/* Basic responsive design */
@media (max-width: 768px) {
  .filters {
    flex-direction: column;
    gap: 10px;
  }
    .search-input, .semester-filter, .year-filter {
    width: 100%;
  }
  
  .actions {
    flex-direction: column;
  }
  
  .btn-edit, .btn-delete {
    width: 100%;
    margin-bottom: 4px;
  }
  
  .manage-courses {
    padding: 10px;
  }
  
  .modal-content {
    margin: 10px;
    min-width: auto;
    width: calc(100% - 20px);
  }
}

/* SearchableDropdown modal integration - ensure exact width match */
.modal-content .form-group .searchable-dropdown {
  width: 100%;
  position: relative;
}

.modal-content .form-group .searchable-dropdown .search-input-dropdown {
  width: 100% !important;
  padding: 10px 40px 10px 10px !important;
  border: 2px solid #e1e5e9 !important;
  border-radius: 6px !important;
  box-sizing: border-box !important;
  font-size: 14px !important;
  transition: border-color 0.2s ease !important;
}

.modal-content .form-group .searchable-dropdown .search-input-dropdown:focus {
  outline: none !important;
  border-color: #4CAF50 !important;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1) !important;
}

.modal-content .form-group .searchable-dropdown .clear-selection {
  right: 10px !important;
}

/* Ensure form groups with SearchableDropdown have proper spacing */
.modal-content .form-group {
  position: relative;
  overflow: visible;
}
</style>
