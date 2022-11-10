$(function(){

    setInterval(() => {
        Ajax('news/ajax.php', {'action': 'frame_text'}, (data) => {

            if(data !== ''){
                let datas = JSON.parse(data);

                $('body .news_frame').html(datas.text);
            }

        });
    }, 20000);

});