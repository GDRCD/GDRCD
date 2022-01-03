$(function(){

    let form = $('.edit-form');

    form.find('select[name="requisito"]').on('change',function(){

        let id = $(this).val()

        Ajax('/pages/gestione/abilita/Requisiti/gestione_requisiti_ajax.php',{'id':id,'action':'get_requirement_data'},setEditInput)


    });

    function setEditInput(data){

        if(data != ''){


            let datas = JSON.parse(data);

            form.find('select[name="abilita"]').val(datas.abilita);
            form.find('select[name="grado"]').val(datas.grado);
            form.find('select[name="tipo"]').val(datas.tipo);
            form.find('select[name="id_rif"]').val(datas.id_rif);
            form.find('input[name="lvl_rif"]').val(datas.lvl_rif);
        }

    }

});