<div class="pagina_link_menu">

    <div class="page_title">
        <h2>Menù rapido</h2>
    </div>

    <div class="page_body">
        <?= Menu::getInstance()->createMenuPlain('Rapido'); ?>
    </div>

    <div class="page_title">
        <h2>Azioni</h2>
    </div>
    <div class="page_body">
        <div class="menu_box">

            <a href="/main.php?dir=<?= Session::read('luogo'); ?>">
                <div class="single_menu">
                    Aggiorna
                </div>
            </a>

            <a href="/main.php?page=mappaclick&map_id=<?= Session::read('mappa'); ?>">
                <div class="single_menu">
                    Mappa
                </div>
            </a>

            <a href="/logout.php">
                <div class="single_menu">
                    Logout
                </div>
            </a>
        </div>

    </div>
</div>