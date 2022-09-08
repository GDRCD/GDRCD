<div class="pagina_user_cambio_pass">

    <!-- Titolo della pagina -->
    <div class="page_title">
        <h2>{$pagetitle}</h2>
    </div>

    <div class="response status_success" style="display:none;">
        {include file="modificapassword/success-message.tpl"}
    </div>
    <div class="response status_error" style="display:none;">
        {include file="modificapassword/error-message.tpl"}
    </div>

    <!-- Box principale -->
    <div class="page_body">
        <div class="panels_box">
            <div class="form_gioco">
                <form class="form ajax_form" action="servizi/modificapassword/ajax.php" data-callback="formresponse">
                    <div class="form_label">
                        {$formlabel.email}
                    </div>
                    <div class="form_field">
                        <input name="email" />
                    </div>
                    <div class="form_label">
                        {$formlabel.oldpass}
                    </div>
                    <div class="form_field">
                        <input name="old_pass" />
                    </div>
                    <div class="form_label">
                        {$formlabel.newpass}
                    </div>
                    <div class="form_field">
                        <input name="new_pass" />
                    </div>
                    <div class="form_label">
                        {$formlabel.repeatpass}
                    </div>
                    <div class="form_field">
                        <input name="repeat_pass" />
                    </div>
                    <div class="form_submit">
                        <input type="hidden" name="op" value="new" />
                        <input type="submit" name="nulla" value="{$formlabel.submit}" />
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<script src="{Router::getPagesLink('servizi/modificapassword/formresponse.js')}"></script>