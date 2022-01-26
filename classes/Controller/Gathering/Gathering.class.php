<?php

/**
 * @class Gathering
 * @note Classe per la gestione centralizzata del gathering
 * @required PHP 7.1+
 * Tabelle:
-> gathering
-> gathering_categorie
-> gathering_oggetti
-> log_gathering

Config:
-> NUM_MAX_AZIONI (1 minimo)
-> NUM_AZIONI_RAND (si/no)
-> TIME_RESET_AZIONI
-> TIPO_DROP (pulsante/automatico)

Permessi :
-> MANAGE_GATHERING (per pannello gestionale)

Specifiche:
-> Sistema di acquisizione oggetti in modo random che può essere tramite pulsante singolo (drop singolo su mappa principale, con possibilità di aggiungerne più di uno), sulla mappa e/o in chat; oppure gestito tramite un numero di azioni eseguite in chat (solo stringe azioni/parlato con un tot di caratteri).
->la tabella gathering_oggetti conterrà l'elenco degli oggetti (suddivisi in categorie) similmente agli oggetti del mercato.
-> la tabella gathering_categorie servirà per suddividere gli oggetti, in modo da agevolarne la ricerca in fase di inserimento
 */
class Gathering extends BaseClass
{
    private
        $gathering_enabled,
        $gathering_rarity;


    public function __construct()
    {
        parent::__construct();

        # Gli esiti sono attivi in chat?
        $this->gathering_enabled = Functions::get_constant('GATHERING_ENABLE');
        $this->gathering_rarity = Functions::get_constant('GATHERING_RARITY');

    }

    /*** GETTER */

    public function gatheringEnabled(): bool
    {
        return $this->gathering_enabled;
    }
    public function gatheringRarity(): bool
    {
        return $this->gathering_rarity;
    }

    /*** PERMISSIONS */

    /**
     * @fn gatheringManage
     * @note Controlla se si hanno i permessi per gestire il Gathering
     * @return bool
     */
    public function gatheringManage(): bool
    {
        return Permissions::permission('MANAGE_GATHERING');
    }
    /*** GET ONE CATEGORY ***/

    /**
     * @fn getoneGatheringCat
     * @note Get di una categoria
     * @param array $post
     * @return array
     */
    public function getoneGatheringCat(int $id)
    {
        $id = Filters::in($id);

        return DB::query("SELECT * FROM gathering_cat WHERE id ='{$id}'");
    }
    /*** GET ONE ITEM ***/

    /**
     * @fn getoneGatheringItem
     * @note Get di una categoria
     * @param array $post
     * @return array
     */
    public function getoneGatheringItem(int $id)
    {
        $id = Filters::in($id);

        return DB::query("SELECT * FROM gathering_item WHERE id ='{$id}'");
    }




}