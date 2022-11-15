<?php

class Template extends BaseClass
{

    protected mixed $engine;
    protected mixed $used_class;

    /**
     * @fn __construct
     * @return Template
     * @throws Throwable
     */
    public function startTemplate(): Template
    {
        $this->engine = Functions::get_constant('TEMPLATE_ENGINE');

        if ( class_exists($this->engine) ) {
            $this->used_class = $this->engine::getInstance()->startTemplate();
        }

        return $this;
    }

    public function render($file_name, $data)
    {
        return $this->used_class->render($file_name, $data);
    }

    public function renderSelect($value_cell, $name_cell, $selected, $data, $label = '')
    {
        return $this->used_class->renderSelect($value_cell, $name_cell, $selected, $data, $label);
    }

    public function renderTable($body_file, $data)
    {
        return $this->used_class->renderTable($body_file, $data);
    }

    public function addValues($container, $values)
    {
        return $this->used_class->addValues($container, $values);
    }

    protected function renderRaw($file_name)
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