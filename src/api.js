import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080', // Your Slim API base URL (running on port 8080)
  headers: {
    'Content-Type': 'application/json'
  },
  withCredentials: true // Enable sending cookies in cross-origin requests
});

// Add a request interceptor to include auth token
api.interceptors.request.use(
    config => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// Add a response interceptor to handle errors
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Handle specific error cases
            switch (error.response.status) {
                case 401:
                    // Unauthorized - clear token and redirect to login
                    localStorage.removeItem('token');
                    window.location.href = '/';
                    break;
                case 403:
                    // Forbidden
                    console.error('Access denied');
                    break;
                case 422:
                    // Validation errors
                    console.error('Validation failed:', error.response.data.errors);
                    break;
                default:
                    console.error('API Error:', error.response.data);
            }
        } else if (error.request) {
            // The request was made but no response was received
            // This could be due to CORS issues or network problems
            console.error('Network Error:', error.message);
            if (error.message.includes('Network Error')) {
                console.error('This might be a CORS issue. Check that the backend server is running and CORS is properly configured.');
            }
        } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Request Error:', error.message);
        }
        return Promise.reject(error);
    }
);

// Student API methods
export const studentApi = {
    // Get student dashboard data
    getDashboard(studentId) {
        return api.get(`/student/dashboard/${studentId}`);
    },
    
    // Get mark breakdown for a course
    getMarkBreakdown(studentId, courseId) {
        return api.get(`/student/marks/${studentId}/${courseId}`);
    },
    
    // Get comparison with coursemates
    getComparison(studentId, courseId) {
        return api.get(`/student/compare/${studentId}/${courseId}`);
    },
    
    // Get ranking information
    getRanking(studentId, courseId) {
        return api.get(`/student/ranking/${studentId}/${courseId}`);
    },
    
    // Get what-if simulator data
    getSimulatorData(studentId, courseId) {
        return api.get(`/student/simulator/${studentId}/${courseId}`);
    },
    
    // Get performance trends
    getPerformanceTrends(studentId) {
        return api.get(`/student/trends/${studentId}`);
    },
    
    // Get remark requests
    getRemarkRequests(studentId) {
        return api.get(`/student/remarks/${studentId}`);
    },
    
    // Submit new remark request
    submitRemarkRequest(requestData) {
        return api.post('/student/remarks', requestData);
    },
    
    // Update or cancel remark request
    updateRemarkRequest(requestId, requestData) {
        return api.put(`/student/remarks/${requestId}`, requestData);
    }
};

// Attach studentApi to the main api object
api.student = studentApi;

export default api;