class Swal {

    static fire(title, message, type) {
        swal({
            title: title,
            message: message,
            type: type
        });
    }

    static button(title, message, type, buttons, callback) {
        swal({
            text: title,
            message: message,
            type: type,
            buttons: buttons,
        })
            .then(value => {
                if (!value) throw null;

                callback()
            })
    }
}