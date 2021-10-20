<?php

/**
 * @class Condizioni
 * @note Classe per la gestione delle condizioni meteo
 * @required PHP 7.1+
 */
class Condizioni
{
  private $me,
      $permessi,
      $parameters;

  public function __construct()
  {
      global $PARAMETERS;
      $this->me = gdrcd_filter('in', $_SESSION['login']);
      $this->permessi = gdrcd_filter('num', $_SESSION['permessi']);
      $this->parameters = $PARAMETERS;
  }

    /**** CONTROLS ****/

    /**
     * @fniVisibility
     * @note Controlla se si hanno i permessi per guardarla
     * @return bool
     */
    public function Visibility(): bool
    {
         return ($this->permessi > MODERATOR);
    }
    /**
     * @fn getAll
     * @note Estrae lista delle condizioni meterologiche
     * @return array
     */
    public function getAll()
    {
        return gdrcd_query("SELECT * FROM meteo_condizioni", 'result');




    }
  
}
