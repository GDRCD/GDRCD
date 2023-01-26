async function Ajax(path, data, success, type = 'POST') {

    data.path = path;

    let response = $.ajax({
        url: 'core/wrapper_ajax.php',
        type: type,
        data: data,
        async: false,
        success: function (response) {

            if (success != false) {
               return success(response);
            }
        }
    });

    return JSON.parse(response.responseText);
}
