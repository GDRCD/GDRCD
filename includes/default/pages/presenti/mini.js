function updatePresentiMini() {

    Ajax('presenti/ajax.php',{'action':'get_presences'},function(data){

        if(data){

            let datas = JSON.parse(data);

            if(datas.template){
                $('.presenti_mini_box .box_presenti').html(datas.template);
            }

            if(datas.counter){
                $('.presenti_mini_box .presence_counter').html(datas.counter);
            }

        }


    } );

}

setInterval(updatePresentiMini,5000);