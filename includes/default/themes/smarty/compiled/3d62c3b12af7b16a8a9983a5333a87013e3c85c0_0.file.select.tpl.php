<?php
/* Smarty version 4.0.4, created on 2022-05-13 14:02:48
  from '/Users/giovannipane/Sites/GDRCD/includes/default/themes/view/smarty/templates/select.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.0.4',
  'unifunc' => 'content_627e6508dd72f9_70996495',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3d62c3b12af7b16a8a9983a5333a87013e3c85c0' => 
    array (
      0 => '/Users/giovannipane/Sites/GDRCD/includes/default/themes/view/smarty/templates/select.tpl',
      1 => 1652429908,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_627e6508dd72f9_70996495 (Smarty_Internal_Template $_smarty_tpl) {
?>
<option value=""></option>
<?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['options']->value, 'option');
$_smarty_tpl->tpl_vars['option']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['option']->value) {
$_smarty_tpl->tpl_vars['option']->do_else = false;
?>
    <option value="<?php ob_start();
echo $_smarty_tpl->tpl_vars['value_cell']->value;
$_prefixVariable1 = ob_get_clean();
ob_start();
echo $_smarty_tpl->tpl_vars['option']->value[$_prefixVariable1];
$_prefixVariable2 = ob_get_clean();
echo $_prefixVariable2;?>
" <?php ob_start();
echo $_smarty_tpl->tpl_vars['value_cell']->value;
$_prefixVariable3 = ob_get_clean();
ob_start();
echo $_smarty_tpl->tpl_vars['name_cell']->value;
$_prefixVariable4 = ob_get_clean();
if ($_smarty_tpl->tpl_vars['option']->value[$_prefixVariable3] == $_smarty_tpl->tpl_vars['selected_value']->value || $_smarty_tpl->tpl_vars['option']->value[$_prefixVariable4] == $_smarty_tpl->tpl_vars['selected_value']->value) {?> selected <?php }?> ><?php ob_start();
echo $_smarty_tpl->tpl_vars['name_cell']->value;
$_prefixVariable5 = ob_get_clean();
ob_start();
echo $_smarty_tpl->tpl_vars['option']->value[$_prefixVariable5];
$_prefixVariable6 = ob_get_clean();
echo $_prefixVariable6;?>
</option>
<?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
}
}
