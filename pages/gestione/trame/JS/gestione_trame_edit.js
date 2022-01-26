$(function(){

    new Form('.quest_edit_form .form').onSubmit({
        path:'/pages/gestione/trame/gestione_trame_ajax.php',
        form_reset: false
    })
});