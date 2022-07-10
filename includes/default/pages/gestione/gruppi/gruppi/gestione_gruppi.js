$(function(){

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/gruppi/gruppi/gestione_gruppi_ajax.php',
            {'id': id, 'action': 'get_group_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="immagine"]').val(datas.immagine);
                    editForm.find('select[name="tipo"]').val(datas.tipo);
                    editForm.find('input[name="url"]').val(datas.url);
                    editForm.find('textarea[name="statuto"]').val(datas.statuto);
                    editForm.find('input[name="denaro"]').val(datas.denaro);
                    editForm.find('input[name="visibile"]').prop("checked",datas.visibile == 1);
                }
            }
        )
    });
})


function updateGroupsList(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.group_management .form').find('select[name="id"]').html(datas.groups_list)
    }
}