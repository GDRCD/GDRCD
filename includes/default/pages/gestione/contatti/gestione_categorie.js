$(function($) {
    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/contatti/gestione_categorie_ajax.php',
            {'id': id, 'action': 'get_category_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);

                }
            }
        )
    });
})

function updateCategoriesList(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.categories_management .form').find('select[name="id"]').html(datas.categories_list)
    }
}