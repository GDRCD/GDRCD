/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'si-lk',
        week: {
            dow: 1,
            doy: 4, // The week that contains Jan 4th is the first week of the year.
        },
        buttonText: {
            prev: 'පෙර',
            next: 'පසු',
            today: 'අද',
            month: 'මාසය',
            week: 'සතිය',
            day: 'දවස',
            list: 'ලැයිස්තුව',
        },
        weekText: 'සති',
        allDayText: 'සියලු',
        moreLinkText: 'තවත්',
        noEventsText: 'මුකුත් නැත',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
