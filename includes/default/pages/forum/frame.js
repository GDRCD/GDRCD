$(function(){

    setInterval(() => {
        Ajax('forum/ajax.php', {'action': 'frame_text'}, (data) => {

            if(data !== ''){
                let datas = JSON.parse(data);

                $('body .forum_frame').html(datas.text);
            }

        });
    }, 20000);

});