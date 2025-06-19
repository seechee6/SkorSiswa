import { createRouter, createWebHistory } from 'vue-router'
import LecturerLayout from '../components/lecturer/LecturerLayout.vue'
import LecturerDashboard from '../components/lecturer/LecturerDashboard.vue'
import ManageCourses from '../components/lecturer/ManageCourses.vue'
import ManageEnrollment from '../components/lecturer/ManageEnrollment.vue'
import ViewEnrolledStudents from '../components/lecturer/ViewEnrolledStudents.vue'
import ManageAssessments from '../components/lecturer/ManageAssessments.vue'
import EnterFinalExam from '../components/lecturer/EnterFinalExam.vue'
import BulkUploadCSV from '../components/lecturer/BulkUploadCSV.vue'
import ExportCSV from '../components/lecturer/ExportCSV.vue'
import FeedbackRemarks from '../components/lecturer/FeedbackRemarks.vue'
import FullMarkBreakdown from '../components/lecturer/FullMarkBreakdown.vue'
import LecturerAnalytics from '../components/lecturer/Analytics.vue'
import TrendGraphs from '../components/lecturer/TrendGraphs.vue'
import LecturerNotifications from '../components/lecturer/Notifications.vue'
import StudentLayout from '../components/student/StudentLayout.vue'
import StudentDashboard from '../components/student/StudentDashboard.vue'
import MarkBreakdown from '../components/student/MarkBreakdown.vue'
import CompareWithCoursemates from '../components/student/CompareWithCoursemates.vue'
import Ranking from '../components/student/Ranking.vue'
import WhatIfSimulator from '../components/student/WhatIfSimulator.vue'
import PerformanceTrends from '../components/student/PerformanceTrends.vue'
import RemarkRequests from '../components/student/RemarkRequests.vue'
import AdvisorDashboard from '../components/advisor/AdvisorDashboard.vue'
import AdminDashboard from '../components/admin/AdminDashboard.vue'
import Login from '../components/Login.vue'

const routes = [
  { path: '/', component: Login },
  {
    path: '/lecturer',
    component: LecturerLayout,
    children: [
      { path: '', redirect: '/lecturer/dashboard' },
      { path: 'dashboard', component: LecturerDashboard },
      { path: 'manage-courses', component: ManageCourses },
      { path: 'manage-enrollment', component: ManageEnrollment },
      { path: 'view-enrolled/:courseId', component: ViewEnrolledStudents },
      { path: 'manage-assessments', component: ManageAssessments },
      { path: 'enter-final-marks', component: EnterFinalExam },
      { path: 'bulk-upload', component: BulkUploadCSV },
      { path: 'export-csv', component: ExportCSV },
      { path: 'feedback', component: FeedbackRemarks },
      { path: 'mark-breakdown', component: FullMarkBreakdown },
      { path: 'analytics', component: LecturerAnalytics },
      { path: 'trend-graphs', component: TrendGraphs },
      { path: 'notifications', component: LecturerNotifications }
    ]
  },  {
    path: '/student',
    component: StudentLayout,
    children: [
      { path: '', redirect: '/student/dashboard' },      { path: 'dashboard', component: StudentDashboard },
      { path: 'marks', component: MarkBreakdown },
      { path: 'compare', component: CompareWithCoursemates },
      { path: 'ranking', component: Ranking },
      { path: 'simulator', component: WhatIfSimulator },
      { path: 'performance', component: PerformanceTrends },
      { path: 'remarks', component: RemarkRequests }
    ]
  },
  { path: '/advisor', component: AdvisorDashboard },
  { path: '/admin', component: AdminDashboard },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router