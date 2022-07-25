$(function () {

    let form = $('.edit-form');

    form.find('select[name="oggetto"]').on('change', function () {
        let id = $(this).val()
        Ajax('gestione/oggetti/gestione_oggetti_ajax.php', {'id': id, 'action': 'get_object_data'}, setEditInput)
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);

            form.find('select[name="tipo"]').val(datas.tipo);
            form.find('input[name="nome"]').val(datas.nome);
            form.find('textarea[name="descrizione"]').val(datas.descrizione);
            form.find('input[name="immagine"]').val(datas.immagine);
            form.find('input[name="cariche"]').val(datas.cariche);
            form.find('input[name="indossabile"]').prop("checked", datas.indossabile);
        }
    }

});


function refreshObjectList(data) {
    if (data) {
        let datas = JSON.parse(data);
        if (datas.response) {
            $('.gestione_oggetti .form .obj_list').html(datas.obj_list)
        }
    }
}