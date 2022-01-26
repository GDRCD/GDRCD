<?php

class TemplateSmarty extends Template
{
    private $smarty;

    public function startTemplate(): TemplateSmarty
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(__DIR__.'/../../../themes/advanced/view/smarty/templates');
        $this->smarty->setConfigDir(__DIR__.'/../../../themes/advanced/view/smarty/config');
        $this->smarty->setCompileDir(__DIR__.'/../../../themes/advanced/view/smarty/compiled');
        $this->smarty->setCacheDir(__DIR__.'/../../../themes/advanced/view/smarty/cache');
        return $this;
    }

    public function render($file_name,$data): string
    {
        foreach ($data as $index => $value){
            $this->smarty->assign($index, $value);
        }

        if(file_exists($this->smarty->getTemplateDir()[0].$file_name)){
            $render = $this->smarty->fetch($file_name);
        }
        else{
            $real_file_name = basename($file_name, '.tpl');
            $this->renderRaw($real_file_name,$data);
            return '';
        }

        return $render;
    }

    public function renderSelect($value_cell,$name_cell,$selected,$data): string
    {
        $this->smarty->assign('value_cell',$value_cell);
        $this->smarty->assign('name_cell',$name_cell);
        $this->smarty->assign('selected_value',$selected);
        return $this->render('select.tpl',['options'=>$data]);
    }

}