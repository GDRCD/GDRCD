<?php

class Template extends BaseClass
{

    protected $engine;
    protected $used_class;

    /**
     * @fn __construct
     * @return Template
     */
    public function startTemplate(): Template
    {
        $this->engine = Functions::get_constant('TEMPLATE_ENGINE');

        switch ( $this->engine ) {
            case 'Smarty':
                $this->used_class = TemplateSmarty::getInstance()->startTemplate();
                break;
            case 'Plate':
                $this->used_class = TemplatePlate::getInstance()->startTemplate();
                break;
            default:
                die('TEMPLATE_ENGINE_NOT_EXIST');
                break;
        }

        return $this;
    }

    public function render($file_name, $data)
    {
        return $this->used_class->render($file_name, $data);
    }

    public function renderSelect($value_cell, $name_cell, $selected, $data)
    {
        return $this->used_class->renderSelect($value_cell, $name_cell, $selected, $data);
    }

    public function renderTable($body_file, $data)
    {
        return $this->used_class->renderTable($body_file, $data);
    }

    public function addValues($container, $values)
    {
        return $this->used_class->addValues($container, $values);
    }

    protected function renderRaw($file_name, $data)
    {
        $base_dir = __DIR__ . '/../../../themes/advanced/view/raw/templates/';

        if ( file_exists($base_dir . $file_name) ) {
            // Start output buffering
            ob_start();

            // Include the template file
            include($base_dir . $file_name);

            // End buffering and return its contents
            return ob_get_clean();
        } else {
            die('TEMPLATE_NOT_EXIST');
        }
    }

}