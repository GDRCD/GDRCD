$(function(){

    let form = $('.form-chat');

    new Form('.form').onSubmit({
        path:'/pages/gestione/gathering/gathering_ajax.php',
        success: refreshList
    })

    function refreshList(data){
        if(data){
            let datas = JSON.parse(data);
            if(datas.response){
                window.location.href = '/main.php?page=gestione_gathering_chat';
                //$('.form-chat select[name="chat"]').html(datas.obj_list)
            }
        }
    }

    form.find('select[name="chat"]').on('change',function(){

        let id = $(this).val()

        Ajax('/pages/gestione/gathering/gathering_ajax.php',{'id':id,'action':'get_object_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){
            let datas = JSON.parse(data);
            form.find('input[name="drop_rate"]').val(datas.drop_rate);
        }

    }

});