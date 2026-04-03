<div class="password_update_page">
    <!-- Titolo della pagina -->
    <div class="general_title">{$pagetitle}</div>

    <!-- Box principale -->
    <div class="page_body">

        <div class="form_container">

            <form class="form ajax_form" action="utenti/email/ajax.php">

                <div class="single_input">
                    <div class="label">{$formlabel.email}</div>
                    <input type="email" name="email"/>
                </div>

                <div class="single_input">
                    <div class="label">{$formlabel.password}</div>
                    <input type="password" name="password"/>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="email_update"/>
                    <input type="submit" name="nulla" value="{$formlabel.submit}"/>
                </div>
            </form>
        </div>
    </div>
</div>