<?php

/**
 * @class Contacts
 * @note Classe per la gestione centralizzata dei contatti del personaggio
 * @required PHP 7.1+
 */
class Contacts extends BaseClass
{

    protected
        $con_enabled,
        $con_public,
        $con_secret,
        $con_categories,
        $con_categories_public,
        $con_categories_staff;
    public function __construct()
    {
        parent::__construct();

        #Attiva/Disattiva i contatti
        $this->con_enabled = Functions::get_constant('CONTACTS_ENABLED');

        # Se pubblica vedi TUTTO a prescindere (ignori il campo "pubblica" nella tabella contatti_nota), altrimenti ti rifai alla scelta del pg - default true
        $this->con_public = Functions::get_constant('CONTACT_PUBLIC');

        # Se true, a prescindere dalla scelta del pg sulla nota, sono SEMPRE visibili solo a chi ha il permesso VIEW_CONTACTS ed al pg stesso, se false si rifa' a CONTACT_PUBLIC - default false
        $this->con_secret = Functions::get_constant('CONTACT_SECRETS');

        # attivi/disattivi le categorie contatto - default true
        $this->con_categories = Functions::get_constant('CONTACT_CATEGORIES');

        # Se si, tutti vedono le categorie, altrimenti solo chi ha il permesso VIEW_CONTACTS_CATEGORIES - default true
        $this->con_categories_public = Functions::get_constant('CONTACT_CATEGORIES_PUBLIC');

        # Solo lo staff puo' assegnare le categorie di contatto, true/false - default false
        $this->con_categories_staff = Functions::get_constant('CONTACT_CATEGORIES_STAFF_ONLY');
    }

    /**** ROUTING ***/

    /**
     * @fn loadManagementContactPage
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementContactPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'list.php';
                break;

            case 'new':
                $page = 'new.php';
                break;

            case 'edit':
                $page = 'edit.php';
                break;
        }

        return $page;
    }

    /**** CONTROLS ****/

    /**
     * @fn extraActive
     * @note Controlla se è attiva la funzionalità dei contatti
     * @return bool
     */
    public function contatcEnables(): bool
    {
        return $this->con_enabled;
    }
    /*** PERMISSIONS */

    /**
     * @fn contactManage
     * @note Controlla se si hanno i permessi per gestire i contatti
     * @return bool
     */
    public function contactManage(): bool
    {
        return Permissions::permission('MANAGE_CONTACT');
    }


    /*** TABLES HELPERS ***/

    /**
     * @fn getAllGatheringChat
     * @note Ottiene delle chat che hanno degli oggetti droppabili
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllContact(string $val = '*', string $pg)
    {
        $where = ($this->contactManage()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM contatti WHERE personaggio = '{$pg}' ", 'result');
    }

    /*** CONTACT INDEX ***/

    /**
     * @fn ContactList
     * @note Render html della lista dei contatti
     * @return string
     */

    public function ContactList($pg): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllContact( 'contatto, categoria, id', $pg);
        return $template->renderTable(
            'scheda/contatti/list',
            $this->renderContactList($list, 'scheda')
        );
    }

    /**
     * @fn renderContactList
     * @note Render html lista contatti pg
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderContactList(object $list, string $page): array
    {
        $row_data = [];
        $path =  'scheda_contatti';
        $backlink = 'scheda';
        foreach ($list as $row) {
            $id=Filters::in($row['id']);

            $nome = Filters::in($row['nome']);
            $categoria = Filters::in($row['categoria']);

            $array = [
                'id'=>$id,

                'nome' => $nome,
                'categoria'=>$categoria,

                'contatti_view_permission'=> $this->contactManage()

            ];

            $row_data[] = $array;
        }

        $cells = [

            'Nome',
            'Categoria',
            'Controlli'
        ];
        $links = [
            ['href' => "/main.php?page={$path}&op=new", 'text' => 'Nuovo contatto']
          //  ['href' => "/main.php?page={$backlink}", 'text' => 'Indietro']
        ];

        return [
            'body' => 'scheda/contatti/list',
            'body_rows'=> $row_data,
            'cells' => $cells,
            'links' => $links,
            'path'=>$path,
            'page'=>$page

        ];
    }
    /***** LISTS *****/

    /**
     * @fn listPG
     * @note Ritorna la lista dei pg registrati escludendo quelli già presenti fra i contatti e l'utente stesso
     * @return string
     */
    function getAllPG($id_pg){

        return DB::query("SELECT id, nome FROM personaggio WHERE id != '{$id_pg}' ", 'result');


    }

    public function listPG($selected = 0, $id_pg)
    {
        echo $id_pg;
        $html = '<option value="0"></option>';
        $pg = $this->getAllPG($id_pg);

        foreach ($pg as $personaggi) {
            $nome = Filters::out($personaggi['nome']);
            $id = Filters::int($personaggi['id']);
            $sel = ($id == $selected) ? 'selected' : '';
            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }


}
