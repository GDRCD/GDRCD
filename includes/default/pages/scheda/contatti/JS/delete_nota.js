$(function(){

    $('body').on('click', '.note_list .commands a.ajax_link', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let id = $(this).data('id'),
            action = $(this).data('action'),
            page = $(this).data('page');

        let button = await Swal.button('Sei sicuro?', 'Vuoi eliminare questa nota?', 'question');

        if (button) {
            Ajax('scheda/contatti/contatti_ajax.php', {
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
                $('.note_list').html(datas.note_list)

                Swal.fire(datas.swal_title, datas.swal_message, datas.swal_type)

            }

        }

    }

})