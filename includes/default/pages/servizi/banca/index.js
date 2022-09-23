function updateBank(data){

    if(data){
        let datas = JSON.parse(data);

        if(datas.response){

            if(datas.new_bank){
                $('.bank_totals .bank_count').html(datas.new_bank);
            }

            if(datas.new_money){
                $('.bank_totals .money_count').html(datas.new_money);
            }

        }

    }
}