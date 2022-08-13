function updateRegistrazioniTable(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.scheda_registrazioni_table').html(datas.new_template)
    }
}
