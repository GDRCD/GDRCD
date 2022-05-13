<?php foreach ($data['body_rows'] as $row) { ?>
    <div class='tr <?= $row['closed_cls']; ?>'>
        <div class='td'><?= $row['date']; ?></div>
        <div class='td'><?= $row['author']; ?></div>
        <div class='td'><?= $row['master']; ?></div>
        <div class='td'><?= $row['titolo']; ?></div>
        <div class='td'><?= $row['totale_esiti']; ?></div>
        <div class='td'><?= ($row['new_response']) ? $row['new_response'] : 0; ?> </div>
        <div class='td commands'>
            <?php if ($row['esito_view_permission']) { ?>
                <a href='/main.php?page=<?= $data['
                   title='Leggi'>
                    <i class='fas fa-eye'></i>
                </a>
            <?php } ?>

            <?php if ($row['esito_membri_permission']) { ?>
                <a href=' / main . php ? page =<?= $data['
                   title='Gestisci membri'>
                    <i class='fas fa-users'></i>
                </a>
            <?php } ?>


            <?php if ($row['esito_manage'] && ($data['page'] == 'gestione')) { ?>
                <a href=' / main . php ? page =<?= $data['
                   title='Assegna Master'>
                    <i class='fas fa-user-tag'></i>
                </a>
            <?php } ?>

            <?php if ($row['esito_manage'] && ($data['page'] == 'gestione') && $row['closed']) { ?>
                <a class='ajax_link' data-id='<?= $row['id']; ?>' data-action='open' href='#' title='Riapri'>
                    <i class='far fa-check-circle'></i>
                </a>
            <?php } ?>

            <?php if ($row['esito_manage'] && ($data['page'] == 'gestione') && !$row['closed']) { ?>
                <a class='ajax_link' data-id='<?= $row['id']; ?>' data-action='close' href='#' title='Chiudi'>
                    <i class='far fa-times-circle'></i>
                </a>
            <?php } ?>
        </div>
    </div>
<?php } ?>