<template>
  <div class="meeting-notes">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading advisees...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-container">
      <div class="error-message">
        <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ error }}
      </div>
      <button @click="fetchAdvisees" class="retry-btn">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Retry      </button>
    </div>

    <!-- Main Content -->
    <div v-if="!loading && !error" class="main-content">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-content">
        <h2>Meeting Notes & Advisor Sessions</h2>
        <p class="welcome-text">Schedule and manage advisor meetings with advisees</p>
      </div>
      <div class="header-actions">
        <button @click="showScheduleModal = true" class="action-btn primary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Schedule Meeting
        </button>        <button @click="exportMeetings" class="action-btn secondary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export
        </button>
        <button @click="showQuickNotesModal" class="action-btn tertiary">
          <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Quick Notes
        </button>
      </div>
    </div>

    <!-- Filter and Search Section -->
    <div class="filters-section">
      <div class="card">
        <div class="filter-row">
          <div class="search-box">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search meetings by student name, notes, or meeting type..."
              class="search-input"
            >
          </div>
          <div class="filter-group">
            <select v-model="statusFilter" class="filter-select">
              <option value="">All Status</option>
              <option value="scheduled">Scheduled</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="rescheduled">Rescheduled</option>
            </select>
            <select v-model="typeFilter" class="filter-select">
              <option value="">All Types</option>
              <option value="academic">Academic Progress</option>
              <option value="career">Career Guidance</option>
              <option value="personal">Personal Support</option>
              <option value="crisis">Crisis Intervention</option>
              <option value="routine">Routine Check-in</option>
            </select>
            <input 
              v-model="dateFilter" 
              type="date" 
              class="filter-select"
              placeholder="Filter by date"
            >
            <button @click="clearFilters" class="clear-filter-btn">Clear</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">{{ upcomingMeetings.length }}</div>
          <div class="stat-label">Upcoming Meetings</div>
          <div class="stat-trend positive">+{{ newMeetingsThisWeek }} this week</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ completedMeetings.length }}</div>
          <div class="stat-label">Completed This Month</div>
          <div class="stat-trend positive">{{ completionRate }}% completion rate</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ urgentMeetings.length }}</div>
          <div class="stat-label">High Priority</div>
          <div class="stat-trend" :class="urgentMeetings.length > 5 ? 'negative' : 'positive'">Crisis & Academic</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ averageMeetingDuration }}</div>
          <div class="stat-label">Avg Duration (min)</div>
          <div class="stat-trend neutral">Per meeting</div>
        </div>
      </div>
    </div>

    <!-- Meetings Table -->
    <div class="card">
      <div class="table-header">
        <h4>Meeting Schedule & Notes</h4>
        <div class="table-actions">
          <div class="view-toggle">
            <button 
              @click="viewMode = 'table'" 
              :class="{ active: viewMode === 'table' }"
              class="toggle-btn"
            >
              Table
            </button>
            <button 
              @click="viewMode = 'calendar'" 
              :class="{ active: viewMode === 'calendar' }"
              class="toggle-btn"
            >
              Calendar
            </button>
          </div>
        </div>
      </div>

      <!-- Table View -->
      <div v-if="viewMode === 'table'" class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>
                <input type="checkbox" v-model="selectAll" @change="toggleAllSelection">
              </th>
              <th @click="sortBy('student')" class="sortable">
                Student 
                <svg class="sort-icon" :class="{ active: sortColumn === 'student' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
              </th>
              <th @click="sortBy('date')" class="sortable">
                Date & Time
                <svg class="sort-icon" :class="{ active: sortColumn === 'date' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                </svg>
              </th>              <th>Type</th>
              <th>Status</th>
              <th>Duration</th>
              <th>Location/Link</th>
              <th>Notes Preview</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="meeting in paginatedMeetings" :key="meeting.id" class="table-row">
              <td>
                <input type="checkbox" v-model="selectedMeetings" :value="meeting.id">
              </td>
              <td>
                <div class="student-info">
                  <div class="student-avatar">{{ getInitials(meeting.student.name) }}</div>
                  <div>
                    <div class="student-name">{{ meeting.student.name }}</div>
                    <div class="student-id">{{ meeting.student.id }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div class="meeting-datetime">
                  <div class="meeting-date">{{ formatDate(meeting.date) }}</div>
                  <div class="meeting-time">{{ meeting.time }}</div>
                </div>
              </td>
              <td>
                <span class="meeting-type-badge" :class="meeting.type">
                  {{ formatMeetingType(meeting.type) }}
                </span>
              </td>
              <td>
                <span class="status-badge" :class="meeting.status">
                  {{ meeting.status }}
                </span>
              </td>              <td>{{ meeting.duration || '--' }} min</td>
              <td>
                <div class="location-link-info">
                  <div v-if="meeting.location" class="location">📍 {{ meeting.location }}</div>
                  <div v-if="meeting.meeting_link || meeting.meetingLink" class="meeting-link">
                    <a :href="meeting.meeting_link || meeting.meetingLink" target="_blank" rel="noopener noreferrer" class="link-btn">
                      🔗 Join Meeting
                    </a>
                  </div>
                  <div v-if="!meeting.location && !meeting.meeting_link && !meeting.meetingLink" class="no-location">
                    --
                  </div>
                </div>
              </td>
              <td>
                <div class="notes-preview">{{ truncateNotes(meeting.notes) }}</div>
              </td>
              <td>
                <div class="action-buttons">                  <button @click="addNotes(meeting)" class="action-btn notes-btn" title="Add/Edit Notes">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="editMeeting(meeting)" class="action-btn edit-btn" title="Edit Meeting">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="viewNotes(meeting)" class="action-btn view-btn" title="View Full Notes">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="markComplete(meeting)" v-if="meeting.status === 'scheduled'" class="action-btn complete-btn" title="Mark Complete">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </button>
                  <button @click="reschedule(meeting)" v-if="meeting.status === 'scheduled'" class="action-btn reschedule-btn" title="Reschedule">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </button>
                  <button @click="deleteMeeting(meeting)" class="action-btn delete-btn" title="Delete">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Calendar View -->
      <div v-if="viewMode === 'calendar'" class="calendar-container">
        <div class="calendar-header">
          <button @click="previousMonth" class="nav-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <h4>{{ currentMonthYear }}</h4>
          <button @click="nextMonth" class="nav-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
        <div class="calendar-grid">
          <div class="calendar-day-header" v-for="day in dayHeaders" :key="day">{{ day }}</div>
          <div 
            v-for="date in calendarDates" 
            :key="date.date" 
            class="calendar-day" 
            :class="{ 
              'other-month': !date.currentMonth,
              'today': date.isToday,
              'has-meetings': date.meetings.length > 0
            }"
          >
            <div class="day-number">{{ date.day }}</div>
            <div class="day-meetings">
              <div 
                v-for="meeting in date.meetings.slice(0, 3)" 
                :key="meeting.id"
                class="meeting-dot"
                :class="meeting.type"
                :title="`${meeting.time} - ${meeting.student.name} (${meeting.type})`"
              ></div>
              <div v-if="date.meetings.length > 3" class="more-meetings">+{{ date.meetings.length - 3 }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="viewMode === 'table'" class="pagination">
        <div class="pagination-info">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredMeetings.length) }} of {{ filteredMeetings.length }} meetings
        </div>
        <div class="pagination-controls">
          <button @click="previousPage" :disabled="currentPage === 1" class="pagination-btn">Previous</button>
          <span class="page-numbers">
            <button 
              v-for="page in pageNumbers" 
              :key="page"
              @click="goToPage(page)"
              :class="{ active: page === currentPage }"
              class="page-btn"
            >
              {{ page }}
            </button>
          </span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="pagination-btn">Next</button>
        </div>
      </div>
    </div>

    <!-- Schedule Meeting Modal -->
    <div v-if="showScheduleModal" class="modal-overlay" @click="closeScheduleModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h4>{{ editingMeeting ? 'Edit Meeting' : 'Schedule New Meeting' }}</h4>
          <button @click="closeScheduleModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveMeeting">            <div class="form-group">
              <label>Student</label>
              <select v-model="meetingForm.studentId" required class="form-control">
                <option value="">Select Student</option>
                <option v-for="student in advisees" :key="student.studentId" :value="student.studentId">
                  {{ student.name }} ({{ student.studentId }})
                </option>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Date</label>
                <input v-model="meetingForm.date" type="date" required class="form-control">
              </div>
              <div class="form-group">
                <label>Time</label>
                <input v-model="meetingForm.time" type="time" required class="form-control">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Duration (minutes)</label>
                <input v-model="meetingForm.duration" type="number" min="15" max="180" class="form-control">
              </div>
              <div class="form-group">
                <label>Meeting Type</label>
                <select v-model="meetingForm.type" required class="form-control">
                  <option value="academic">Academic Progress</option>
                  <option value="career">Career Guidance</option>
                  <option value="personal">Personal Support</option>
                  <option value="crisis">Crisis Intervention</option>
                  <option value="routine">Routine Check-in</option>
                </select>
              </div>
            </div>            <div class="form-group">
              <label>Meeting Location</label>
              <input v-model="meetingForm.location" type="text" placeholder="Office, Online, etc." class="form-control">
            </div>
            <div class="form-group">
              <label>Meeting Link <small class="text-muted">(Optional - Zoom, Teams, etc.)</small></label>
              <input v-model="meetingForm.meetingLink" type="url" placeholder="https://zoom.us/j/..." class="form-control">
              <small class="text-muted">Paste the meeting link for virtual meetings</small>
            </div>
            <div class="form-group">
              <label>Agenda/Purpose</label>
              <textarea v-model="meetingForm.agenda" rows="3" placeholder="Meeting agenda and purpose..." class="form-control"></textarea>
            </div>
            <div class="form-group" v-if="editingMeeting">
              <label>Meeting Notes</label>
              <textarea v-model="meetingForm.notes" rows="5" placeholder="Meeting notes and outcomes..." class="form-control"></textarea>
            </div>
            <div class="form-group" v-if="editingMeeting">
              <label>Action Items</label>
              <textarea v-model="meetingForm.actionItems" rows="3" placeholder="Action items and follow-ups..." class="form-control"></textarea>
            </div>
            <div class="form-group" v-if="editingMeeting">
              <label>Status</label>
              <select v-model="meetingForm.status" class="form-control">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
                <option value="rescheduled">Rescheduled</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" @click="closeScheduleModal" class="cancel-btn">Cancel</button>
              <button type="submit" class="save-btn">{{ editingMeeting ? 'Save Changes' : 'Schedule Meeting' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Notes Modal -->
    <div v-if="showNotesModal" class="modal-overlay" @click="closeNotesModal">
      <div class="modal large" @click.stop>
        <div class="modal-header">
          <h4>Meeting Notes - {{ selectedMeeting?.student?.name }}</h4>
          <button @click="closeNotesModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="meeting-details">
            <div class="detail-row">
              <span class="detail-label">Date:</span>
              <span>{{ formatDate(selectedMeeting?.date) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Time:</span>
              <span>{{ selectedMeeting?.time }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Duration:</span>
              <span>{{ selectedMeeting?.duration }} minutes</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Type:</span>
              <span class="meeting-type-badge" :class="selectedMeeting?.type">{{ formatMeetingType(selectedMeeting?.type) }}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Status:</span>
              <span class="status-badge" :class="selectedMeeting?.status">{{ selectedMeeting?.status }}</span>
            </div>
          </div>
          <div class="notes-section">
            <h5>Agenda</h5>
            <p>{{ selectedMeeting?.agenda || 'No agenda specified' }}</p>
            
            <h5>Meeting Notes</h5>
            <p>{{ selectedMeeting?.notes || 'No notes available' }}</p>
            
            <h5>Action Items</h5>            <p>{{ selectedMeeting?.actionItems || 'No action items specified' }}</p>
          </div>        </div>
      </div>
    </div>

    <!-- Add/Edit Notes Modal -->
    <div v-if="showAddNotesModal" class="modal-overlay" @click="closeAddNotesModal">
      <div class="modal large" @click.stop>
        <div class="modal-header">
          <h4>{{ selectedMeetingForNotes ? 'Add Meeting Notes' : 'Add Notes' }} - {{ selectedMeetingForNotes?.student?.name }}</h4>
          <button @click="closeAddNotesModal" class="close-btn">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>        <div class="modal-body">
          <div class="meeting-info" v-if="selectedMeetingForNotes && selectedMeetingForNotes.student.name">
            <div class="info-row">
              <span class="info-label">Meeting Date:</span>
              <span>{{ formatDate(selectedMeetingForNotes.date) }} at {{ selectedMeetingForNotes.time }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Type:</span>
              <span class="meeting-type-badge" :class="selectedMeetingForNotes.type">{{ formatMeetingType(selectedMeetingForNotes.type) }}</span>
            </div>
            <div class="info-row" v-if="selectedMeetingForNotes.agenda">
              <span class="info-label">Agenda:</span>
              <span>{{ selectedMeetingForNotes.agenda }}</span>
            </div>
          </div>
          
          <form @submit.prevent="saveNotes">
            <!-- Student selector for quick notes -->
            <div class="form-group" v-if="!selectedMeetingForNotes.student.name">
              <label>Select Student</label>
              <select v-model="notesForm.selectedStudentId" required class="form-control">
                <option value="">Choose a student...</option>
                <option v-for="student in advisees" :key="student.studentId" :value="student.studentId">
                  {{ student.name }} ({{ student.studentId }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Meeting Notes</label>
              <textarea 
                v-model="notesForm.notes" 
                rows="8" 
                placeholder="Enter detailed meeting notes here..."
                class="form-control"
                required
              ></textarea>
              <small class="form-hint">Record key discussion points, student concerns, and observations</small>
            </div>
            
            <div class="form-group">
              <label>Action Items & Follow-up</label>
              <textarea 
                v-model="notesForm.actionItems" 
                rows="4" 
                placeholder="List action items and follow-up tasks..."
                class="form-control"
              ></textarea>
              <small class="form-hint">What needs to be done next? Any referrals or resources to provide?</small>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label>Meeting Duration (minutes)</label>
                <input 
                  v-model="notesForm.duration" 
                  type="number" 
                  min="5" 
                  max="180" 
                  class="form-control"
                  placeholder="60"
                >
              </div>
              <div class="form-group">
                <label>Next Meeting Date (optional)</label>
                <input 
                  v-model="notesForm.nextMeetingDate" 
                  type="date" 
                  class="form-control"
                >
              </div>
            </div>
            
            <div class="form-group">
              <label>
                <input 
                  v-model="notesForm.markAsCompleted" 
                  type="checkbox" 
                  class="checkbox"
                > 
                Mark this meeting as completed
              </label>
            </div>
            
            <div class="form-group">
              <label>
                <input 
                  v-model="notesForm.saveToSystem" 
                  type="checkbox" 
                  class="checkbox"
                  checked
                > 
                Save notes to student's permanent record
              </label>
              <small class="form-hint">This will add the notes to the student's advisor notes in the system</small>
            </div>
            
            <div class="form-actions">
              <button type="button" @click="closeAddNotesModal" class="cancel-btn">Cancel</button>
              <button type="submit" class="save-btn" :disabled="!notesForm.notes.trim()">
                {{ notesForm.saveToSystem ? 'Save Notes & Update Record' : 'Save Notes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div> <!-- End main-content -->
  </div>
</template>

<script>
import { advisorService } from '../../services/advisor.js';

export default {
  name: 'MeetingNotes',
  data() {
    return {
      currentUser: JSON.parse(localStorage.getItem('user') || '{}'),
      advisees: [],
      meetings: [],
      loading: false,
      error: null,
      searchQuery: '',
      statusFilter: '',
      typeFilter: '',
      dateFilter: '',
      sortColumn: 'date',
      sortDirection: 'desc',
      currentPage: 1,
      itemsPerPage: 10,
      selectAll: false,
      selectedMeetings: [],
      viewMode: 'table',      showScheduleModal: false,
      showNotesModal: false,
      showAddNotesModal: false,
      editingMeeting: null,
      selectedMeeting: null,      selectedMeetingForNotes: null,
      currentDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1), // First day of current month
        meetingForm: {
        studentId: '',
        date: '',
        time: '',
        duration: 60,
        type: 'academic',
        location: '',
        meetingLink: '',
        agenda: '',
        notes: '',
        actionItems: '',
        status: 'scheduled'
      },notesForm: {
        notes: '',
        actionItems: '',
        duration: '',
        nextMeetingDate: '',
        markAsCompleted: false,
        saveToSystem: true,
        selectedStudentId: ''
      },

      dayHeaders: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    }
  },
  async mounted() {
    await this.fetchAdvisees();
    await this.loadMeetingsFromBackend();
  },

  computed: {
    filteredMeetings() {
      let filtered = this.meetings;

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(meeting => 
          meeting.student.name.toLowerCase().includes(query) ||
          meeting.notes.toLowerCase().includes(query) ||
          meeting.agenda.toLowerCase().includes(query) ||
          meeting.type.toLowerCase().includes(query)
        );
      }

      if (this.statusFilter) {
        filtered = filtered.filter(meeting => meeting.status === this.statusFilter);
      }

      if (this.typeFilter) {
        filtered = filtered.filter(meeting => meeting.type === this.typeFilter);
      }

      if (this.dateFilter) {
        filtered = filtered.filter(meeting => meeting.date === this.dateFilter);
      }

      // Sort
      filtered.sort((a, b) => {
        let aValue = a[this.sortColumn];
        let bValue = b[this.sortColumn];

        if (this.sortColumn === 'student') {
          aValue = a.student.name;
          bValue = b.student.name;
        }

        if (this.sortDirection === 'asc') {
          return aValue > bValue ? 1 : -1;
        } else {
          return aValue < bValue ? 1 : -1;
        }
      });

      return filtered;
    },

    paginatedMeetings() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredMeetings.slice(start, end);
    },

    totalPages() {
      return Math.ceil(this.filteredMeetings.length / this.itemsPerPage);
    },

    pageNumbers() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },

    upcomingMeetings() {
      const today = new Date().toISOString().split('T')[0];
      return this.meetings.filter(meeting => 
        meeting.date >= today && meeting.status === 'scheduled'
      );
    },

    completedMeetings() {
      const thisMonth = new Date().toISOString().slice(0, 7);
      return this.meetings.filter(meeting => 
        meeting.date.startsWith(thisMonth) && meeting.status === 'completed'
      );
    },

    urgentMeetings() {
      return this.meetings.filter(meeting => 
        meeting.type === 'crisis' || meeting.type === 'academic'
      );
    },

    newMeetingsThisWeek() {
      const today = new Date();
      const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
      return this.meetings.filter(meeting => {
        const meetingDate = new Date(meeting.date);
        return meetingDate >= weekAgo && meetingDate <= today;
      }).length;
    },

    completionRate() {
      const total = this.meetings.length;
      if (total === 0) return 0;
      const completed = this.meetings.filter(m => m.status === 'completed').length;
      return Math.round((completed / total) * 100);
    },

    averageMeetingDuration() {
      const completed = this.meetings.filter(m => m.duration);
      if (completed.length === 0) return 0;
      const total = completed.reduce((sum, m) => sum + m.duration, 0);
      return Math.round(total / completed.length);
    },

    currentMonthYear() {
      return this.currentDate.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
      });
    },    calendarDates() {
      const dates = [];
      const currentYear = this.currentDate.getFullYear();
      const currentMonth = this.currentDate.getMonth();
      const firstDay = new Date(currentYear, currentMonth, 1);
      const startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - firstDay.getDay());

      // Cache today's date string to avoid creating new Date objects in the loop
      const todayString = new Date().toISOString().split('T')[0];

      for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dateString = date.toISOString().split('T')[0];
        const meetingsOnDate = this.meetings.filter(m => m.date === dateString);
        
        dates.push({
          date: dateString,
          day: date.getDate(),
          currentMonth: date.getMonth() === currentMonth,
          isToday: dateString === todayString,
          meetings: meetingsOnDate
        });
      }

      return dates;
    }
  },

  methods: {
    // Fetch advisees using existing service
    async fetchAdvisees() {
      if (!this.currentUser || !this.currentUser.id) {
        this.error = 'User not authenticated';
        return;
      }

      this.loading = true;
      this.error = null;
      
      try {
        const response = await advisorService.getAdvisees(this.currentUser.id);
        
        if (response.success) {
          this.advisees = response.data || [];
        } else {
          this.error = response.message || 'Failed to fetch advisees';
        }
      } catch (error) {
        console.error('Error fetching advisees:', error);
        this.error = 'Failed to load advisee data. Please try again.';
      } finally {
        this.loading = false;
      }
    },    // Load meetings from backend instead of localStorage
    async loadMeetingsFromBackend() {
      try {
        console.log('Loading meetings from backend for advisor:', this.currentUser.id);
        const response = await advisorService.getMeetings(this.currentUser.id);
        
        if (response && response.success && response.data) {
          this.meetings = response.data;
          console.log('Loaded meetings from backend:', this.meetings);
        } else {
          console.log('No meetings found or API returned unsuccessful response');
          this.meetings = [];
        }
      } catch (error) {
        console.error('Error loading meetings from backend:', error);
        // Fallback to empty array if backend fails
        this.meetings = [];
      }
    },

    // Load meetings from localStorage (simple storage) - kept for backward compatibility
    loadMeetingsFromStorage() {
      const storedMeetings = localStorage.getItem('advisor_meetings');
      if (storedMeetings) {
        this.meetings = JSON.parse(storedMeetings);
      }
    },

    // Save meetings to localStorage
    saveMeetingsToStorage() {
      localStorage.setItem('advisor_meetings', JSON.stringify(this.meetings));
    },

    // Search and Filter
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = '';
      this.typeFilter = '';
      this.dateFilter = '';
    },

    // Sorting
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortDirection = 'asc';
      }
    },

    // Pagination
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },

    goToPage(page) {
      this.currentPage = page;
    },

    // Selection
    toggleAllSelection() {
      if (this.selectAll) {
        this.selectedMeetings = this.paginatedMeetings.map(m => m.id);
      } else {
        this.selectedMeetings = [];
      }
    },    // Meeting Actions
    addNotes(meeting) {
      this.selectedMeetingForNotes = meeting;
      this.notesForm = {
        notes: meeting.notes || '',
        actionItems: meeting.actionItems || '',
        duration: meeting.duration || '',
        nextMeetingDate: '',
        markAsCompleted: meeting.status !== 'completed',
        saveToSystem: true
      };
      this.showAddNotesModal = true;
    },

    editMeeting(meeting) {
      this.editingMeeting = meeting;
      this.meetingForm = {
        studentId: meeting.student.id,
        date: meeting.date,
        time: meeting.time,
        duration: meeting.duration,
        type: meeting.type,
        location: meeting.location,
        agenda: meeting.agenda,
        notes: meeting.notes,
        actionItems: meeting.actionItems,
        status: meeting.status
      };
      this.showScheduleModal = true;
    },

    viewNotes(meeting) {
      this.selectedMeeting = meeting;
      this.showNotesModal = true;
    },    async markComplete(meeting) {
      try {
        meeting.status = 'completed';
          // Find the student
        const student = this.advisees.find(s => s.studentId === meeting.student.id);
        if (student) {
          // Update meeting status in backend
          await advisorService.updateMeetingStatus(
            this.currentUser.id,
            student.id, // Use numeric ID
            meeting
          );
        }
        
        this.saveMeetingsToStorage();
        alert('Meeting marked as completed!');
      } catch (error) {
        console.error('Error marking meeting as complete:', error);
        alert('Failed to update meeting status. Please try again.');
        // Revert the status change on error
        meeting.status = 'scheduled';
      }
    },    reschedule(meeting) {
      this.editMeeting(meeting);
    },    async deleteMeeting(meeting) {
      if (confirm('Are you sure you want to delete this meeting?')) {
        try {
          // Delete meeting from backend using the meetings API
          if (meeting.id) {
            await advisorService.deleteMeeting(this.currentUser.id, meeting.id);
          }

          // Reload meetings from backend to get updated list
          await this.loadMeetingsFromBackend();
          
          alert('Meeting deleted successfully!');
        } catch (error) {
          console.error('Error deleting meeting:', error);
          alert('Failed to delete meeting. Please try again.');
        }
      }
    },

    // Modal Actions
    closeScheduleModal() {
      this.showScheduleModal = false;
      this.editingMeeting = null;
      this.resetMeetingForm();
    },    closeNotesModal() {
      this.showNotesModal = false;
      this.selectedMeeting = null;
    },

    closeAddNotesModal() {
      this.showAddNotesModal = false;
      this.selectedMeetingForNotes = null;
      this.resetNotesForm();
    },    resetNotesForm() {
      this.notesForm = {
        notes: '',
        actionItems: '',
        duration: '',
        nextMeetingDate: '',
        markAsCompleted: false,
        saveToSystem: true,
        selectedStudentId: ''
      };
    },    resetMeetingForm() {
      this.meetingForm = {
        studentId: '',
        date: '',
        time: '',
        duration: 60,
        type: 'academic',
        location: '',
        meetingLink: '',
        agenda: '',
        notes: '',
        actionItems: '',
        status: 'scheduled'
      };
    },async saveNotes() {
      try {
        const meeting = this.selectedMeetingForNotes;
        let student;

        // Handle quick notes vs regular meeting notes
        if (meeting.student && meeting.student.name) {
          // Regular meeting notes - find student by student object
          student = this.advisees.find(s => s.id === meeting.student.id);
        } else {
          // Quick notes - find student by selected ID
          student = this.advisees.find(s => s.studentId === this.notesForm.selectedStudentId);
          if (student) {
            // Update the temporary meeting with student info
            meeting.student = {
              id: student.id, 
              name: student.name,
              avatar: this.getInitials(student.name)
            };
          }
        }
        
        if (!student) {
          alert('Please select a valid student');
          return;
        }        // Update existing meeting or create new one for quick notes
        if (meeting.id.toString().startsWith('quick-note-')) {
          // This is a quick note - create a new meeting record
          const meetingData = {
            title: 'Advisor Notes',
            date: meeting.date,
            time: meeting.time,
            duration: parseInt(this.notesForm.duration) || 30,
            location: 'Advisor Office',
            meetingLink: '',
            type: 'routine',
            status: 'completed',
            agenda: 'Advisor Note',
            notes: this.notesForm.notes,
            actionItems: this.notesForm.actionItems
          };

          await advisorService.scheduleMeeting(
            this.currentUser.id,
            student.id,
            meetingData
          );
        } else {
          // Update existing meeting with notes
          await advisorService.updateMeeting(
            this.currentUser.id,
            meeting.id,
            {
              notes: this.notesForm.notes,
              action_items: this.notesForm.actionItems,
              duration: parseInt(this.notesForm.duration) || meeting.duration,
              status: this.notesForm.markAsCompleted ? 'completed' : meeting.status
            }
          );
        }

        // Schedule next meeting if date provided
        if (this.notesForm.nextMeetingDate) {          const nextMeetingData = {
            title: 'Follow-up Meeting',
            date: this.notesForm.nextMeetingDate,
            time: '10:00', // Default time
            duration: 60,
            location: '',
            meetingLink: '',
            type: meeting.type || 'routine',
            status: 'scheduled',
            agenda: 'Follow-up discussion'
          };

          await advisorService.scheduleMeeting(
            this.currentUser.id,
            student.id,
            nextMeetingData
          );
        }        // Reload meetings from backend to get updated data
        await this.loadMeetingsFromBackend();

        // Close modal and reset form
        this.closeNotesModal();
        alert('Notes saved successfully!');
      } catch (error) {
        console.error('Error saving notes:', error);
        alert('Failed to save notes. Please try again.');
      }
    },async saveMeeting() {
      try {
        const student = this.advisees.find(s => s.studentId === this.meetingForm.studentId);
        
        console.log('Current user:', this.currentUser);
        console.log('Selected student:', student);
        console.log('All advisees:', this.advisees);
        
        if (!student) {
          alert('Please select a valid student');
          return;
        }        if (this.editingMeeting) {
          // Update existing meeting
          const index = this.meetings.findIndex(m => m.id === this.editingMeeting.id);
          if (index > -1) {
            this.meetings[index] = {
              ...this.editingMeeting,
              student: { 
                id: student.id, // Use numeric ID for consistency 
                name: student.name,
                avatar: this.getInitials(student.name)
              },
              ...this.meetingForm
            };

            // Update meeting in backend using the new meetings API
            await advisorService.updateMeeting(
              this.currentUser.id,
              this.editingMeeting.id, // Use meeting ID, not student ID
              {
                ...this.meetingForm,
                student_id: student.id
              }
            );
          }        } else {
          // Create new meeting using the new meetings API
          const newMeetingData = {
            title: this.meetingForm.title || 'Advisor Meeting',
            date: this.meetingForm.date,
            time: this.meetingForm.time,
            duration: this.meetingForm.duration || 60,
            location: this.meetingForm.location || '',
            meetingLink: this.meetingForm.meetingLink || '',
            type: this.meetingForm.type,
            status: this.meetingForm.status || 'scheduled',
            agenda: this.meetingForm.agenda || '',
            notes: this.meetingForm.notes || '',
            actionItems: this.meetingForm.actionItems || ''
          };

          console.log('Calling scheduleMeeting with:', {
            advisorId: this.currentUser.id,
            studentId: student.id,
            meetingData: newMeetingData
          });
          
          const response = await advisorService.scheduleMeeting(
            this.currentUser.id,
            student.id,
            newMeetingData
          );

          // If successful, reload meetings from backend to get the latest data
          if (response && response.success) {
            await this.loadMeetingsFromBackend();
          }
        }

        // No longer need localStorage storage, everything is in the backend
        this.closeScheduleModal();
        
        // Show success message
        alert('Meeting saved successfully!');
      } catch (error) {
        console.error('Error saving meeting:', error);
        
        // Show more detailed error message
        let errorMessage = 'Failed to save meeting. ';
        if (error.response && error.response.status === 403) {
          errorMessage += 'You may not have permission to add notes for this student. Please check if you are assigned as their advisor.';
        } else {
          errorMessage += 'Please try again.';
        }
        
        alert(errorMessage);
      }
    },

    // Calendar Navigation
    previousMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
    },

    nextMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
    },    // Export
    exportMeetings() {
      const csvContent = [
        ['Student', 'Date', 'Time', 'Type', 'Status', 'Duration', 'Notes'].join(','),
        ...this.meetings.map(meeting => [
          `"${meeting.student.name}"`,
          meeting.date,
          meeting.time,
          meeting.type,
          meeting.status,
          meeting.duration || '',
          `"${meeting.notes || ''}"`
        ].join(','))
      ].join('\n');

      const blob = new Blob([csvContent], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'meetings.csv';
      a.click();
      window.URL.revokeObjectURL(url);
    },

    // Quick Notes Modal for adding notes without a scheduled meeting
    showQuickNotesModal() {
      // Create a temporary meeting for quick notes
      this.selectedMeetingForNotes = {
        id: 'quick-note-' + Date.now(),
        student: { id: '', name: '' },
        date: new Date().toISOString().split('T')[0],
        time: new Date().toTimeString().split(':').slice(0,2).join(':'),
        type: 'routine',
        status: 'completed',
        location: '',
        agenda: 'Quick advisor note',
        notes: '',
        actionItems: ''
      };
      
      this.notesForm = {
        notes: '',
        actionItems: '',
        duration: '',
        nextMeetingDate: '',
        markAsCompleted: false,
        saveToSystem: true
      };
      
      this.showAddNotesModal = true;
    },

    // Utility Functions
    getInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    formatMeetingType(type) {
      const types = {
        academic: 'Academic Progress',
        career: 'Career Guidance',
        personal: 'Personal Support',
        crisis: 'Crisis Intervention',
        routine: 'Routine Check-in'
      };
      return types[type] || type;
    },

    truncateNotes(notes) {
      if (!notes) return 'No notes';
      return notes.length > 100 ? notes.substring(0, 100) + '...' : notes;
    }
  }
}
</script>

<style scoped>
.meeting-notes {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Loading State */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #457B9D;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-container p {
  color: #6b7280;
  font-size: 16px;
  margin: 0;
}

/* Error State */
.error-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  padding: 40px;
  text-align: center;
  margin-bottom: 24px;
}

.error-message {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #dc2626;
  font-size: 16px;
  margin-bottom: 20px;
}

.error-icon {
  width: 24px;
  height: 24px;
  color: #dc2626;
}

.retry-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #457B9D;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
  margin: 0 auto;
}

.retry-btn:hover {
  background: #1D3557;
}

.retry-btn svg {
  width: 16px;
  height: 16px;
}

/* Dashboard Header - Following lecturer pattern */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-content h2 {
  color: #1D3557;
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.welcome-text {
  color: #6c757d;
  font-size: 16px;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.primary {
  background: #457B9D;
  color: white;
}

.action-btn.primary:hover {
  background: #1D3557;
  transform: translateY(-1px);
}

.action-btn.secondary {
  background: #F1FAEE;
  color: #1D3557;
}

.action-btn.secondary:hover {
  background: #A8DADC;
  transform: translateY(-1px);
}

.action-btn.tertiary {
  background: #E9C46A;
  color: #1D3557;
}

.action-btn.tertiary:hover {
  background: #F4A261;
  transform: translateY(-1px);
}

.btn-icon {
  width: 16px;
  height: 16px;
}

/* Card Styles */
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

/* Filters Section */
.filters-section .card {
  padding: 20px;
}

.filter-row {
  display: flex;
  gap: 16px;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #9ca3af;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.search-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-group {
  display: flex;
  gap: 12px;
  align-items: center;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  outline: none;
  cursor: pointer;
}

.filter-select:focus {
  border-color: #3b82f6;
}

.clear-filter-btn {
  padding: 8px 16px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.clear-filter-btn:hover {
  background: #dc2626;
}

/* Stats Section */
.stats-section {
  margin-bottom: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-number {
  font-size: 36px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 4px;
}

.stat-trend {
  font-size: 12px;
  font-weight: 500;
}

.stat-trend.positive {
  color: #10b981;
}

.stat-trend.negative {
  color: #ef4444;
}

.stat-trend.neutral {
  color: #6b7280;
}

/* Table Styles */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 20px 0 20px;
}

.table-header h4 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.view-toggle {
  display: flex;
  background: #f3f4f6;
  border-radius: 8px;
  padding: 2px;
}

.toggle-btn {
  padding: 8px 16px;
  border: none;
  background: none;
  color: #6b7280;
  font-size: 14px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.toggle-btn.active {
  background: white;
  color: #3b82f6;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.table-container {
  padding: 20px;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.data-table th {
  text-align: left;
  padding: 12px;
  color: #374151;
  font-weight: 600;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
}

.data-table th.sortable:hover {
  background: #f9fafb;
}

.sort-icon {
  width: 16px;
  height: 16px;
  margin-left: 4px;
  color: #9ca3af;
  transition: color 0.2s;
}

.sort-icon.active {
  color: #3b82f6;
}

.data-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #f3f4f6;
}

.table-row:hover {
  background: #f9fafb;
}

.student-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
}

.student-name {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 2px;
}

.student-id {
  font-size: 12px;
  color: #6b7280;
}

.meeting-datetime {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meeting-date {
  font-weight: 500;
  color: #1f2937;
}

.meeting-time {
  font-size: 12px;
  color: #6b7280;
}

.meeting-type-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.meeting-type-badge.academic {
  background: #dbeafe;
  color: #1d4ed8;
}

.meeting-type-badge.career {
  background: #d1fae5;
  color: #047857;
}

.meeting-type-badge.personal {
  background: #fef3c7;
  color: #b45309;
}

.meeting-type-badge.crisis {
  background: #fee2e2;
  color: #dc2626;
}

.meeting-type-badge.routine {
  background: #f3f4f6;
  color: #374151;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  text-transform: capitalize;
}

.status-badge.scheduled {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-badge.completed {
  background: #d1fae5;
  color: #047857;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.status-badge.rescheduled {
  background: #fef3c7;
  color: #b45309;
}

.notes-preview {
  max-width: 200px;
  color: #6b7280;
  font-size: 13px;
}

.action-buttons {
  display: flex;
  gap: 4px;
}

.action-btn {
  padding: 6px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn svg {
  width: 16px;
  height: 16px;
}

.notes-btn {
  background: #fef3c7;
  color: #b45309;
}

.notes-btn:hover {
  background: #fde68a;
}

.edit-btn {
  background: #f3f4f6;
  color: #374151;
}

.edit-btn:hover {
  background: #e5e7eb;
}

.view-btn {
  background: #dbeafe;
  color: #1d4ed8;
}

.view-btn:hover {
  background: #bfdbfe;
}

.complete-btn {
  background: #d1fae5;
  color: #047857;
}

.complete-btn:hover {
  background: #a7f3d0;
}

.reschedule-btn {
  background: #fef3c7;
  color: #b45309;
}

.reschedule-btn:hover {
  background: #fde68a;
}

.delete-btn {
  background: #fee2e2;
  color: #dc2626;
}

.delete-btn:hover {
  background: #fecaca;
}

/* Calendar Styles */
.calendar-container {
  padding: 20px;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-header h4 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.nav-btn {
  padding: 8px;
  border: none;
  background: #f3f4f6;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.2s;
}

.nav-btn:hover {
  background: #e5e7eb;
}

.nav-btn svg {
  width: 16px;
  height: 16px;
  color: #374151;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background: #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.calendar-day-header {
  background: #f9fafb;
  padding: 12px;
  text-align: center;
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.calendar-day {
  background: white;
  min-height: 80px;
  padding: 8px;
  position: relative;
}

.calendar-day.other-month {
  background: #f9fafb;
  color: #9ca3af;
}

.calendar-day.today {
  background: #eff6ff;
}

.calendar-day.has-meetings {
  background: #fef7cd;
}

.day-number {
  font-weight: 500;
  margin-bottom: 4px;
}

.day-meetings {
  display: flex;
  flex-wrap: wrap;
  gap: 2px;
}

.meeting-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  cursor: pointer;
}

.meeting-dot.academic {
  background: #3b82f6;
}

.meeting-dot.career {
  background: #10b981;
}

.meeting-dot.personal {
  background: #f59e0b;
}

.meeting-dot.crisis {
  background: #ef4444;
}

.meeting-dot.routine {
  background: #6b7280;
}

.more-meetings {
  font-size: 10px;
  color: #6b7280;
  margin-left: 2px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-top: 1px solid #f3f4f6;
}

.pagination-info {
  color: #6b7280;
  font-size: 14px;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pagination-btn {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 4px;
}

.page-btn {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.page-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal.large {
  max-width: 800px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h4 {
  color: #1f2937;
  font-size: 18px;
  font-weight: 600;
  margin: 0;
}

.close-btn {
  padding: 8px;
  border: none;
  background: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: 20px;
  max-height: calc(90vh - 140px);
  overflow-y: auto;
}

/* Form Styles */
.form-group {
  margin-bottom: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  color: #374151;
  font-weight: 500;
  font-size: 14px;
}

.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
}

.form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.form-hint {
  color: #6b7280;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

.checkbox {
  margin-right: 8px;
  width: auto;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.cancel-btn {
  padding: 10px 20px;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.save-btn {
  padding: 10px 20px;
  border: none;
  background: #3b82f6;
  color: white;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.save-btn:hover {
  background: #2563eb;
}

/* Meeting Details */
.meeting-info {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.info-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  gap: 12px;
}

.info-label {
  font-weight: 500;
  color: #374151;
  width: 120px;
  flex-shrink: 0;
}

.meeting-details {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.detail-row {
  display: flex;
  margin-bottom: 8px;
}

.detail-label {
  font-weight: 500;
  color: #374151;
  width: 120px;
  flex-shrink: 0;
}

.notes-section h5 {
  color: #1f2937;
  font-size: 16px;
  font-weight: 600;
  margin: 20px 0 8px 0;
}

.notes-section p {
  color: #6b7280;
  line-height: 1.6;
  margin: 0 0 16px 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .meeting-notes {
    padding: 16px;
  }

  .header-section {
    flex-direction: column;
    gap: 16px;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .filter-row {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-group {
    justify-content: stretch;
  }

  .filter-select {
    flex: 1;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .table-container {
    overflow-x: scroll;
  }
  .data-table {
    min-width: 800px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .modal {
    width: 95%;
    margin: 20px;
  }
}

/* Meeting link styling */
.location-link-info {
  font-size: 0.9rem;
}

.location {
  color: #666;
  margin-bottom: 4px;
}

.meeting-link {
  margin-top: 4px;
}

.link-btn {
  display: inline-block;
  padding: 4px 8px;
  background: #e3f2fd;
  color: #1976d2;
  text-decoration: none;
  border-radius: 4px;
  font-size: 0.8rem;
  transition: all 0.2s;
}

.link-btn:hover {
  background: #bbdefb;
  text-decoration: none;
  color: #0d47a1;
}

.no-location {
  color: #999;
  font-style: italic;
}
</style>
