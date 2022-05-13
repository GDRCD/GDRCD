$(function () {

  new Form('.new_season .form').onSubmit({
    path: '/pages/gestione/meteo/stagioni/gestione_stagioni_ajax.php',
    success: redirectForm,
  });

  function redirectForm() {
    setTimeout(() => {
        window.location.href = 'main.php?page=gestione_meteo_stagioni'
      }, 2000)
  }
})