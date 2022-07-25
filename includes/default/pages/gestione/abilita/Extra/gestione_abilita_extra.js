$(function () {

    let form = $('.edit-form');

    form.find('#fake-extraction').on('click', function () {

        let abi = $('#ModAbiExtraForm select[name="abilita"]').val(),
            grado = $('#ModAbiExtraForm select[name="grado"]').val();

        Ajax('gestione/abilita/Extra/gestione_abilita_ajax.php', {
            'action': 'get_extra_data',
            'abilita': abi,
            'grado': grado
        }, function (data) {

            if (data != '') {

                let datas = JSON.parse(data);


                if (datas.response) {
                    form.find('textarea[name="descr"]').val(datas.Descr);
                    form.find('input[name="costo"]').val(datas.Costo);
                } else {
                    alert('Non esistono dati per questo livello di questa abilità.');
                    form.find('textarea[name="descr"]').val('');
                    form.find('input[name="costo"]').val('');
                }


            }
        })
    });

});