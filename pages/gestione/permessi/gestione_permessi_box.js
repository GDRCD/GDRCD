$(function(){

    $('.gestione_permessi #permessi_gerarchia').sortable();

    $('.gestione_permessi #order_confirm').on('click', function() {

        let order = $('.gestione_permessi #permessi_gerarchia').sortable('toArray', {
            attribute: 'data-id'
        });

        Ajax('/pages/gestione/permessi/gestione_permessi_ajax.php',{'order':order,'action':'orderPermission'},PermissionSuccess,'POST');



    });

    function PermissionSuccess(data){

        if(data != ''){

            let datas = JSON.parse(data);

        }
    }

});