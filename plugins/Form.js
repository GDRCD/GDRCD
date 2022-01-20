function Form(selector, path, success = false, swal_alert = true) {

    $(selector).on('submit', function (e) {
        e.preventDefault();

        let main = this;

        //*** SWAL CONFIRM
        Swal.button(
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
            },
            function () {

                //*** SEND AJAX FORM
                let data = new FormData(main);

                $.ajax({
                    url: path,
                    type: "post",
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function (data) {

                        //*** CALLBACK
                        if (success != false) {
                            success(data);
                        }

                        //*** SWAL ALERT
                        if (swal_alert) {

                            let json = JSON.parse(data);

                            if (json.swal_title) {

                                Swal.fire(
                                    json.swal_title,
                                    (json.swal_message) ? json.swal_message : '',
                                    (json.swal_type) ? json.swal_type : ''
                                )

                            }
                        }
                    }
                });
            }
        )

    });
}