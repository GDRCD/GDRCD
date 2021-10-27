$(function(){

    $('#ModAbiExtraForm #fake-extraction').on('click',function(){

        let abi = $('#ModAbiExtraForm select[name="abi"]').val(),
            grado = $('#ModAbiExtraForm select[name="grado"]').val();

        $.ajax({
           url:'/pages/gestione/abilita/Extra/gestione_abilita_ajax.php',
            type:"POST",
            data:{'action':'DatiAbiExtra','abi':abi,'grado':grado},
            success:function(data){

               if(data != ''){

                   let datas = JSON.parse(data);


                   if(datas.response) {
                       $('#ModAbiExtraForm textarea[name="descr"]').val(datas.Descr);
                       $('#ModAbiExtraForm input[name="costo"]').val(datas.Costo);
                   } else{
                       alert('Non esistono dati per questo livello di questa abilità.');
                       $('#ModAbiExtraForm textarea[name="descr"]').val('');
                       $('#ModAbiExtraForm input[name="costo"]').val('');
                   }


               }
            }


        });

    });

});