$(function () {

    let form = $('.edit-form');

    form.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax('gestione/ticket/gestione_status_ajax.php', {'id': id, 'action': 'get_status_data'}, setEditInput)
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);
            console.log(datas);
            form.find('input[name="titolo"]').val(datas.titolo);
            form.find('textarea[name="descrizione"]').val(datas.descrizione);
            form.find('input[name="colore"]').val(datas.colore);
            form.find('input[name="is_initial_state"]').prop("checked", datas.is_initial_state);
            form.find('input[name="is_blocked"]').prop("checked", datas.is_blocked);

        }
    }

});

function refreshStatusList(data) {
    if (data) {
        let datas = JSON.parse(data);
        if (datas.response) {
            $('.gestione_status .form select[name="id"]').html(datas.status_list)
        }
    }
}