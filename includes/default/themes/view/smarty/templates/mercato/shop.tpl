{foreach $data as $shop}
    <div class='single_shop'>
        <div class='shop_img'><img src='/themes/advanced/imgs/shops/{{$shop.img}}'></div>
        <div class='shop_name'>
            <a href='/main.php?page=servizi_mercato&op=objects&shop={{$shop.id}}'>{{$shop.nome}}</a>
        </div>
    </div>
{/foreach}