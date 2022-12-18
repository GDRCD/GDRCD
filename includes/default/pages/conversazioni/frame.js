$(function(){

    setInterval(() => {
        Ajax('conversazioni/ajax.php', {'action': 'frame_text'}, (data) => {

            if(data !== ''){
                let datas = JSON.parse(data);

                $('body .messages_frame').html(datas.text);
            }

        });
    }, 20000);

});