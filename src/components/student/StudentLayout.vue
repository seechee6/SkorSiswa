&lt;template&gt;
  &lt;div class="student-layout"&gt;
    &lt;StudentSidebar /&gt;
    &lt;div class="student-main"&gt;
      &lt;router-view /&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
import StudentSidebar from './StudentSidebar.vue'
export default {
  name: 'StudentLayout',
  components: { StudentSidebar }
}
&lt;/script&gt;

&lt;style scoped&gt;
.student-layout {
  display: flex;
  min-height: 100vh;
}
.student-main {
  flex: 1;
  padding: 32px 40px;
  background: #f7f9fa;
}
&lt;/style&gt;
