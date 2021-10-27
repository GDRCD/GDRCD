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