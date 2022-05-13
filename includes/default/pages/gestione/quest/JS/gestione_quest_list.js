$(function () {

    $('body').on('click', '.quest_list .commands a.ajax_link', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let id = $(this).data('id'),
            action = $(this).data('action'),
            page = $(this).data('page');

        let button = await Swal.button('Sei sicuro?', 'Vuoi eliminare questa quest?', 'question');

        if (button) {
            Ajax('gestione/quest/gestione_quest_ajax.php', {
                action: action,
                id: id,
                page: page
            }, refreshList)
        }
    })

    function refreshList(data) {

        if (data) {

            let datas = JSON.parse(data);

            if (datas.response) {
                $('.quest_list').html(datas.quest_list)

                Swal.fire(datas.swal_title, datas.swal_message, datas.swal_type)

            }

        }

    }

});