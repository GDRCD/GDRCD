<div class="password_update_page">
    <!-- Titolo della pagina -->
    <div class="general_title">{$pagetitle}</div>

    <!-- Box principale -->
    <div class="page_body">

        <div class="form_container">

            <form class="form ajax_form" action="utenti/password/ajax.php">

                <div class="single_input">
                    <div class="label">{$formlabel.email}</div>
                    <input type="email" name="email"/>
                </div>

                <div class="single_input">
                    <div class="label">{$formlabel.oldpass}</div>
                    <input type="password" name="old_pass"/>
                </div>


                <div class="single_input">
                    <div class="label">{$formlabel.newpass}</div>
                    <input type="password" name="new_pass"/>
                </div>


                <div class="single_input">
                    <div class="label">{$formlabel.repeatpass}</div>
                    <input type="password" name="repeat_pass"/>
                </div>

                <div class="single_input">
                    <input type="hidden" name="action" value="password_update"/>
                    <input type="submit" name="nulla" value="{$formlabel.submit}"/>
                </div>
            </form>
        </div>
    </div>
</div>