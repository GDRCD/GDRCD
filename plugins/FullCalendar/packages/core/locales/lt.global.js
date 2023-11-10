/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'lt',
        week: {
            dow: 1,
            doy: 4, // The week that contains Jan 4th is the first week of the year.
        },
        buttonText: {
            prev: 'Atgal',
            next: 'Pirmyn',
            today: 'Šiandien',
            month: 'Mėnuo',
            week: 'Savaitė',
            day: 'Diena',
            list: 'Darbotvarkė',
        },
        weekText: 'SAV',
        allDayText: 'Visą dieną',
        moreLinkText: 'daugiau',
        noEventsText: 'Nėra įvykių rodyti',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
