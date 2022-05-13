<?php
/* Smarty version 4.0.4, created on 2022-05-13 10:57:43
  from '/Users/giovannipane/Sites/GDRCD/includes/default/themes/view/smarty/templates/meteo/meteo.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.0.4',
  'unifunc' => 'content_627e39a75704b6_26151132',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '7a9e98e15ef814d7c7a5e482fbfbabbe4ff6e2af' => 
    array (
      0 => '/Users/giovannipane/Sites/GDRCD/includes/default/themes/view/smarty/templates/meteo/meteo.tpl',
      1 => 1652429908,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_627e39a75704b6_26151132 (Smarty_Internal_Template $_smarty_tpl) {
if ((isset($_smarty_tpl->tpl_vars['moon']->value))) {?>
    <img title="<?php ob_start();
echo $_smarty_tpl->tpl_vars['moon']->value['Title'];
$_prefixVariable1 = ob_get_clean();
echo $_prefixVariable1;?>
" src="<?php ob_start();
echo $_smarty_tpl->tpl_vars['moon']->value['Img'];
$_prefixVariable2 = ob_get_clean();
echo $_prefixVariable2;?>
" alt="<?php ob_start();
echo $_smarty_tpl->tpl_vars['moon']->value['Title'];
$_prefixVariable3 = ob_get_clean();
echo $_prefixVariable3;?>
">
<?php }
if ((isset($_smarty_tpl->tpl_vars['meteo']->value))) {?>
    <div class="meteo">
        Meteo: <br>
        <?php ob_start();
echo $_smarty_tpl->tpl_vars['meteo']->value['temp'];
$_prefixVariable4 = ob_get_clean();
echo $_prefixVariable4;?>
C - <?php ob_start();
echo $_smarty_tpl->tpl_vars['meteo']->value['vento'];
$_prefixVariable5 = ob_get_clean();
echo $_prefixVariable5;?>
 <br>

        <?php if ($_smarty_tpl->tpl_vars['meteo']->value['img']) {?>
            <img alt="<?php ob_start();
echo $_smarty_tpl->tpl_vars['meteo']->value['meteo'];
$_prefixVariable6 = ob_get_clean();
echo $_prefixVariable6;?>
" src="<?php ob_start();
echo $_smarty_tpl->tpl_vars['meteo']->value['img'];
$_prefixVariable7 = ob_get_clean();
echo $_prefixVariable7;?>
" style="width: 20px">
        <?php }?>
         <?php ob_start();
echo $_smarty_tpl->tpl_vars['meteo']->value['meteo'];
$_prefixVariable8 = ob_get_clean();
echo $_prefixVariable8;?>

    </div>
<?php }
}
}
