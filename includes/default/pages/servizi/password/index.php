
<?php Router::loadRequired(); ?>

<div class="password_update_page">
    <!-- Titolo della pagina -->
    <div class="general_title">
        Modifica Password
    </div>

    <!-- Box principale -->
    <div class="page_body">

        <div class="form_container">

            <form class="form ajax_form" action="servizi/password/ajax.php">

                <div class="single_input">
                    <div class="label">Email</div>
                    <input type="email" name="email"/>
                </div>

                <div class="single_input">
                    <div class="label">Vecchia password</div>
                    <input type="password" name="old_pass"/>
                </div>


                <div class="single_input">
                    <div class="label">Nuova password</div>
                    <input type="password" name="new_pass"/>
                </div>


                <div class="single_input">
                    <div class="label">Ripeti nuova password</div>
                    <input type="password" name="repeat_pass"/>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="update_password"/>
                    <input type="submit" name="nulla" value="Modifica"/>
                </div>
            </form>
        </div>
    </div>
</div>