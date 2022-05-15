<?php


class Scheda extends BaseClass
{


    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @fn available
     * @note Controlla se una scheda e' accessibile.
     * @param $id_pg
     * @return bool
     */
    public function available($id_pg): bool
    {
        $id_pg = Filters::int($id_pg);
        return (Personaggio::pgExist($id_pg));
    }

}