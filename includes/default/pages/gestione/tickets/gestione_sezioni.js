$(function () {

    let form = $('.edit-form');

    form.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax('gestione/tickets/gestione_sezioni_ajax.php', {'id': id, 'action': 'get_section_data'}, setEditInput)
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);
            form.find('input[name="titolo"]').val(datas.titolo);
            form.find('textarea[name="descrizione"]').val(datas.descrizione);
            form.find('select[name="sezione_padre"]').val(datas.sezione_padre);
            form.find('input[name="creabile"]').prop("checked", datas.creabile);
        }
    }

});

function refreshSectionsList(data) {
    if (data) {
        let datas = JSON.parse(data);
        if (datas.response) {
            $('.gestione_sezioni .form select[name="id"], .gestione_sezioni .form select[name="sezione_padre"]').html(datas.sections_list)
        }
    }
}