$(function(){

    $('#ModAbiRequisitoForm select[name="req_id"]').on('change',function(){

        let id = $(this).val();

        $.ajax({
           url:'/pages/Abilita/Requisiti/gestione_requisiti_ajax.php',
            type:"POST",
            data:{'action':'DatiAbiRequisito','id':id},
            success:function(data){

               if(data != ''){

                   let datas = JSON.parse(data);

                   $('#ModAbiRequisitoForm select[name="id_req"] optgroup option[selected="selected"]').attr('selected',false);

                   if(datas.response) {
                       $('#ModAbiRequisitoForm select[name="abi"]').val(datas.Abi);
                       $('#ModAbiRequisitoForm select[name="grado"]').val(datas.Grado);
                       $('#ModAbiRequisitoForm select[name="tipo"]').val(datas.Tipo);
                       $('#ModAbiRequisitoForm select[name="liv_req"]').val(datas.LivRif);

                       if(datas.Tipo == 1) {
                           $('#ModAbiRequisitoForm select[name="id_req"] optgroup[label="Abilita"] option[value="'+datas.IdRif+'"]').attr('selected',true);
                       }
                       else if(datas.Tipo == 2) {
                           $('#ModAbiRequisitoForm select[name="id_req"] optgroup[label="Caratteristiche"] option[value="'+datas.IdRif+'"]').attr('selected',true);
                       }

                   } else{
                       $('#ModAbiRequisitoForm select[name="abi"]').val('');
                       $('#ModAbiRequisitoForm select[name="grado"]').val('');
                       $('#ModAbiRequisitoForm select[name="tipo"]').val('');
                       $('#ModAbiRequisitoForm select[name="id_req"]').val('');
                       $('#ModAbiRequisitoForm select[name="liv_req"]').val('');
                   }


               }
            }


        });

    });

});