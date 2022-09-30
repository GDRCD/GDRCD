$(function(){

})

function searchSuccess(data){

    if(data){
        let datas= JSON.parse(data);
        $('#anagrafe .results_box').html(datas.search);
    }

}