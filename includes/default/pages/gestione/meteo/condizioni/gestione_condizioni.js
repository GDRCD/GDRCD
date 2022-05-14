$(function () {

    let editForm = $('.edit-form');

    Chosen.init('.chosen-select');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax(
            'gestione/meteo/condizioni/gestione_condizioni_ajax.php',
            {'id': id, 'action': 'get_condition_data'},
            function (data) {
                if (data != '') {

                    let datas = JSON.parse(data);

                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="immagine"]').val(datas.img);

                    if (datas.vento) {
                        Chosen.update(editForm.find('select#vento'), datas.vento.split(','))
                    }
                }
            }
        )
    });
})


function updateConditionsList(data) {

    if (data) {

        let datas = JSON.parse(data);

        $('.gestione_meteo_condizioni .form').find('select[name="id"]').html(datas.List)

        Chosen.reset('.chosen-select');
    }
}