ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$('.ajax_form').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            try {
                const data = JSON.parse(response);
                if (data.response) {
                    alert(data.swal_message || 'Esperienza assegnata con successo!');
                    $('.ajax_form')[0].reset();
                    $('select[name="personaggio"]').val(null).trigger('change');
                } else {
                    alert(data.swal_message || 'Errore durante l\'assegnazione.');
                }
            } catch (e) {
                alert('Errore durante l\'elaborazione della risposta.');
            }
        },
        error: function () {
            alert('Errore di connessione al server.');
        },
    });
});
