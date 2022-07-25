$(function () {
    let form = $('.edit-form');

    form.find('select[name="stat"]').on('change', function () {
        let id = $(this).val();
        Ajax('gestione/statistiche/gestione_statistiche_ajax.php', {'id': id, 'action': 'get_stat_data'}, setEditInput);
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);
            form.find('input[name="nome"]').val(datas.nome);
            form.find('textarea[name="descr"]').val(datas.descrizione);
            form.find('input[name="max_val"]').val(datas.max_val);
            form.find('input[name="min_val"]').val(datas.min_val);
            form.find('input[name="iscrizione"]').prop("checked", datas.iscrizione);
        }
    }
});

function refreshStatsList(data) {
    if (data) {
        let datas = JSON.parse(data);
        if (datas.response) {
            $('.gestione_statistiche .form select[name="stat"]').html(datas.stat_list)
        }
    }
}