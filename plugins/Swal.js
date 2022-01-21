class Swal {

    static fire(title, message, type) {
        swal(title,message,type);
    }

    static button(title, message, type, buttons) {
        return swal(title, message, type, {
            buttons: buttons
        }).then(value => {
                if (!value) throw null;

                return value;
            });
    }
}