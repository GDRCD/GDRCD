/*!
FullCalendar Core v6.0.2
Docs & License: https://fullcalendar.io
(c) 2022 Adam Shaw
*/
(function (index_js) {
    'use strict';

    var locale = {
        code: 'ja',
        buttonText: {
            prev: '前',
            next: '次',
            today: '今日',
            month: '月',
            week: '週',
            day: '日',
            list: '予定リスト',
        },
        weekText: '週',
        allDayText: '終日',
        moreLinkText(n) {
            return '他 ' + n + ' 件';
        },
        noEventsText: '表示する予定はありません',
    };

    index_js.globalLocales.push(locale);

})(FullCalendar);
