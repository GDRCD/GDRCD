/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'tr',
        week: {
            dow: 1,
            doy: 7, // The week that contains Jan 1st is the first week of the year.
        },
        buttonText: {
            prev: 'geri',
            next: 'ileri',
            today: 'bugün',
            month: 'Ay',
            week: 'Hafta',
            day: 'Gün',
            list: 'Ajanda',
        },
        weekText: 'Hf',
        allDayText: 'Tüm gün',
        moreLinkText: 'daha fazla',
        noEventsText: 'Gösterilecek etkinlik yok',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
