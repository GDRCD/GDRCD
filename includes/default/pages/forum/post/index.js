function updatePosts(data){

    if(data != ''){

        let datas = JSON.parse(data);

        if(datas.response){
            $('.forum_post').html(datas.new_view);
        }

    }

}