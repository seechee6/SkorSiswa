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
import PerformanceTrends from '../components/student/PerformanceTrends.vue'
import AdvisorLayout from '../components/advisor/AdvisorLayout.vue'
import AdviseeList from '../components/advisor/AdviseeList.vue'
import AdvisorMarkBreakdown from '../components/advisor/MarkBreakdown.vue'
import AdviseeOverallPerformance from '../components/advisor/AdviseeOverallPerformance.vue'
import MeetingNotes from '../components/advisor/MeetingNotes.vue'
import RemarkRequests from '../components/student/RemarkRequests.vue'
import StudentNotifications from '../components/student/StudentNotifications.vue'
import AdminLayout from '../components/admin/AdminLayout.vue'
import AdminDashboard from '../components/admin/AdminDashboard.vue'
import AdminManageUsers from '../components/admin/ManageUsers.vue'
import AdminManageCourses from '../components/admin/ManageCourses.vue'
import AdminStudentEnrollments from '../components/admin/StudentEnrollments.vue'
import AdminLecturerAssignments from '../components/admin/LecturerAssignments.vue'
import AdminPasswordManagement from '../components/admin/PasswordManagement.vue'
import AdminLogs from '../components/admin/SystemLogs.vue'
import Login from '../components/Login.vue'
import RemarkReviews from '../components/lecturer/RemarkReviews.vue'

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
      { path: 'notifications', component: LecturerNotifications },
      { path: 'remark-reviews', component: RemarkReviews }
    ]
  },  {
    path: '/student',
    component: StudentLayout,
    children: [
      { path: '', redirect: '/student/dashboard' },      
      { path: 'dashboard', component: StudentDashboard },      
      { path: 'marks', component: MarkBreakdown },
      { path: 'compare', component: CompareWithCoursemates },
      { path: 'ranking', component: Ranking },
      { path: 'performance', component: PerformanceTrends },
      { path: 'remarks', component: RemarkRequests },
      { path: 'notifications', component: StudentNotifications }
    ]  
  },
  {
    path: '/advisor',
    component: AdvisorLayout,
    children: [
      { path: '', redirect: '/advisor/advisee-list' },
      { path: 'advisee-list', component: AdviseeList },
      { path: 'mark-breakdown/:studentId', name: 'AdvisorMarkBreakdown', component: AdvisorMarkBreakdown, props: true },
      { path: 'overall-performance/:studentId', name: 'AdviseeOverallPerformance', component: AdviseeOverallPerformance, props: true },
      { path: 'meeting-notes', component: MeetingNotes }
    ]
  },
  {
    path: '/admin',
    component: AdminLayout,
    children: [      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', component: AdminDashboard },
      { path: 'users', component: AdminManageUsers },
      { path: 'courses', component: AdminManageCourses },
      { path: 'enrollments', component: AdminStudentEnrollments },
      { path: 'assignments', component: AdminLecturerAssignments },
      { path: 'password-reset', component: AdminPasswordManagement },
      { path: 'logs', component: AdminLogs }
    ]
  },
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router