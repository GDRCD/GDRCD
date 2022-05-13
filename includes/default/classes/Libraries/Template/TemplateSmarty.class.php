<?php

class TemplateSmarty extends Template
{
    private $smarty;

    public function startTemplate(): Template
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(__DIR__ . '/../../../themes/advanced/view/smarty/templates');
        $this->smarty->setConfigDir(__DIR__.'/../../../themes/advanced/view/smarty/config');
        $this->smarty->setCompileDir(__DIR__ . '/../../../themes/advanced/view/smarty/compiled');
        $this->smarty->setCacheDir(__DIR__.'/../../../themes/advanced/view/smarty/cache');
        return $this;
    }

    public function render($file_name,$data): string
    {
        foreach ($data as $index => $value){
            $this->smarty->assign($index, $value);
        }

        if(file_exists($this->smarty->getTemplateDir()[0].$file_name.'.tpl')){
            $render = $this->smarty->fetch($file_name.'.tpl');
        }
        else{
            $render = $this->renderRaw($file_name.'.php',$data);
        }

        return $render;
    }

    public function addValues($container, $values){

        $old_val = $this->smarty->getTemplateVars($container);


        if(!is_array($old_val) && !empty($old_val)){
            $old_val = [$old_val];
        }

        $old_val[] = $values;

        $this->smarty->assign($container,$old_val);
    }

    public function renderSelect($value_cell,$name_cell,$selected,$data): string
    {
        return $this->render('select',['options'=>$data,'value_cell'=>$value_cell,'name_cell'=>$name_cell,'selected_value'=>$selected]);
    }

    public function renderTable($body_file,$data): string
    {
        $this->smarty->assign('body',$body_file);
        return $this->render('table-content',$data);
    }

}