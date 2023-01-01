$(async function () {
    let calendar = new FullCalendarWrapper();
    await calendar.init('#calendar_container');
    await calendar.render();
});