function updateExpTransactions(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.scheda_exp_box .scheda_exp_table').html(datas.new_template)
    }
}
