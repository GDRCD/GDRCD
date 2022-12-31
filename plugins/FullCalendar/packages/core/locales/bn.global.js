/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'bn',
        week: {
            dow: 0,
            doy: 6, // The week that contains Jan 1st is the first week of the year.
        },
        buttonText: {
            prev: 'পেছনে',
            next: 'সামনে',
            today: 'আজ',
            month: 'মাস',
            week: 'সপ্তাহ',
            day: 'দিন',
            list: 'তালিকা',
        },
        weekText: 'সপ্তাহ',
        allDayText: 'সারাদিন',
        moreLinkText(n) {
            return '+অন্যান্য ' + n;
        },
        noEventsText: 'কোনো ইভেন্ট নেই',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
