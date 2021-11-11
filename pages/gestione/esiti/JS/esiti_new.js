$(function(){

    $('form #cd_add').on('click',function(){

        Ajax('pages/gestione/esiti/esiti_ajax.php',{'op':'cd_add'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});