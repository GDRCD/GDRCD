function Ajax(path,data,success,type = 'get'){
    $.ajax({
        url: path,
        type: type,
        data: data,
        success: function (response) {

            if(success != false) {
                success(response);
            }
        }
    });
}
