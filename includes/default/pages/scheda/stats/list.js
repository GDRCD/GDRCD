function updateStats(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.stats_list .scheda_stat_table').html(datas.new_template)
    }
}
