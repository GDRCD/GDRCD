$(function(){

    new Form('.members_add_form .form, .esiti_members .delete_member_form').onSubmit({
        path:'gestione/esiti/esiti_ajax.php',
        success: refreshMembers
    })

    new Form('.members_add_form .form').onSubmit({
        path:'gestione/esiti/esiti_ajax.php',
        success: refreshMembers
    })


    function refreshMembers(data){

        if(data){

            let datas = JSON.parse(data);

            if(datas.response){
                $('.esiti_members').html(datas.members_list)
            }
        }

    }


});