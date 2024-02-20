<div class="page_title">
    <h2>{$pagetitle}</h2>
</div>
<br>
<br>
<form action="{$formaction}" method="post">

    <div class="form_label">{$formlabel.newpass}</div>
    <div class="form_field"><input name="new_password" type="password" value="" required></div>

    <div class="form_label">{$formlabel.repeatpass}</div>
    <div class="form_field"><input name="confirm_password" type="password" value="" required></div>

    <div class="form_submit">
        <input type="hidden" name="token" value="{$token}">
        <input type="submit" value="{$formlabel.submit}">
    </div>

</form>