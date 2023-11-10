/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'id',
        week: {
            dow: 1,
            doy: 7, // The week that contains Jan 1st is the first week of the year.
        },
        buttonText: {
            prev: 'mundur',
            next: 'maju',
            today: 'hari ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Agenda',
        },
        weekText: 'Mg',
        allDayText: 'Sehari penuh',
        moreLinkText: 'lebih',
        noEventsText: 'Tidak ada acara untuk ditampilkan',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
