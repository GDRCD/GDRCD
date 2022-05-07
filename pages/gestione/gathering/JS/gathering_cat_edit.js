$(function(){

    new Form('form').onSubmit({
        path:'/pages/gestione/gathering/gathering_ajax.php',
        success: function(data){


            if(data){
                let datas = JSON.parse(data);


                if(datas.response){
                    window.location.href = '/main.php?page=gestione_gathering_category';
                }
            }

        }
    })

    $('form #cd_add').on('click',function(){
        Ajax('pages/gestione/gathering/gathering_ajax.php',{'action':'edit_cat'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});