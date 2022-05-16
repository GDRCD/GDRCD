$(function(){

    $('form #cd_add').on('click',function(){
        Ajax('gestione/esiti/esiti_ajax.php',{'action':'cd_add'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('form .cd_box').append(datas.InputHtml);


        }

    }
});

function refreshEsitiList(data){
    if(data){
        let datas = JSON.parse(data);

        if(datas.response){
            window.location.href = '/main.php?page=gestione_esiti';
        }
    }
}