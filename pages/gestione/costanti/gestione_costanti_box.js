$(function(){

    $('.single_section .form_title').on('click',function(){
        $(this).closest('.single_section').find('.box_input').slideToggle('fast');
    });


});
