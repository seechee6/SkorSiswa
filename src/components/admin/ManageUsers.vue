<template>
  <div class="manage-users">
    <div class="header">
      <h2>Manage Users</h2>
      <button @click="showAddUserModal = true" class="btn-primary">Add User</button>
    </div>

    <div class="filters">
      <input v-model="search" placeholder="Search users..." class="search-input">
      <select v-model="roleFilter" class="role-filter">
        <option value="">All Roles</option>
        <option value="student">Student</option>
        <option value="lecturer">Lecturer</option>
        <option value="admin">Admin</option>
        <option value="advisor">Advisor</option>
      </select>
    </div>

    <div class="users-table">
      <table>        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>ID Number</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.matric_no || user.staff_id || 'N/A' }}</td>
            <td>{{ user.role }}</td>
            <td class="actions">
              <button @click="editUser(user)" class="btn-edit">Edit</button>
              <button @click="deleteUser(user)" class="btn-delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>    <!-- Add/Edit User Modal -->
    <div v-if="showAddUserModal" class="modal">
      <div class="modal-content">
        <h3>{{ editingUser ? 'Edit User' : 'Add New User' }}</h3>        <form @submit.prevent="editingUser ? saveUser() : addUser()">
          <div class="form-group">
            <label>Full Name</label>
            <input v-if="editingUser" v-model="userForm.name" required class="form-input">
            <input v-else v-model="newUser.name" required class="form-input">
          </div>
          <div class="form-group" v-if="!editingUser">
            <label>{{ newUser.role === 'student' ? 'Matric Number' : 'Staff ID' }}</label>
            <input 
              v-if="newUser.role === 'student'" 
              v-model="newUser.matric_no" 
              required 
              class="form-input"
              placeholder="Enter matric number"
            >
            <input 
              v-else 
              v-model="newUser.staff_id" 
              required 
              class="form-input"
              placeholder="Enter staff ID"
            >
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-if="editingUser" v-model="userForm.email" type="email" required class="form-input">
            <input v-else v-model="newUser.email" type="email" required class="form-input">
          </div>          <div class="form-group" v-if="!editingUser">
            <label>Password</label>
            <div class="password-input-container">
              <input 
                v-model="newUser.password" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                class="form-input"
                placeholder="Enter password (min. 6 characters)"
                minlength="6"
              >
              <button 
                type="button" 
                @click="togglePasswordVisibility" 
                class="btn-toggle-password"
              >
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select v-if="editingUser" v-model="userForm.role" required class="form-input">
              <option value="student">Student</option>
              <option value="lecturer">Lecturer</option>
              <option value="advisor">Advisor</option>
              <option value="admin">Admin</option>
            </select>
            <select v-else v-model="newUser.role" required class="form-input">
              <option value="student">Student</option>
              <option value="lecturer">Lecturer</option>
              <option value="advisor">Advisor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">{{ editingUser ? 'Save' : 'Add User' }}</button>
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api';

export default {
  name: 'ManageUsers',  data() {
    return {
      users: [],
      search: '',
      roleFilter: '',
      showAddUserModal: false,
      editingUser: null,
      showPassword: false,
      userForm: {
        name: '',
        email: '',
        role: 'student'
      },newUser: {
        name: '',
        matric_no: '',
        staff_id: '',
        email: '',
        password: '',
        role: 'student'
      }
    }
  },
  computed: {    filteredUsers() {
      return this.users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(this.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.search.toLowerCase()) ||
                            (user.matric_no && user.matric_no.toLowerCase().includes(this.search.toLowerCase())) ||
                            (user.staff_id && user.staff_id.toLowerCase().includes(this.search.toLowerCase()));
        const matchesRole = !this.roleFilter || user.role === this.roleFilter;
        return matchesSearch && matchesRole;
      });
    }
  },
  created() {
    this.fetchUsers();
  },
  methods: {    async fetchUsers() {
      try {
        const response = await api.get('/api/admin/users');
        this.users = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    editUser(user) {
      this.editingUser = user;
      this.userForm = { ...user };
      this.showAddUserModal = true;
    },    async saveUser() {
      try {
        if (this.editingUser) {
          await api.put(`/api/admin/users/${this.editingUser.id}`, this.userForm);
        } else {
          await api.post('/api/admin/users', this.userForm);
        }
        this.showAddUserModal = false;
        this.fetchUsers();
      } catch (error) {
        console.error('Error saving user:', error);
      }
    },
    async addUser() {
      try {
        await api.post('/api/admin/users', this.newUser);
        this.showAddUserModal = false;
        this.fetchUsers();      } catch (error) {
        console.error('Error adding user:', error);
      }    },
    async deleteUser(user) {
      if (confirm(`Are you sure you want to delete the user "${user.name}"? This action cannot be undone.`)) {
        try {
          await api.delete(`/api/admin/users/${user.id}`);
          alert('User deleted successfully!');
          this.fetchUsers();
        } catch (error) {
          console.error('Error deleting user:', error);
          
          // More detailed error handling
          if (error.response) {
            const status = error.response.status;
            const message = error.response.data?.message || error.response.data?.error || 'Unknown error';
            
            if (status === 400) {
              alert(`Cannot delete user: ${message}`);
            } else if (status === 404) {
              alert('User not found. It may have already been deleted.');
              this.fetchUsers(); // Refresh the list
            } else if (status === 403) {
              alert('You do not have permission to delete this user.');
            } else if (status === 500) {
              alert('Server error occurred while deleting user. Please try again later.');
            } else {
              alert(`Error deleting user (${status}): ${message}`);
            }
          } else if (error.request) {
            alert('Network error: Unable to connect to server. Please check your connection.');
          } else {
            alert('An unexpected error occurred. Please try again.');
          }
        }
      }
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },    closeModal() {
      this.showAddUserModal = false;
      this.editingUser = null;
      this.showPassword = false;
      this.userForm = {
        name: '',
        email: '',
        role: 'student'
      };this.newUser = {
        name: '',
        matric_no: '',
        staff_id: '',
        email: '',
        password: '',
        role: 'student'
      };
    }
  }
}
</script>

<style scoped>
.manage-users {
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

.search-input, .role-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.users-table {
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
  font-weight: bold;
  color: #333;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 2px solid #e1e5e9;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 14px;
  transition: border-color 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
}

.password-input-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.password-input-container .form-input {
  flex: 1;
}

.btn-toggle-password {
  background-color: #2196F3;
  color: white;
  padding: 10px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  white-space: nowrap;
  transition: background-color 0.2s ease;
}

.btn-toggle-password:hover {
  background-color: #1976D2;
}

.form-group input, .form-group select {
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

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }    .actions {
    flex-direction: column;
  }
  
  .btn-edit, .btn-delete {
    width: 100%;
    margin-bottom: 4px;
  }
}
</style>
