$(function(){

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/razze/gestione_razze_ajax.php',
            {'id': id, 'action': 'get_race_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="sing_m"]').val(datas.sing_m);
                    editForm.find('input[name="sing_f"]').val(datas.sing_f);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                    editForm.find('input[name="immagine"]').val(datas.immagine);
                    editForm.find('input[name="icon"]').val(datas.icon);
                    editForm.find('input[name="url_site"]').val(datas.url_site);
                    editForm.find('input[name="visibile"]').prop("checked",datas.visibile == 1);
                    editForm.find('input[name="iscrizione"]').prop("checked",datas.iscrizione == 1);

                }
            }
        )
    });
})


function updateRaces(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_races_container .form').find('select[name="id"]').html(datas.races_list)
    }
}