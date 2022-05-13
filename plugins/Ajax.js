async function Ajax(path,data,success,type = 'POST'){

    data.path = path;

    $.ajax({
        url: 'core/wrapper_ajax.php',
        type: type,
        data: data,
        success: function (response) {

            if(success != false) {
                success(response);
            }
        }
    });
}
