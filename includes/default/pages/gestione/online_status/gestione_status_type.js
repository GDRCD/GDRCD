$(function () {
    let form = $('.edit-form');

    form.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax('gestione/online_status/gestione_status_ajax.php', {
            'id': id,
            'action': 'get_status_type_data'
        }, setEditInput)
    });

    function setEditInput(data) {
        if (data != '') {
            let datas = JSON.parse(data);
            form.find('input[name="label"]').val(datas.label);
            form.find('input[name="request"]').val(datas.request);
        }
    }
});

function refreshStatusTypeList(data) {
    if (data) {
        let datas = JSON.parse(data);

        if (datas.response) {
            $('.gestione_status_type .form select[name="id"]').html(datas.status_list)
        }
    }
}