function Form(selector,path,success=false) {

  $(selector).on('submit', function (e) {
    e.preventDefault();

    let data = new FormData(this);

    $.ajax({
      url: path,
      type: "post",
      data: data,
      contentType: false,
      processData: false,
      success: function (data) {

        if(success != false) {
          success(data);
        }
      }
    });
  });
}

function CheckingNull(selector) {

  $('#'+selector+' input[type="file"]').val('');
  $('#'+selector+' input[type="number"]').val('');
  $('#'+selector+' input[type="text"]').val('');
  $('#'+selector+' input[type="checkbox"]').prop('checked',false);
  $('#'+selector+' select').val('');
  $('#'+selector+' textarea').val('');

}
