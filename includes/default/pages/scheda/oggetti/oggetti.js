$(function () {
    $('.objects_box').on('click', '.name', function (e) {
        e.stopImmediatePropagation();
        let id = $(this).data('id');
        if (id) {
            Ajax('scheda/oggetti/ajax.php',
                {'obj': id, 'action': 'get_object_info'},
                function (data) {
                    if (data) {
                        let datas = JSON.parse(data);
                        if (datas.template) {
                            $('.scheda_oggetti .object_info_box').html(datas.template);
                        }
                    }
                }
            );
        }
    })
})

function updateObjects(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.objects_box.equipped_objects').html(datas.new_equip)
        $('.objects_box.inventory_objects').html(datas.new_inventory)
        $('.object_info_box').html('');
    }
}
