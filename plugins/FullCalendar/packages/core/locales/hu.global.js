/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'hu',
        week: {
            dow: 1,
            doy: 4, // The week that contains Jan 4th is the first week of the year.
        },
        buttonText: {
            prev: 'vissza',
            next: 'előre',
            today: 'ma',
            month: 'Hónap',
            week: 'Hét',
            day: 'Nap',
            list: 'Lista',
        },
        weekText: 'Hét',
        allDayText: 'Egész nap',
        moreLinkText: 'további',
        noEventsText: 'Nincs megjeleníthető esemény',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
