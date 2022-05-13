$(function(){

    new Form('.give_answer .form').onSubmit({
        path:'/pages/gestione/esiti/esiti_ajax.php',
        success: refreshAnswers
    })

    function refreshAnswers(data){

        if(data){
            let datas = JSON.parse(data);

            if(datas.response){
                $('.answer_list').html(datas.new_view);
            }
        }

    }

    $('.answer_box .answer_list .single_answer .dice_result').on('click',function(){
        $(this).find('.internal_text').slideToggle('fast');
    });

    $('.give_answer form #cd_add').on('click',function(){

        Ajax('pages/gestione/esiti/esiti_ajax.php',{'action':'cd_add'},AddCD);
    });

    function AddCD(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('.give_answer form .cd_box').append(datas.InputHtml);


        }

    }
});