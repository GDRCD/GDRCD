$(function(){

    new Form('form').onSubmit({
        path:'/pages/scheda/contatti/contatti_ajax.php',
        success: function(data){

            if(data){
                let datas = JSON.parse(data);
                let pg = $('#pg').val(),
                    id_pg= $('#id_pg').val(),
                    id_contatto=$('#id_contatto').val();


                if(datas.response){
                    window.location.href = '/main.php?page=scheda_contatti&pg='+pg+'&id_pg='+id_pg+'&op=view&id='+id_contatto;
                }
            }

        }
    })

    $('form #cd_add').on('click',function(){
        Ajax('pages/scheda/contatti/contatti_ajax.php',{'action':'new_nota'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});