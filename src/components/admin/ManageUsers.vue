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
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.role }}</td>
            <td>
              <span :class="['status', user.status]">{{ user.status }}</span>
            </td>
            <td class="actions">
              <button @click="editUser(user)" class="btn-edit">Edit</button>
              <button @click="resetPassword(user.id)" class="btn-reset">Reset Password</button>
              <button @click="toggleUserStatus(user)" class="btn-toggle">
                {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add/Edit User Modal -->
    <div v-if="showAddUserModal" class="modal">
      <div class="modal-content">
        <h3>{{ editingUser ? 'Edit User' : 'Add New User' }}</h3>
        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label>Name</label>
            <input v-model="userForm.name" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="userForm.email" type="email" required>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select v-model="userForm.role" required>
              <option value="student">Student</option>
              <option value="lecturer">Lecturer</option>
              <option value="admin">Admin</option>
              <option value="advisor">Advisor</option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save</button>
            <button type="button" @click="showAddUserModal = false" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- New User Form (for Add User modal) -->
    <div v-if="showAddUserModal" class="modal">
      <div class="modal-content">
        <h3>Add New User</h3>
        <form @submit.prevent="addUser">
          <div class="form-group">
            <label>Full Name</label>
            <input v-model="newUser.name" required>
          </div>
          <div class="form-group">
            <label>Matric Number</label>
            <input v-model="newUser.matric_no" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input v-model="newUser.email" type="email" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input v-model="newUser.password" type="password" required>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select v-model="newUser.role" required>
              <option value="student">Student</option>
              <option value="lecturer">Lecturer</option>
              <option value="advisor">Advisor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Add User</button>
            <button type="button" @click="showAddUserModal = false" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ManageUsers',
  data() {
    return {
      users: [],
      search: '',
      roleFilter: '',
      showAddUserModal: false,
      editingUser: null,
      userForm: {
        name: '',
        email: '',
        role: 'student'
      },
      newUser: {
        name: '',
        matric_no: '',
        email: '',
        password: '',
        role: 'student'
      }
    }
  },
  computed: {
    filteredUsers() {
      return this.users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(this.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.search.toLowerCase());
        const matchesRole = !this.roleFilter || user.role === this.roleFilter;
        return matchesSearch && matchesRole;
      });
    }
  },
  created() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await axios.get('/api/admin/users');
        this.users = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    },
    editUser(user) {
      this.editingUser = user;
      this.userForm = { ...user };
      this.showAddUserModal = true;
    },
    async saveUser() {
      try {
        if (this.editingUser) {
          await axios.put(`/api/admin/users/${this.editingUser.id}`, this.userForm);
        } else {
          await axios.post('/api/admin/users', this.userForm);
        }
        this.showAddUserModal = false;
        this.fetchUsers();
      } catch (error) {
        console.error('Error saving user:', error);
      }
    },
    async addUser() {
      try {
        await axios.post('/api/admin/users', this.newUser);
        this.showAddUserModal = false;
        this.fetchUsers();
      } catch (error) {
        console.error('Error adding user:', error);
      }
    },
    async resetPassword(userId) {
      try {
        await axios.post(`/api/admin/users/${userId}/reset-password`);
        alert('Password reset email has been sent.');
      } catch (error) {
        console.error('Error resetting password:', error);
      }
    },
    async toggleUserStatus(user) {
      try {
        const newStatus = user.status === 'active' ? 'inactive' : 'active';
        await axios.patch(`/api/admin/users/${user.id}/status`, { status: newStatus });
        this.fetchUsers();
      } catch (error) {
        console.error('Error toggling user status:', error);
      }
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

.btn-reset {
  background-color: #FF9800;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-toggle {
  background-color: #9E9E9E;
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
  }
  
  .actions {
    flex-direction: column;
  }
  
  .btn-edit, .btn-reset, .btn-toggle {
    width: 100%;
    margin-bottom: 4px;
  }
}
</style>
