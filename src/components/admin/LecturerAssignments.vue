<template>
    <div class="lecturer-assignments">
        <div class="header">
            <h2>Lecturer Assignments</h2>
            <p>Manage lecturer assignments to courses</p>
        </div>

        <div class="filters">
            <input v-model="search" placeholder="Search lecturers or courses..." class="search-input">
            <select v-model="semesterFilter" class="semester-filter">
                <option value="">All Semesters</option>
                <option v-for="semester in semesters" :key="semester" :value="semester">
                    {{ semester }}
                </option>
            </select>
        </div>
        <div class="assignments-table">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Semester</th>
                        <th>Year</th>
                        <th>Assigned Lecturer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="course in filteredCourses" :key="course.id">
                        <td>{{ course.code }}</td>
                        <td>{{ course.name }}</td>
                        <td>{{ course.semester }}</td>
                        <td>{{ course.year }}</td>
                        <td class="lecturer-assignment">                            <SearchableDropdown :items="lecturers" :selectedItem="getLecturerById(course.lecturer_id)"
                                @select="(lecturer) => assignLecturer(course, lecturer)"
                                placeholder="Select lecturer..." itemType="lecturer" displayField="name"
                                subDisplayField="staff_id" itemKey="id" :readonly="false" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import api from '../../api';
import SearchableDropdown from '../SearchableDropdown.vue';

export default {
    name: 'LecturerAssignments',
    components: {
        SearchableDropdown
    },
    data() {
        return {
            courses: [],
            lecturers: [],
            search: '',
            semesterFilter: '',
            semesters: [
                '2024/2025-1',
                '2024/2025-2',
                '2025/2026-1',
                '2025/2026-2',
                '2026/2027-1',
                '2026/2027-2'
            ]
        }
    },
    computed: {
        filteredCourses() {
            return this.courses.filter(course => {
                const matchesSearch =
                    course.code.toLowerCase().includes(this.search.toLowerCase()) ||
                    course.name.toLowerCase().includes(this.search.toLowerCase()) ||
                    (course.lecturer_name && course.lecturer_name.toLowerCase().includes(this.search.toLowerCase()));

                const matchesSemester = !this.semesterFilter ||
                    `${course.semester} ${course.year}` === this.semesterFilter;

                return matchesSearch && matchesSemester;
            });
        }
    },
    created() {
        this.fetchCourses();
        this.fetchLecturers();
    },
    methods: {        async fetchCourses() {
            try {
                const response = await api.get('/courses');
                this.courses = response.data;
            } catch (error) {
                console.error('Error fetching courses:', error);
                // Removed alert - just log the error
            }
        }, async fetchLecturers() {
            try {
                // Fetch all users and filter for lecturers
                const response = await api.get('/api/admin/users');
                this.lecturers = response.data.filter(user => user.role === 'lecturer');
            } catch (error) {
                console.error('Error fetching lecturers:', error);
                // Removed alert - just log the error
            }
        },
        getLecturerById(lecturerId) {
            return this.lecturers.find(lecturer => lecturer.id === lecturerId) || null;
        },    async assignLecturer(course, lecturer) {
      try {
        // Send only the necessary fields for the update
        const updatedCourse = {
          code: course.code,
          name: course.name,
          semester: course.semester,
          year: course.year,
          lecturer_id: lecturer ? lecturer.id : null
        };
        
        const response = await api.put(`/courses/${course.id}`, updatedCourse);
        
        if (response.data.success) {
          // Refresh the courses list to get updated data from server
          await this.fetchCourses();
          
          console.log(`Lecturer ${lecturer ? 'assigned' : 'unassigned'} successfully! Activity logged to system.`);
        } else {
          throw new Error('Update failed');
        }
      } catch (error) {
        console.error('Error assigning lecturer:', error);
        // Removed alert - just log the error
      }
    }
    }
}
</script>

<style scoped>
.lecturer-assignments {
    padding: 20px;
}

.header {
    margin-bottom: 20px;
}

.header h2 {
    margin-bottom: 5px;
}

.header p {
    color: #666;
    margin: 0;
}

.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.search-input,
.semester-filter {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-input {
    flex: 1;
}

.assignments-table {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.lecturer-assignment {
    min-width: 250px;
}

.lecturer-assignment .searchable-dropdown {
    width: 100%;
}

@media (max-width: 768px) {
    .filters {
        flex-direction: column;
    }

    .assignments-table {
        font-size: 14px;
    }

    th,
    td {
        padding: 8px;
    }

    .lecturer-assignment {
        min-width: 200px;
    }
}
</style>
