import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8080', // Updated to match your PHP backend server port
  headers: {
    'Content-Type': 'application/json'
  }
});

export default api;