function updateRegistrazioniTable(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.manage_registrazioni_table.table_new').html(datas.new_template)
        $('.manage_registrazioni_table.table_blocked').html(datas.blocked_template)
        $('.manage_registrazioni_table.table_controlled').html(datas.completed_template)
    }
}
