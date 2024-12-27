$(function () {

    /*** FUNCTIONS **/
    function refreshMessagesCounter() {
        Ajax('conversazioni/ajax.php', {'action': 'have_new_messages'}, (data) => {
            if (data !== '') {
                let datas = JSON.parse(data);
                let new_messages = datas?.new_messages;
                new_messages > 0 ? $('.icon_messages').addClass('new_messages') : $('.icon_messages').removeClass('new_messages');
            }
        });
    }

    function refreshForumCounter() {
        Ajax('forum/ajax.php', {'action': 'have_new_posts'}, (data) => {

            if (data !== '') {
                let datas = JSON.parse(data);
                let new_posts = datas?.new_posts;
                new_posts > 0 ? $('.icon_forum').addClass('new_messages') : $('.icon_forum').removeClass('new_messages');
            }

        });
    }

    function refreshNewsCounter() {
        Ajax('news/ajax.php', {'action': 'have_new_news'}, (data) => {

            if (data !== '') {
                let datas = JSON.parse(data);
                let new_news = datas?.new_news;
                new_news > 0 ? $('.icon_news').addClass('new_messages') : $('.icon_news').removeClass('new_messages');
            }

        });
    }

    function renderStatusOnline(instance) {
        Ajax('online_status/ajax.php', {'action': 'render_set_content'}, (data) => {
            if (data != '') {
                let content = JSON.parse(data);
                instance.setContent(content?.content);
            }
        })
    }

    function renderNotifications(instance) {
        Ajax('notifiche/ajax.php', {'action': 'notifications_list'}, (data) => {
            if (data != '') {
                let datas = JSON.parse(data);
                instance.setContent(datas.list);
            }
        })
    }

    function refreshNotificationsCounter(instance) {
        Ajax('notifiche/ajax.php', {'action': 'count_new_notifications'}, (data) => {
            if (data != '') {
                let datas = JSON.parse(data);
                let new_notifications = datas?.new_notifications;
                new_notifications > 0 ? $('.icon_notifications').addClass('new_messages') : $('.icon_notifications').removeClass('new_messages');
            }
        })
    }


    /*** INIT TOOLTIPS ***/

    TippyWrapper.init('.box_menu_icons .frame_single_icon.icon_messages', '<div>Messaggi</div>');

    TippyWrapper.init('.box_menu_icons .frame_single_icon.icon_forum', '<div>Forum</div>');

    TippyWrapper.init('.box_menu_icons .frame_single_icon.icon_news', '<div>News</div>');

    TippyWrapper.initWithAjax('.box_menu_icons .frame_single_icon.icon_notifications', 'Loading...', {
        onShow: renderNotifications
    });

    TippyWrapper.initWithAjax('.box_menu_icons .frame_single_icon.icon_status', 'Loading...', {
        onShow: renderStatusOnline
    });


    /*** CRONJOB **/

    refreshMessagesCounter();
    refreshForumCounter();
    refreshNewsCounter();
    refreshNotificationsCounter();

    setInterval(() => {
        refreshMessagesCounter();
        refreshForumCounter();
        refreshNewsCounter();
        refreshNotificationsCounter();
    }, 20000);

});