class SwalWrapper {

    static fire(title, message, type) {
        return Swal.fire(title, message, type);
    }

    static button(title, message, type, buttons = false) {

        if (buttons === false) {
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

        return Swal.fire(title, message, type, {
            buttons: buttons
        }).then(value => {
            if (!value) throw null;

            return value;
        });
    }

    static async customForm(form,title) {
        return await Swal.fire({
            title: title,
            html: '<form id="swal_alert_form" class="form_container">' + form + '</form>',
            focusConfirm: false,
            preConfirm: () => {
                let data = $("#swal_alert_form").serializeArray();
                var returnArray = {};
                for (var i = 0; i < data.length; i++) {
                    returnArray[data[i]['name']] = data[i]['value'];
                }
                return returnArray;
            }
        });
    }
}