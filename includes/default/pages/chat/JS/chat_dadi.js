$(function () {

    ScrollDown();

/*    new Form('.chat_form_ajax').onSubmit({
        path: 'chat/chat_ajax.php',
        success: invioSuccess
    });*/

    function invioSuccess(data) {
        if (data != '') {

            let datas = JSON.parse(data);

            if (datas.response == true) {
                aggiornaChat();
                window.location.reload(true);
            }

        }
    }

    function aggiornaChat() {

        $.ajax({
            url: 'chat/chat_ajax.php',
            type: 'POST',
            data: {'action': 'aggiorna_chat'},
            success: (function (data) {

                if (data != '') {

                    let datas = JSON.parse(data);

                    if (datas.list != '') {
                        $('.chat_azioni_box .chat_azioni', top.parent.document).append(datas.list);
                        let audio = top.parent.document.getElementById('chat_audio');
                        ScrollDown();

                        if (audio != null) {
                            audio.play();
                        }
                    }
                }

            })
        })

    }

    function ScrollDown() {
        let box = $(".chat_azioni", top.parent.document),
            height = box[0].scrollHeight;
        box.animate({scrollTop: height}, 50);
    }

    function getUrlVars() {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    function controllachat() {

        let dir = getUrlVars()['dir'];

        if (dir != undefined) {
            $.ajax({
                url: 'chat/chat_ajax.php',
                type: "POST",
                data: {'action': 'controllaChat', 'dir': dir},
                success: function (data) {
                    if (data != '') {
                        let datas = JSON.parse(data);

                        if (datas.response == false) {
                            window.location.href = '/main.php?dir=' + datas.newdir;
                        }
                    }
                }
            })
        }

    }

    setInterval(function () {
        aggiornaChat()
    }, 20000);

});