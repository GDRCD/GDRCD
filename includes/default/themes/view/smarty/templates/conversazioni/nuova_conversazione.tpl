<div class="new_conversation">
    <form method="POST" class="ajax_form" action="conversazioni/ajax.php" data-callback="newConvSuccess"
          data-swal="false">
        <div class="single_input">
            <div class="label">Titolo</div>
            <input type="text" name="title" required>
        </div>

        <div class="single_input">
            <div class="label">Immagine</div>
            <input type="text" name="img" required>
        </div>

        <div class="single_input">
            <div class="label">Seleziona membri</div>
            <select name="members[]" class="chosen-select" multiple data-placeholder="Seleziona membri">
                {{$personaggi}}
            </select>
        </div>

        <div class="single_input submit">
            <button type="submit" title="Invia"><i class="fas fa-paper-plane"></i></button>
            <input type="hidden" name="action" value="new_conversation">
        </div>
    </form>
</div>