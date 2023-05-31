$(function($) {
    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/calendario/gestione_tipi_ajax.php',
            {'id': id, 'action': 'get_calendar_event_type'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="colore_bg"]').val(datas.colore_bg);
                    editForm.find('input[name="colore_testo"]').val(datas.colore_testo);
                    editForm.find('select[name="pubblico"]').val(datas.pubblico);
                    editForm.find('select[name="permessi"]').val(datas.permessi);
                }
            }
        )
    });
})

function updateCalendarEvents(data) {
    if (data) {
        let datas = JSON.parse(data);
        console.log(datas)
        $('.event_types_management .form').find('select[name="id"]').html(datas.event_types)
    }
}