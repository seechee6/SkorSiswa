<template>
  <div>
    <button @click="fetchUsers">Load Users</button>
    <ul>
      <li v-for="user in users" :key="user.id">
        {{ user.name }} ({{ user.role_name }})
      </li>
    </ul>
  </div>
</template>

<script>
import api from '../api';

export default {
  data() {
    return {
      users: []
    };
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await api.get('/users');
        this.users = response.data;
      } catch (error) {
        alert('Failed to fetch users');
      }
    }
  }
};
</script>