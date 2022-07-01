class Swal {

    static fire(title, message, type) {
        new swal(title,message,type);
    }

    static button(title, message, type, buttons = false) {

        if(buttons === false){
            buttons = {
                no: {
                    text: 'No',
                    value: false
                },
                confirm: {
                    text: 'Si',
                    value: true
                }
            }
        }

        return new swal(title, message, type, {
            buttons: buttons
        }).then(value => {
                if (!value) throw null;

                return value;
            });
    }
}