$(function () {

    $('.groups_storage_container').on('click', '.group_objects_container .single_object', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        let id = $(this).data('id');

        Ajax('servizi/gruppi/ajax.php', {'action': 'get_storage_object_info', 'id': id}, (data) => {
            if (data != '') {

                let datas = JSON.parse(data);

                if (datas.template) {
                    $('.group_objects_info').html(datas.template);
                }

            }
        })
    })

})


function updateObjectView(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.groups_storage_container .group_objects_container').html(datas.new_view);
        $('.group_objects_info').html('');
    }
}