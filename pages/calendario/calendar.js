document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            center: '',
            left: 'title',
            themeSystem: 'bootstrap5',
        },
        displayEventTime: false,
        editable: false,
        navLinks: true, // can click day/week names to navigate views
        dayMaxEvents: true, // allow "more" link when too many events
        events: "pages/calendario/event.php",
    });
    calendar.setOption('locale', 'it');
    calendar.setOption('height', 400);
    calendar.render();
});