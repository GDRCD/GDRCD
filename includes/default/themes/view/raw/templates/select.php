
<option value=""></option>
<?php foreach($data['options'] as $option){?>
        <option value="<?=$option[$data['value_cell']];?>" <?=($option[$data['value_cell']] == $data['selected_value']) ? 'selected' : ''?>>
            <?=$option[$data['name_cell']];?>
        </option>
<?php } ?>