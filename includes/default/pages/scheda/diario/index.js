function updateDiaryList(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.scheda_diario_table').html(datas.new_template);
    }
}