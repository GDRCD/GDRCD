$(function(){

    let form = $('.edit-form');

    new Form('.gestione_status .form').onSubmit({
        path:'gestione/online_status/gestione_status_ajax.php',
        success: refreshLists
    })

    function refreshLists(data){
        if(data){
            let datas = JSON.parse(data);
            if(datas.response){
                $('.gestione_status .form select[name="id"]').html(datas.status_list)
            }
        }
    }

    form.find('select[name="id"]').on('change',function(){

        let id = $(this).val()

        Ajax('gestione/online_status/gestione_status_ajax',{'id':id,'action':'get_status_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){


            let datas = JSON.parse(data);

            form.find('select[name="type"]').val(datas.type);
            form.find('input[name="text"]').val(datas.text);


        }

    }

});