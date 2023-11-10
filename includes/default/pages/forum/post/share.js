function shareRedirectBack(post_id){
    window.location.href = '/main.php?page=forum/index&op=post&post_id='+post_id+'&pagination=1'
}

$(function(){
    Chosen.init('.chosen-select');
});