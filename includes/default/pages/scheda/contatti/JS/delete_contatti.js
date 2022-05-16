$(function(){


    $('body').on('click','.td.commands .ajax_link',function(e){

        e.preventDefault();

        let main = $(this),
            action = main.data('action'),
            id = main.data('id');

        Ajax('/pages/scheda/contatti/contatti_ajax.php',{
            action: action,
            id: id
        },refreshList)


    })

    function refreshList(data){

        if(data){

            let datas = JSON.parse(data);

            if(datas.response){
                $('.contatti_list').html(datas.contatti_list)

                Swal.fire(datas.swal_title,datas.swal_message,datas.swal_type)

            }

        }

    }


});
