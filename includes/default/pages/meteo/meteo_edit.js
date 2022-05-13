$(function(){

    new Form('#form_meteo_edit').onSubmit({
        path: 'meteo/meteo_ajax.php',
        form_reset:false
    });

})