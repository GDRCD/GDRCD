$(function(){

    new Form('form').onSubmit({
        path:'/pages/scheda/contatti/contatti_ajax.php',
        success: function(data){

            if(data){
                let datas = JSON.parse(data);


                if(datas.response){
                    window.location.href = '/main.php?page=scheda_contatti&pg='+data.pg+'&id_pg='+data.id_pg;
                }
            }

        }
    })

    $('form #cd_add').on('click',function(){
        Ajax('pages/scheda/contatti/contatti_ajax.php',{'action':'new_contatto'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});