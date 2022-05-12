<?php if (isset($data['moon'])) { ?>
    <img title="<?= $data['moon']['Title']; ?>" src="<?= $data['moon']['Img']; ?>" alt="<?= $data['moon']['Title']; ?>">
<?php } ?>


<?php if (isset($data['meteo'])) { ?>
    <div class="meteo">
        Meteo: <br>
        <?= $data['meteo']['temp']; ?>C - <?= $data['meteo']['vento']; ?> <br>


        <?php if (isset($data['meteo']['img'])) { ?>
            <img alt="<?= $data['meteo']['meteo']; ?>" src="<?= $data['meteo']['img']; ?>" style="width: 20px">
        <?php } ?>

        <?= $data['meteo']['meteo']; ?>

    </div>

<?php } ?>
