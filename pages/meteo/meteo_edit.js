$(function(){

    new Form('#form_meteo_edit').onSubmit({
        path: '/pages/meteo/meteo_ajax.php',
        form_reset:false
    });

})