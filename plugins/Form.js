class Form {

    swal_alert = true;
    form_reset = true;
    selector = false;

    constructor(selector) {
        this.selector = selector;
    }

    setOptions(options){
        this.swal_alert = (options.swal_alert !== undefined) ? options.swal_alert : this.swal_alert;
        this.form_reset = (options.form_reset !== undefined) ? options.form_reset : this.form_reset;
    }

    onSubmit(options) {
        let cls = this,
            selector = cls.selector;

        $(selector).on('submit', async function (e) {
            e.preventDefault();

            await cls.setOptions(options);

            let path = options.path,
                success = (options.success) ? options.success : false,
                form = this,
                accepted = true;

            //*** SWAL CONFIRM
            if (cls.swal_alert) {
                accepted = await Swal.button(
                    'Confermi l\'invio del form?',
                    '',
                    'info',
                    {
                        catch: {
                            text: "No",
                            value: false,
                        },
                        confirm: {
                            text: "Si",
                            value: true,
                        }
                    });
            }

            if (accepted) {
                await cls.sendData(form, path, success);
            }
        });
    }

    sendData(form, path, success) {
        //*** SEND AJAX FORM
        let data = new FormData(form),
            cls = this;

        $.ajax({
            url: path,
            type: "post",
            data: data,
            contentType: false,
            processData: false,
            success: async function (data) {

                //*** CALLBACK
                if (success != false) {
                    success(data);
                }

                //**** RESET
                if (cls.form_reset) {
                    await cls.FormReset(form);
                }

                //*** SWAL ALERT
                if (cls.swal_alert) {

                    let json = JSON.parse(data);

                    if (json.swal_title) {

                        await Swal.fire(
                            json.swal_title,
                            (json.swal_message) ? json.swal_message : '',
                            (json.swal_type) ? json.swal_type : ''
                        )

                    }
                }
            }
        });
    }

    FormReset(form) {
        $(form).find('input[type="file"]').val('');
        $(form).find('input[type="number"]').val('');
        $(form).find('input[type="text"]').val('');
        $(form).find('input[type="checkbox"]').prop('checked', false);
        $(form).find('select').val('');
        $(form).find('textarea').val('');
    }

}