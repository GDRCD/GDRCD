$(function () {

});


function refreshMembers(data) {

    if (data) {

        let datas = JSON.parse(data);

        if (datas.response) {
            $('.esiti_members').html(datas.members_list)
        }
    }

}