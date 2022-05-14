$(function(){

  /*new Form('.gestione_stagioni_condizioni .form').onSubmit({
    path: 'gestione/meteo/stagioni/gestione_stagioni_ajax.php',
    success: refreshPage,
  });*/

})

function refreshStagioniTable(data) {

  if(data){
    let datas = JSON.parse(data);
    if(datas.stagioni_conditions) {
      $('.stagioni-condizioni-table').html(datas.stagioni_conditions)
    }
  }

}