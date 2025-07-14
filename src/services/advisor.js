import api from '../api.js';

export const advisorService = {
  // Get all advisees for a specific advisor
  async getAdvisees(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees`);
      return response.data;
    } catch (error) {
      console.error('Error fetching advisees:', error);
      throw error;
    }
  },

  // Add a note for an advisee (using correct backend endpoint)
  async addAdviseeNote(advisorId, studentId, note, courseId = 1) {
    try {
      const response = await api.post('/advisor-notes', {
        advisor_id: advisorId,
        student_id: studentId,
        course_id: courseId,
        note: note
      });
      return response.data;
    } catch (error) {
      console.error('Error adding advisee note:', error);
      throw error;
    }
  },

  // Update an advisor note
  async updateAdviseeNote(noteId, noteContent) {
    try {
      const response = await api.put(`/advisor-notes/${noteId}`, {
        note: noteContent
      });
      return response.data;
    } catch (error) {
      console.error('Error updating advisee note:', error);
      throw error;
    }
  },

  // Delete an advisor note
  async deleteAdviseeNote(noteId) {
    try {
      const response = await api.delete(`/advisor-notes/${noteId}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting advisee note:', error);
      throw error;
    }
  },

  // === NEW MEETING FUNCTIONALITY ===

  // Schedule a meeting using the dedicated meetings endpoint
  async scheduleMeeting(advisorId, studentId, meetingData) {
    try {
      console.log('Scheduling meeting:', { advisorId, studentId, meetingData });
        const response = await api.post(`/advisors/${advisorId}/meetings`, {
        student_id: studentId,
        title: meetingData.title || 'Advisor Meeting',
        meeting_date: meetingData.date,
        meeting_time: meetingData.time,
        duration: meetingData.duration || 60,
        location: meetingData.location || '',
        meeting_link: meetingData.meetingLink || '',
        meeting_type: meetingData.type,
        status: meetingData.status || 'scheduled',
        agenda: meetingData.agenda || '',
        notes: meetingData.notes || '',
        action_items: meetingData.actionItems || ''
      });
      return response.data;
    } catch (error) {
      console.error('Error scheduling meeting:', error);
      
      // More detailed error logging
      if (error.response) {
        console.error('Response status:', error.response.status);
        console.error('Response data:', error.response.data);
      }
      
      throw error;
    }
  },

  // Update meeting status and add notes
  async updateMeeting(advisorId, meetingId, meetingData) {
    try {
      const response = await api.put(`/meetings/${meetingId}`, {
        student_id: meetingData.student_id,
        title: meetingData.title,
        meeting_date: meetingData.meeting_date,
        meeting_time: meetingData.meeting_time,
        duration: meetingData.duration,
        location: meetingData.location,
        meeting_link: meetingData.meeting_link,
        meeting_type: meetingData.meeting_type,
        status: meetingData.status,
        agenda: meetingData.agenda,
        notes: meetingData.notes,
        action_items: meetingData.action_items
      });
      return response.data;
    } catch (error) {
      console.error('Error updating meeting:', error);
      throw error;
    }
  },

  // Get all meetings for an advisor
  async getMeetings(advisorId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/meetings`);
      return response.data;
    } catch (error) {
      console.error('Error fetching meetings:', error);
      throw error;
    }
  },

  // Get meetings for a specific student
  async getStudentMeetings(advisorId, studentId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/meetings?student_id=${studentId}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching student meetings:', error);
      throw error;
    }
  },

  // Delete a meeting
  async deleteMeeting(advisorId, meetingId) {
    try {
      const response = await api.delete(`/meetings/${meetingId}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting meeting:', error);
      throw error;
    }
  },

  // Complete a meeting (shortcut for updating status to completed)
  async completeMeeting(advisorId, meetingId, notes, actionItems) {
    try {
      return await this.updateMeeting(advisorId, meetingId, {
        status: 'completed',
        notes: notes,
        action_items: actionItems
      });
    } catch (error) {
      console.error('Error completing meeting:', error);
      throw error;
    }
  },

  // === BACKWARD COMPATIBILITY ===
  // Keep these methods for any existing code that might use them
  async updateMeetingStatus(advisorId, meetingId, meetingData) {
    return this.updateMeeting(advisorId, meetingId, meetingData);
  },

  async addMeetingNote(advisorId, meetingId, noteData) {
    return this.updateMeeting(advisorId, meetingId, {
      notes: noteData.notes,
      action_items: noteData.actionItems,
      status: 'completed',
      next_meeting_date: noteData.nextMeetingDate
    });
  },

  // Get meeting history/notes for an advisee (using correct backend endpoint)
  async getAdviseeNotes(advisorId, studentId, courseId = 1) {
    try {
      const response = await api.get(`/advisor-notes/${advisorId}/${studentId}/${courseId}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching advisee notes:', error);
      throw error;
    }
  },

  // Get detailed performance data for a specific advisee
  async getAdviseePerformance(advisorId, studentId) {
    try {
      const response = await api.get(`/advisors/${advisorId}/advisees/${studentId}/performance`);
      return response.data;
    } catch (error) {
      console.error('Error fetching advisee performance:', error);
      throw error;
    }
  },

  // Export advisee list to CSV
  exportAdviseesToCSV(advisees) {
    const headers = ['Name', 'Student ID', 'Program', 'Year', 'GPA', 'Status'];
    const csvContent = [
      headers.join(','),
      ...advisees.map(advisee => [
        `"${advisee.name}"`,
        advisee.studentId,
        advisee.program,
        advisee.year,
        advisee.gpa.toFixed(2),
        advisee.status
      ].join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `advisee_list_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

export default advisorService;
