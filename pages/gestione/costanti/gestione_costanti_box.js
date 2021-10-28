$(function(){

    $('.single_section .gestione_form_title').on('click',function(){
        $(this).closest('.single_section').find('.box_input').slideToggle('fast');
    });


});
