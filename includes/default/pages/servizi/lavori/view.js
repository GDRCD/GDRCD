function updateWorksTable(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.works_list').html(datas.works_table)
    }
}