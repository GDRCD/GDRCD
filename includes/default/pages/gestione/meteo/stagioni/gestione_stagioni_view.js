$(function () {


    $('.stagioni-table .commands .ajax_link').on('click', async function () {

        let id = $(this).data('id'),
            action = $(this).data('action');

        let button = await Swal.button('Sei sicuro?', 'Vuoi eliminare questa stagione? Azione irreversibile.', 'info');

        if (button) {
            Ajax('gestione/meteo/stagioni/gestione_stagioni_ajax.php', {
                action: action,
                id: id
            }, refreshList)
        }
    })


    function refreshList(data) {

        if (data) {

            let datas = JSON.parse(data);

            if (datas.response) {
                $('.stagioni-table').html(datas.stagioni_list)

                Swal.fire(datas.swal_title, datas.swal_message, datas.swal_type)

            }

        }

    }

})