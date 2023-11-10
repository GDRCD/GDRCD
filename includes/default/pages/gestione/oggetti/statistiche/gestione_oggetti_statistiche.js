$(function () {

    let form = $('.edit-form');

    form.find('select[name="oggetto"],select[name="stat"]').on('change', function () {

        let object = form.find('select[name="oggetto"]').val();
        let stat = form.find('select[name="stat"]').val();

        if(stat && object) {
            Ajax('gestione/oggetti/statistiche/gestione_oggetti_statistiche_ajax.php', {
                'oggetto': object,
                'stat': stat,
                'action': 'get_object_stat_data'
            }, setEditInput)
        }
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);

            form.find('input[name="valore"]').val(datas.valore);
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