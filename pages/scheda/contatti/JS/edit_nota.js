$(function(){

    new Form('form').onSubmit({
        path:'/pages/scheda/contatti/contatti_ajax.php',
        success: function(data){


            if(data){
                let datas = JSON.parse(data);
                let id = $('#id').val(),
                    id_pg= $('#id_pg').val();


                if(datas.response){
                    top.$( ".selector" ).dialog( "destroy" );
                }
            }

        }
    })

    $('form #cd_add').on('click',function(){
        Ajax('pages/scheda/contatti/contatti_ajax.php',{'action':'edit_nota'},AddCD);
    });

    function AddCD(data){

        top.$( ".selector" ).dialog( "destroy" );

    }
});