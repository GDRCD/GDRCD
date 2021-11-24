$(function(){

    let form = $('.edit-form');

    form.find('select[name="id"]').on('change',function(){

        let id = $(this).val()

        Ajax('/pages/gestione/online_status/gestione_status_ajax',{'id':id,'action':'get_status_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){


            let datas = JSON.parse(data);

            form.find('select[name="type"]').val(datas.type);
            form.find('input[name="text"]').val(datas.text);


        }

    }

});