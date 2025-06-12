&lt;template&gt;
  &lt;div class="student-sidebar"&gt;
    &lt;div class="sidebar-header"&gt;Student Panel&lt;/div&gt;
    &lt;ul class="sidebar-list"&gt;
      &lt;li&gt;&lt;router-link to="/student/dashboard"&gt;Dashboard&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/marks"&gt;Mark Breakdown&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/compare"&gt;Compare with Coursemates&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/ranking"&gt;Class Ranking&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/simulator"&gt;What-If Simulator&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/trends"&gt;Performance Trends&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/appeals"&gt;Submit Appeal&lt;/router-link&gt;&lt;/li&gt;
      &lt;li&gt;&lt;router-link to="/student/notifications"&gt;Notifications&lt;/router-link&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  name: 'StudentSidebar'
}
&lt;/script&gt;

&lt;style scoped&gt;
.student-sidebar {
  width: 220px;
  background: #2c3e50;
  color: #fff;
  min-height: 100vh;
  padding-top: 20px;
  box-shadow: 2px 0 8px rgba(0,0,0,0.04);
}
.sidebar-header {
  font-size: 1.3em;
  font-weight: bold;
  padding: 16px 24px;
  border-bottom: 1px solid #34495e;
  margin-bottom: 10px;
}
.sidebar-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar-list li {
  margin: 0;
}
.sidebar-list a {
  display: block;
  color: #ecf0f1;
  text-decoration: none;
  padding: 12px 24px;
  transition: background 0.2s;
}
.sidebar-list a.router-link-exact-active,
.sidebar-list a:hover {
  background: #34495e;
}
&lt;/style&gt;
