$(function(){

    $('.quest_insert_form #new_member').on('click',function(){

        Ajax('pages/gestione/quest/gestione_quest_ajax.php',{'action':'create_input'},CreateInput)

        return false;

    });

    function CreateInput(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('.quest_insert_form .partecipanti_box').append(datas.Input);

        }

    }

});