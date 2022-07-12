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
        displayEventTime: true,
        editable: false,

        dayMaxEvents: true, // allow "more" link when too many events

        eventDidMount: function(info) {
            var tooltip = new Tooltip(info.el, {
                title: info.event.extendedProps.description,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        events: "pages/scheda/calendario/event.php?pg="+pg,
    });
    calendar.setOption('locale', 'it');
    calendar.setOption('height', 400);
    calendar.render();

});