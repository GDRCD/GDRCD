<?php

class SchedaContatti extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isPublic
     * @note Controlla se la scheda contatti' e' pubblica
     * @return bool
     */
    public function isPublic(): bool
    {
        return Functions::get_constant('CONTACT_PUBLIC');
    }

    /**
     * @fn isAccessible
     * @note Controlla se i contatti sono accessibili
     * @param int $id_pg
     * @return bool
     */
    public function isAccessible(int $id_pg): bool
    {
        return ($this->isPublic() ||  Personaggio::isMyPg($id_pg));
    }
    /*** INDEX ***/




}