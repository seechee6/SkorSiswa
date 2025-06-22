<template>  <div class="password-management">    <!-- Change My Password Section -->
    <div class="my-password-section">
      <div class="header text-center">
        <h2>Change My Password</h2>
        <p>Update your own admin password</p>
        <button @click="showMyPasswordForm = !showMyPasswordForm" class="btn-toggle-form">
          {{ showMyPasswordForm ? 'Hide Form' : 'Show Form' }}
        </button>
      </div>
      
      <div v-if="showMyPasswordForm" class="change-password-form-container">
        <div class="change-password-form">
          <form @submit.prevent="changeMyPassword">
            <div class="form-group">
              <label>Current Password</label>
              <div class="password-input-container">
                <input 
                  v-model="currentPassword" 
                  :type="showCurrentPassword ? 'text' : 'password'" 
                  required 
                  class="password-input"
                  placeholder="Enter your current password"
                >
                <button 
                  type="button" 
                  @click="showCurrentPassword = !showCurrentPassword" 
                  class="btn-toggle-password"
                >
                  {{ showCurrentPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>
            <div class="form-group">
              <label>New Password</label>
              <div class="password-input-container">
                <input 
                  v-model="myNewPassword" 
                  :type="showMyNewPassword ? 'text' : 'password'" 
                  required 
                  minlength="6"
                  class="password-input"
                  placeholder="Enter new password (min. 6 characters)"
                >
                <button 
                  type="button" 
                  @click="showMyNewPassword = !showMyNewPassword" 
                  class="btn-toggle-password"
                >
                  {{ showMyNewPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <div class="password-input-container">
                <input 
                  v-model="myConfirmPassword" 
                  :type="showMyConfirmPassword ? 'text' : 'password'" 
                  required 
                  minlength="6"
                  class="password-input"
                  placeholder="Confirm new password"
                >
                <button 
                  type="button" 
                  @click="showMyConfirmPassword = !showMyConfirmPassword" 
                  class="btn-toggle-password"
                >
                  {{ showMyConfirmPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>
            <div v-if="myPasswordError" class="error-message">
              {{ myPasswordError }}
            </div>
            <div class="form-actions text-center">
              <button type="submit" class="btn-primary" :disabled="!canSubmitMyPassword">
                Change My Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <hr class="section-divider">

    <div class="header">
      <h2>Password Management</h2>
      <p>Set new passwords for users in the system</p>
    </div>

    <div class="filters">
      <input v-model="search" placeholder="Search users..." class="search-input">      <select v-model="roleFilter" class="role-filter">
        <option value="">All Roles</option>
        <option value="admin">Admins</option>
        <option value="lecturer">Lecturers</option>
        <option value="student">Students</option>
        <option value="advisor">Advisors</option>
      </select>
    </div>

    <div class="users-table">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Staff ID / Matric No</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.staff_id || user.matric_no }}</td>
            <td>
              <span class="role-badge" :class="user.role">{{ user.role }}</span>
            </td>            <td class="actions">
              <button @click="resetPassword(user)" class="btn-reset">Set Password</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>    <!-- Reset Password Modal -->
    <div v-if="showResetModal" class="modal">
      <div class="modal-content">
        <h3>Set New Password</h3>
        <p>Set a new password for <strong>{{ selectedUser?.name }}</strong>:</p>
        <form @submit.prevent="confirmPasswordReset">
          <div class="form-group">
            <label>New Password</label>
            <div class="password-input-container">
              <input 
                v-model="newPassword" 
                :type="showPassword ? 'text' : 'password'" 
                required 
                minlength="6"
                class="password-input"
                placeholder="Enter new password (min. 6 characters)"
              >
              <button 
                type="button" 
                @click="togglePasswordVisibility" 
                class="btn-toggle-password"
              >
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
          </div>          <div class="form-group">
            <label>Confirm Password</label>
            <div class="password-input-container">
              <input 
                v-model="confirmPassword" 
                :type="showConfirmPassword ? 'text' : 'password'" 
                required 
                minlength="6"
                class="password-input"
                placeholder="Confirm new password"
              >
              <button 
                type="button" 
                @click="showConfirmPassword = !showConfirmPassword" 
                class="btn-toggle-password"
              >
                {{ showConfirmPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
          </div>
          <div v-if="passwordError" class="error-message">
            {{ passwordError }}
          </div>
          <p class="info">The user will be able to login with this new password immediately.</p>
          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="!canSubmit">Set Password</button>
            <button type="button" @click="closeResetModal" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="modal">
      <div class="modal-content">
        <h3>Password Updated Successfully</h3>
        <p>Password for <strong>{{ selectedUser?.name }}</strong> has been updated successfully.</p>
        <p class="success">The user can now login with the new password.</p>
        <div class="form-actions">
          <button @click="closeSuccessModal" class="btn-primary">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../../api';

export default {
  name: 'PasswordManagement',  data() {
    return {
      users: [],
      search: '',
      roleFilter: '',
      showResetModal: false,
      showSuccessModal: false,
      selectedUser: null,      newPassword: '',
      confirmPassword: '',
      showPassword: false,
      showConfirmPassword: false,      passwordError: '',
      // My password change fields
      showMyPasswordForm: false,
      currentPassword: '',
      myNewPassword: '',
      myConfirmPassword: '',
      showCurrentPassword: false,
      showMyNewPassword: false,
      showMyConfirmPassword: false,
      myPasswordError: '',
      currentAdminId: null
    }
  },computed: {    filteredUsers() {
      return this.users.filter(user => {
        // Exclude the current admin from the list
        if (this.currentAdminId && user.id === this.currentAdminId) {
          return false;
        }
        
        // Include all other users including other admins
        const matchesSearch = 
          user.name.toLowerCase().includes(this.search.toLowerCase()) ||
          user.email.toLowerCase().includes(this.search.toLowerCase()) ||
          (user.staff_id && user.staff_id.toLowerCase().includes(this.search.toLowerCase())) ||
          (user.matric_no && user.matric_no.toLowerCase().includes(this.search.toLowerCase()));
        
        const matchesRole = !this.roleFilter || user.role === this.roleFilter;
        
        return matchesSearch && matchesRole;
      });
    },canSubmit() {
      return this.newPassword && 
             this.confirmPassword && 
             this.newPassword.length >= 6 && 
             this.newPassword === this.confirmPassword;
    },
    canSubmitMyPassword() {
      return this.currentPassword && 
             this.myNewPassword && 
             this.myConfirmPassword && 
             this.myNewPassword.length >= 6 && 
             this.myNewPassword === this.myConfirmPassword;
    }
  },  created() {
    this.fetchCurrentAdmin();
    this.fetchUsers();
  },  methods: {    async fetchCurrentAdmin() {
      try {
        const response = await api.get('/api/admin/current-admin');
        this.currentAdminId = response.data.id;
      } catch (error) {
        console.error('Error fetching current admin:', error);
        // Fallback: assume the first admin user in the list is the current admin
        this.setCurrentAdminFromUserList();
      }
    },
    
    setCurrentAdminFromUserList() {
      // Find the first admin user in the users list and set as current admin
      const adminUser = this.users.find(user => user.role === 'admin');
      if (adminUser) {
        this.currentAdminId = adminUser.id;
      }
    },
      async fetchUsers() {
      try {
        const response = await api.get('/api/admin/users');
        this.users = response.data;
        // Set current admin ID if not already set
        if (!this.currentAdminId) {
          this.setCurrentAdminFromUserList();
        }
      } catch (error) {
        console.error('Error fetching users:', error);
        alert('Error loading users. Please try again.');      }
    },
    
    async changeMyPassword() {
      // Validate passwords match
      if (this.myNewPassword !== this.myConfirmPassword) {
        this.myPasswordError = 'New passwords do not match';
        return;
      }
      
      // Validate password length
      if (this.myNewPassword.length < 6) {
        this.myPasswordError = 'New password must be at least 6 characters long';
        return;
      }
      
      // Check if new password is different from current
      if (this.currentPassword === this.myNewPassword) {
        this.myPasswordError = 'New password must be different from current password';
        return;
      }
      
      this.myPasswordError = '';
        try {
        await api.put('/api/admin/change-my-password', {
          current_password: this.currentPassword,
          new_password: this.myNewPassword
        });
        
        // Clear form
        this.currentPassword = '';
        this.myNewPassword = '';
        this.myConfirmPassword = '';
        this.showCurrentPassword = false;
        this.showMyNewPassword = false;
        this.showMyConfirmPassword = false;
        this.showMyPasswordForm = false;
        
        alert('Your password has been changed successfully!');
      } catch (error) {
        console.error('Error changing password:', error);
        if (error.response?.status === 400) {
          this.myPasswordError = 'Current password is incorrect';
        } else if (error.response?.status === 404) {
          this.myPasswordError = 'Admin user not found. Please contact support.';
        } else {
          this.myPasswordError = 'Error changing password. Please try again.';
        }
      }
    },

    resetPassword(user) {
      this.selectedUser = user;
      this.newPassword = '';
      this.confirmPassword = '';
      this.passwordError = '';
      this.showPassword = false;
      this.showResetModal = true;
    },
    async confirmPasswordReset() {
      // Validate passwords match
      if (this.newPassword !== this.confirmPassword) {
        this.passwordError = 'Passwords do not match';
        return;
      }
      
      // Validate password length
      if (this.newPassword.length < 6) {
        this.passwordError = 'Password must be at least 6 characters long';
        return;
      }
      
      this.passwordError = '';
      
      try {
        await api.put(`/api/admin/users/${this.selectedUser.id}/password`, {
          new_password: this.newPassword
        });
        
        this.showResetModal = false;
        this.showSuccessModal = true;
      } catch (error) {
        console.error('Error setting password:', error);
        this.passwordError = 'Error setting password. Please try again.';
      }
    },    closeResetModal() {
      this.showResetModal = false;
      this.selectedUser = null;
      this.newPassword = '';
      this.confirmPassword = '';
      this.passwordError = '';
      this.showPassword = false;
      this.showConfirmPassword = false;
    },
    closeSuccessModal() {
      this.showSuccessModal = false;
      this.selectedUser = null;
    },
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    }
  }
}
</script>

<style scoped>
.password-management {
  padding: 20px;
}

.my-password-section {
  background-color: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  border: 1px solid #e9ecef;
}

.my-password-section .header {
  margin-bottom: 20px;
}

.text-center {
  text-align: center;
}

.btn-toggle-form {
  background-color: #2196F3;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.2s ease;
}

.btn-toggle-form:hover {
  background-color: #1976D2;
}

.change-password-form-container {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.change-password-form {
  max-width: 400px;
  width: 100%;
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.section-divider {
  border: none;
  height: 2px;
  background-color: #e9ecef;
  margin: 30px 0;
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

.search-input, .role-filter {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.search-input {
  flex: 1;
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

th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.role-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.9em;
  text-transform: capitalize;
}

.role-badge.lecturer {
  background-color: #E3F2FD;
  color: #1976D2;
}

.role-badge.student {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.role-badge.advisor {
  background-color: #FFF3E0;
  color: #F57C00;
}

.role-badge.admin {
  background-color: #F3E5F5;
  color: #7B1FA2;
}

.actions {
  display: flex;
  gap: 8px;
}

.btn-reset {
  background-color: #FF9800;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-danger {
  background-color: #f44336;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-secondary {
  background-color: #9E9E9E;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-copy {
  background-color: #2196F3;
  color: white;
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-left: 10px;
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
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  min-width: 400px;
  max-width: 500px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.warning {
  color: #FF9800;
  font-size: 0.9em;
  margin: 10px 0;
}

.info {
  color: #2196F3;
  font-size: 0.9em;
  margin: 10px 0;
}

.success {
  color: #4CAF50;
  font-size: 0.9em;
  margin: 10px 0;
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

.password-input-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.password-input {
  flex: 1;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.password-input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.btn-toggle-password {
  background-color: #2196F3;
  color: white;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.btn-toggle-password:hover {
  background-color: #1976D2;
}

.error-message {
  color: #f44336;
  font-size: 0.9em;
  margin: 10px 0;
  padding: 8px;
  background-color: #ffebee;
  border-radius: 4px;
  border-left: 4px solid #f44336;
}

@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }
  
  .modal-content {
    margin: 10px;
    min-width: auto;
  }
  
  .password-input-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .btn-toggle-password {
    align-self: flex-start;
    margin-top: 5px;
  }
}
</style>
