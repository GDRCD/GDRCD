$(function(){

    let form = $('.edit-form');

    new Form('.gestione_oggetti_tipo .form').onSubmit({
        path:'/pages/gestione/oggetti/gestione_oggetti_ajax.php',
        success: refreshList
    })

    function refreshList(data){
        if(data){
            let datas = JSON.parse(data);
            if(datas.response){
                $('.gestione_oggetti_tipo .form select[name="tipo"]').html(datas.obj_type_list)
            }
        }
    }

    form.find('select[name="tipo"]').on('change',function(){

        let id = $(this).val()

        Ajax('/pages/gestione/oggetti/gestione_oggetti_ajax.php',{'id':id,'action':'get_object_type_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){


            let datas = JSON.parse(data);

            form.find('input[name="nome"]').val(datas.nome);
            form.find('textarea[name="descrizione"]').val(datas.descrizione);
        }

    }

});