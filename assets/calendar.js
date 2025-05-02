import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    selectable: true,
    editable: true,
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: '/your-calendar-events-endpoint', 

    dateClick: async function (info) {
      const { value: formValues } = await Swal.fire({
        title: 'Create New Task',
        html: `
          <input id="swal-title" class="swal2-input" placeholder="Task name">
          <input type="file" id="swal-file" class="swal2-file">
        `,
        focusConfirm: false,
        preConfirm: () => {
          return {
            title: document.getElementById('swal-title').value,
            file: document.getElementById('swal-file').files[0],
          };
        }
      });

      if (formValues && formValues.title) {
        const formData = new FormData();
        formData.append('title', formValues.title);
        formData.append('start', info.dateStr);
        formData.append('end', info.dateStr);
        if (formValues.file) {
          formData.append('file', formValues.file); 
        }

        try {
          const response = await fetch('/calendar-event-create', {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest' 
            },
            body: formData
          });

          if (response.ok) {
            await response.json();
            calendar.refetchEvents(); // 🔄 Refresh calendar
            Swal.fire('Success', 'Event created successfully', 'success');
          } else {
            Swal.fire('Error', 'Failed to create event', 'error');
          }
        } catch (error) {
          console.error('Error creating event:', error);
          Swal.fire('Error', 'Network issue during event creation', 'error');
        }
      }
    },

    eventDrop: async function (info) {
      try {
        const response = await fetch('/calendar-event-update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            id: info.event.id,
            start: info.event.start.toISOString(),
            end: info.event.end ? info.event.end.toISOString() : null
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error('Update failed:', errorData);
          Swal.fire('Error', 'Failed to update event', 'error');
          info.revert(); // ⏪ Revert if error
        }
      } catch (error) {
        console.error('Error updating event:', error);
        Swal.fire('Error', 'Network error while updating event', 'error');
        info.revert();
      }
    }
  });

  calendar.render();
});
