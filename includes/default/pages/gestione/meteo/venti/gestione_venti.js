$(function () {

  let editForm = $('.edit-form');

  new Form('.gestione_meteo_venti .form').onSubmit({
    path: 'gestione/meteo/venti/gestione_venti_ajax.php',
    success: updateFormList
  });

  function updateFormList(data) {
    if (data) {

      let datas = JSON.parse(data);

      if (datas.response) {

        $('.gestione_meteo_venti select[name="id"]').html(datas.meteo_venti)

      }

    }
  }


  editForm.find('select[name="id"]').on('change', function () {
    let id = $(this).val()
    Ajax(
      'gestione/meteo/venti/gestione_venti_ajax.php',
      {'id': id, 'action': 'get_vento_data'},
      function (data) {

        if (data != '') {
          let datas = JSON.parse(data);
          editForm.find('input[name="nome"]').val(datas.nome);
        }

      }
    )
  });
})