$(function () {

    $('body').on('click', '.trame_list .commands a.ajax_link', async function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let id = $(this).data('id'),
            action = $(this).data('action'),
            page = $(this).data('page');

        let button = await SwalWrapper.button('Sei sicuro?', 'Vuoi eliminare questa trama?', 'info');

        if (button) {
            Ajax('gestione/trame/gestione_trame_ajax.php', {
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
                $('.trame_list').html(datas.trame_list)

                SwalWrapper.fire(datas.swal_title, datas.swal_message, datas.swal_type)

            }

        }

    }

})