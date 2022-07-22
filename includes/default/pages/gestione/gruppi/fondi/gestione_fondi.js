$(function(){

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/gruppi/fondi/gestione_fondi_ajax.php',
            {'id': id, 'action': 'get_found_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('select[name="gruppo"]').val(datas.gruppo);
                    editForm.find('input[name="denaro"]').val(datas.denaro);
                    editForm.find('input[name="interval"]').val(datas.interval);
                    editForm.find('select[name="interval_type"]').val(datas.interval_type);
                }
            }
        )
    });
})


function updateFounds(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.group_found_managements .form').find('select[name="id"]').html(datas.founds_list)
    }
}