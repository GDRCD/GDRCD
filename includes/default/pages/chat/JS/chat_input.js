$(function () {

    ScrollDown();

    setInterval(function () {
        aggiornaChat()
        controllachat();
    }, 20000);

    Chosen.init('.whisp-chosen')
});


function invioAzioneSuccess(data) {
    if (data != '') {
        let datas = JSON.parse(data);
        if (datas.response == true) {
            aggiornaChat();
            $('.chat_form_ajax select[name="tipo"]').val('A');
            $('.chat_form_ajax input[name="testo"]').val('');
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
    let box = $(".chat_azioni"),
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
        Ajax('chat/chat_ajax.php', {'action': 'controllaChat', 'dir': dir}, (data) => {
            if (data != '') {
                let datas = JSON.parse(data);
                if (datas.response == false) {
                    window.location.href = '/main.php?dir=' + datas.newdir;
                }
            }
        })
    }
}
