import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080', // Your Slim API base URL
  headers: {
    'Content-Type': 'application/json'
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

export default api;