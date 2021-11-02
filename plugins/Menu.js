function Menu(){
    $('.menu_box .section_title').on('click',function(){
        $(this).closest('.single_section').find('.box_input').slideToggle('fast');
    });
}