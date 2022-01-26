$(function(){

    new Form('.gestione_costanti form').onSubmit({
        path: '/pages/gestione/costanti/gestione_costanti_ajax.php',
        form_reset:false
    })

    $('.single_section .form_title').on('click',function(){
        $(this).closest('.single_section').find('.box_input').slideToggle('fast');
    });


});
