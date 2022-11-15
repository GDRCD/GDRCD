<?php

class SchedaContatti extends Scheda
{

    /**** CONTROLS ****/

    /**
     * @fn isAccessible
     * @note Controlla se i contatti sono accessibili
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public function isAccessible(int $pg): bool
    {
        return (Contatti::getInstance()->contactPublic() || Contatti::getInstance()->contactView($pg));
    }

}