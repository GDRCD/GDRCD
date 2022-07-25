$(function () {

    let editForm = $('.edit-form');

    editForm.find('select[name="requisito"]').on('change', function () {
        let id = $(this).val()
        Ajax(
            'gestione/abilita/Requisiti/gestione_requisiti_ajax.php',
            {'id': id, 'action': 'get_requirement_data'},
            function (data) {

                if (data != '') {


                    let datas = JSON.parse(data);

                    editForm.find('select[name="abilita"]').val(datas.abilita);
                    editForm.find('select[name="grado"]').val(datas.grado);
                    editForm.find('select[name="tipo"]').val(datas.tipo);
                    editForm.find('select[name="id_rif"]').val(datas.id_rif);
                    editForm.find('input[name="lvl_rif"]').val(datas.lvl_rif);
                }

            }
        ).then(() => {
        })
    });


});

function updateFormList() {
    Ajax(
        'gestione/abilita/Requisiti/gestione_requisiti_ajax.php',
        {'action': 'get_requirement_list'},
        function (data) {

            if (data) {
                let datas = JSON.parse(data);

                $('.gestione_abilita_requisiti .form').find('select[name="requisito"]').html(datas.List)
            }
        })
}