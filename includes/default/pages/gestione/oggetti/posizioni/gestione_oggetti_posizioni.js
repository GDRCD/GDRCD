$(function () {

    let form = $('.edit-form');

    form.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax('gestione/oggetti/posizioni/gestione_oggetti_posizioni_ajax.php', {
            'id': id,
            'action': 'get_object_position_data'
        }, setEditInput)
    });

    function setEditInput(data) {

        if (data != '') {
            let datas = JSON.parse(data);

            form.find('input[name="nome"]').val(datas.nome);
            form.find('input[name="immagine"]').val(datas.immagine);
            form.find('input[name="numero"]').val(datas.numero);
        }
    }

});

function refreshObjPosList(data) {
    if (data) {
        let datas = JSON.parse(data);
        if (datas.response) {
            $('.gestione_oggetti_posizione .form select[name="id"]').html(datas.obj_position_list)
        }
    }
}