$(function(){

    new Form('form').onSubmit({
        path:'/pages/gestione/gathering/gathering_ajax.php',
        success: function(data){


            if(data){
                let datas = JSON.parse(data);


                if(datas.response){
                 //   window.location.href = '/main.php?page=gestione_gathering_item';
                    location.reload();

                }
            }

        }
    })

    $('form #cd_add').on('click',function(){
        Ajax('pages/gestione/gathering/gathering_ajax.php',{'action':'edit_chat_item'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});