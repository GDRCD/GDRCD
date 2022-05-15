$(function(){

    let editForm = $('.edit_form');


    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/gruppi/ruoli/gestione_ruoli_ajax.php',
            {'id': id, 'action': 'get_role_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="immagine"]').val(datas.immagine);
                    editForm.find('select[name="gruppo"]').val(datas.gruppo);
                    editForm.find('input[name="stipendio"]').val(datas.stipendio);
                    editForm.find('input[name="poteri"]').prop("checked",datas.poteri == 1);
                }
            }
        )
    });
})


function updateRolesList(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.group_roles_management .form').find('select[name="id"]').html(datas.roles_list)
    }
}