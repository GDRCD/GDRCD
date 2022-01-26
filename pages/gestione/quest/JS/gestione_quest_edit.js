$(function(){

    new Form('.quest_edit_form').onSubmit({
        path:'/pages/gestione/quest/gestione_quest_ajax.php',
        form_reset: false
    })

    $('.quest_edit_form #new_member').on('click',function(){

        Ajax('pages/gestione/quest/gestione_quest_ajax.php',{'action':'create_input'},CreateInput)

        return false;

    });

    function CreateInput(data){

        if(data != ''){

            let datas = JSON.parse(data);

            $('.quest_edit_form .partecipanti_box').append(datas.Input);

        }

    }

});