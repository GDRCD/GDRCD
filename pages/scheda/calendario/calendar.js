document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var url =  new URL(window.location.href);
    var pg = url.searchParams.get("pg");
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
        events: "pages/scheda/calendario/event.php?pg="+pg,
    });
    calendar.setOption('locale', 'it');
    calendar.setOption('height', 400);
    calendar.render();

});