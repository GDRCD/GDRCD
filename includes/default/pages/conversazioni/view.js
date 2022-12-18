$(function () {
    Chosen.init('.chosen-select');
    ScrollDownMessages();

    $('body').on('click', '.conversations_list .single_conversation a.ajax_link', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();


        let id = $(this).data('id'),
            action = $(this).data('action');

        let button = await Swal.button('Sei sicuro?', 'Vuoi eliminare questa conversazione?', 'info');

        if (button) {
            Ajax('conversazioni/ajax.php', {
                action: action,
                id: id,
            }, deleteConvSuccess)
        }
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