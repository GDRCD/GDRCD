function updateForumPermission(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_forums_permissions .visual_result').html(datas.list)
    }
}

function submitForumVisual(){
    $('.visual_form').submit();
}