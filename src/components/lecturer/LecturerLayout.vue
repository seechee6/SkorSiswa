<template>
  <div class="lecturer-layout">
    <LecturerSidebar />
    <div class="lecturer-main">
      <router-view />
    </div>
  </div>
</template>
<script>
import LecturerSidebar from './LecturerSidebar.vue'
export default {
  name: 'LecturerLayout',
  components: { LecturerSidebar }
}
</script>
<style scoped>
.lecturer-layout {
  display: flex;
  min-height: 100vh;
}
.lecturer-main {
  flex: 1;
  padding: 32px 40px;
  background: #f7f9fa;
}
</style>
