$(function () {

    let form = $('.edit-form');

    new Form('.gestione_negozi .form').onSubmit({
        path: 'gestione/mercato/gestione_mercato_ajax.php',
        success: refreshShopLists
    })

    function refreshShopLists(data) {

        if (data) {

            let datas = JSON.parse(data);

            if (datas.response) {

                $('.gestione_negozi select[name="id"]').html(datas.shop_list)

            }

        }

    }

    form.find('select[name="id"]').on('change', function () {

        let id = $(this).val()

        Ajax('gestione/mercato/gestione_mercato_ajax.php', {'shop': id, 'action': 'get_shop_data'}, setEditInput)


    });

    function setEditInput(data) {

        if (data != '') {


            let datas = JSON.parse(data);

            form.find('input[name="nome"]').val(datas.nome);
            form.find('textarea[name="descrizione"]').val(datas.descrizione);
            form.find('input[name="immagine"]').val(datas.immagine);
        }

    }

});