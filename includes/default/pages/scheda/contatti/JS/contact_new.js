$(function () {

})

function goBackContatti() {
    setTimeout(() => {
        let url = $('#url').val();

        window.location.href = url;
    }, 2000)
}