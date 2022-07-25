$(function () {

})

function refreshStagioniTable(data) {

    if (data) {
        let datas = JSON.parse(data);
        if (datas.stagioni_conditions) {
            $('.stagioni-condizioni-table').html(datas.stagioni_conditions)
        }
    }

}