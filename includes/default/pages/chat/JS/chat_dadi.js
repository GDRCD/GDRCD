$(function () {

    ScrollDown();

    setInterval(function () {
        aggiornaChat()
    }, 20000);
});


function invioSuccess(data) {
    if (data != '') {
        let datas = JSON.parse(data);
        if (datas.response) {
            aggiornaChat();
            window.location.reload();
        }
    }
}


function aggiornaChat() {
    Ajax('chat/chat_ajax.php', {'action': 'aggiorna_chat'}, (data) => {
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