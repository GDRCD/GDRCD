$(function(){

    let form = $('.edit-form');

    form.find('select[name="id"]').on('change',function(){

        let id = $(this).val()

        Ajax('/pages/gestione/oggetti/gestione_oggetti_ajax.php',{'id':id,'action':'get_object_position_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){


            let datas = JSON.parse(data);

            form.find('input[name="nome"]').val(datas.nome);
            form.find('input[name="immagine"]').val(datas.immagine);
            form.find('input[name="numero"]').val(datas.numero);
        }

    }

});