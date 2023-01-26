$(function () {
    Chosen.init('.chosen-select');
    ScrollDownMessages();

    $('body').on('click', '.conversazioni_container a.ajax_link', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();


        let id = $(this).data('id'),
            action = $(this).data('action'),
            button;

        switch (action) {
            case 'delete_conversation':
                button = await SwalWrapper.button('Sei sicuro?', 'Vuoi eseguire questa conversazione?', 'info');
                break;
            case 'add_event_from_conversation':
                button = await SwalWrapper.button('Sei sicuro?', 'Vuoi aggiungere questo evento al calendario?', 'info');
                break;
        }


        if (button) {
            switch (action) {
                case 'delete_conversation':
                    Ajax('conversazioni/ajax.php', {
                        action: action,
                        id: id,
                    }, deleteConvSuccess);
                    break;
                case 'add_event_from_conversation':
                    Ajax('conversazioni/ajax.php', {
                        action: action,
                        id: id,
                    }, addEventFromConvSuccess);
                    break;
            }
        }
    })

    $('body').on('click', '#search_conversations', function () {
        let title = $('.conversations_search_box .search_body .single_input input[name="title"]').val(),
            member = $('.conversations_search_box .search_body .single_input select[name="member"]').val();

        Ajax('conversazioni/ajax.php', {
            action: 'get_filtered_conversations',
            member: member,
            title: title,
        }, filterSuccess)

    })
});

function ScrollDownMessages() {
    let box = $("#conversations_view_container .conversation_body");

    if (box.length > 0) {
        let height = box[0].scrollHeight;
        box.animate({scrollTop: height}, 50);
    }
}

function deleteConvSuccess(data) {

    if (data) {

        let datas = JSON.parse(data),
            box = $('#conversations_view_container');

        if (datas.response) {
            box.find('.conversations_messages').html('');
            box.find('.conversations_list').html(datas.new_conversations);
        }

        SwalWrapper.fire(datas.swal_title, datas.swal_message, datas.swal_type)
    }
}

function addEventFromConvSuccess(data) {

    if (data) {

        let datas = JSON.parse(data),
            box = $('#conversations_view_container');

        SwalWrapper.fire(datas.swal_title, datas.swal_message, datas.swal_type)
    }
}

function invioMessaggioSuccess(data) {

    if (data) {

        let datas = JSON.parse(data),
            box = $('#conversations_view_container');

        if (datas.response) {
            box.find('.conversations_messages').html(datas.new_messages);
            box.find('.conversations_list').html(datas.new_conversations);
            ScrollDownMessages();
        }

    }
}

function filterSuccess(data) {
    if (data) {
        let datas = JSON.parse(data),
            box = $('#conversations_view_container');

        if (datas.response) {
            box.find('.conversations_list').html(datas.new_conversations);
        }
    }
}

function newConvSuccess(data) {

    if (data) {
        let datas = JSON.parse(data);

        if (datas.response) {
            window.location.href = "/main.php?page=conversazioni/index&conversation=" + datas.conv_id;
        }
    }
}

function editConvSuccess(data) {

    if (data) {
        let datas = JSON.parse(data);

        if (datas.response) {
            window.location.href = "/main.php?page=conversazioni/index&conversation=" + datas.conv_id;
        }
    }
}

function conversationsFilter() {

    return true;

}