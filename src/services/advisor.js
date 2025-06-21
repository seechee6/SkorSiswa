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

  // Add a note for an advisee
  async addAdviseeNote(advisorId, studentId, note, courseId = null) {
    try {
      const response = await api.post(`/advisors/${advisorId}/advisees/${studentId}/notes`, {
        note,
        course_id: courseId
      });
      return response.data;
    } catch (error) {
      console.error('Error adding advisee note:', error);
      throw error;
    }
  },

  // Export advisee list to CSV
  exportAdviseesToCSV(advisees) {
    const headers = ['Name', 'Student ID', 'Program', 'Year', 'GPA', 'Status', 'Last Meeting'];
    const csvContent = [
      headers.join(','),
      ...advisees.map(advisee => [
        `"${advisee.name}"`,
        advisee.studentId,
        advisee.program,
        advisee.year,
        advisee.gpa.toFixed(2),
        advisee.status,
        advisee.lastMeeting
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
