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
        $gathering_rarity,
        $gathering_ability,
        $gathering_ability_esclusive;


    public function __construct()
    {
        parent::__construct();

        # Gli esiti sono attivi in chat?
        $this->gathering_enabled = Functions::get_constant('GATHERING_ENABLE');
        $this->gathering_rarity = Functions::get_constant('GATHERING_RARITY');
        $this->gathering_ability = Functions::get_constant('GATHERING_ABILITY');
        $this->gathering_ability_esclusive = Functions::get_constant('GATHERING_ABILITY_ESCLUSIVE');

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
    public function gatheringAbility(): bool
    {
        return $this->gathering_ability;
    }
    public function gatheringAbilityEsclusive(): bool
    {
        return $this->gathering_ability_esclusive;
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

    /**** ROUTING ***/

    /**
     * @fn loadManagementGatheringItemPage
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementGatheringPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'gathering_chat_list.php';
                break;

            case 'new':
                $page = 'gathering_new.php';
                break;

            case 'edit':
                $page = 'gathering_edit.php';
                break;
        }

        return $page;
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

    /*** TABLES HELPERS ***/

    /**
     * @fn getAllGathering
     * @note Ottiene la lista degli esiti
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllGathering(string $val = '*', string $order = '')
    {
        $where = ($this->gatheringManage()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM gathering WHERE {$where} {$order}", 'result');
    }




}