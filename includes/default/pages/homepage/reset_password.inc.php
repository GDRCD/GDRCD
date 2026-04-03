<strong>
    <?php echo gdrcd_filter('out', $MESSAGE['homepage']['forms']['forgot']); ?>
</strong>

<div class="pass_rec">

    <div class="form_container">
        <form class="form ajax_form" action="homepage/password/ajax.php">
            <div class="single_input">
                <div class="label">Email</div>
                <input type="text" name="email"/>
            </div>
            <div class="single_input">
                <input type="hidden" name="action" value="password_recovery"/>
                <input type="submit" value="Invia password"/>
            </div>
        </form>
    </div>

</div>