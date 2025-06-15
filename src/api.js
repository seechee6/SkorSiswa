import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
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