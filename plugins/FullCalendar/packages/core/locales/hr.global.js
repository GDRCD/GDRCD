/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'hr',
        week: {
            dow: 1,
            doy: 7, // The week that contains Jan 1st is the first week of the year.
        },
        buttonText: {
            prev: 'Prijašnji',
            next: 'Sljedeći',
            today: 'Danas',
            month: 'Mjesec',
            week: 'Tjedan',
            day: 'Dan',
            list: 'Raspored',
        },
        weekText: 'Tje',
        allDayText: 'Cijeli dan',
        moreLinkText(n) {
            return '+ još ' + n;
        },
        noEventsText: 'Nema događaja za prikaz',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
