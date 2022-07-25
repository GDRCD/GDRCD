$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/gruppi/lavori/gestione_lavori_ajax.php',
            {'id': id, 'action': 'get_works_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="immagine"]').val(datas.immagine);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                    editForm.find('input[name="stipendio"]').val(datas.stipendio);
                }
            }
        )
    });

})


function updateWorksList(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.works_management .form').find('select[name="id"]').html(datas.works_list)
    }
}