// Authentication utilities
export const auth = {
  // Get current user from localStorage
  getCurrentUser() {
    try {
      const user = localStorage.getItem('user');
      return user ? JSON.parse(user) : null;
    } catch (error) {
      console.error('Error parsing user data:', error);
      return null;
    }
  },

  // Set current user in localStorage
  setCurrentUser(user) {
    try {
      localStorage.setItem('user', JSON.stringify(user));
    } catch (error) {
      console.error('Error saving user data:', error);
    }
  },

  // Remove current user from localStorage
  removeCurrentUser() {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  },

  // Check if user is authenticated
  isAuthenticated() {
    const user = this.getCurrentUser();
    return user && user.id;
  },

  // Check if user has a specific role
  hasRole(role) {
    const user = this.getCurrentUser();
    return user && user.role_name === role;
  },

  // Check if user is an advisor
  isAdvisor() {
    return this.hasRole('advisor');
  },

  // Check if user is a lecturer
  isLecturer() {
    return this.hasRole('lecturer');
  },

  // Check if user is a student
  isStudent() {
    return this.hasRole('student');
  },

  // Check if user is an admin
  isAdmin() {
    return this.hasRole('admin');
  }
};

export default auth;