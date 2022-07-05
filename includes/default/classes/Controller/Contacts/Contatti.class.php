<?php

/**
 * @class Contatti
 * @note Classe per la gestione centralizzata dei contatti del personaggio
 * @required PHP 7.1+
 */
class Contatti extends BaseClass
{
    protected
        $con_enabled,
        $con_public,
        $con_secret,
        $con_categories,
        $con_categories_public,
        $con_categories_staff;

    /**
     * @fn __construct
     * @note construct function
     * @return void
     */
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
            case 'view':
                $page = 'view.php';
                break;
            case 'new_nota':
                $page = 'new_nota.php';
                break;
            case 'dettaglio_nota':
                $page = 'dettaglio_nota.php';
                break;
        }

        return $page;
    }

    /**** CONTROLS ****/

    /**
     * @fn contactEnables
     * @note Controlla se i contatti sono abilitati
     * @return bool
     */
    public function contactEnables(): bool
    {
        return $this->con_enabled;
    }

    /**
     * @fn contactPublic
     * @note Controlla se i contatti sono pubblici
     * @return bool
     */
    public function contactPublic(): bool
    {
        return $this->con_public;
    }

    /**
     * @fn contactSecret
     * @note Controlla se i contatti sono segreti agli utenti
     * @return bool
     */
    public function contactSecret(): bool
    {
        return $this->con_secret;
    }

    /**
     * @fn contactCategories
     * @note Controlla se le categorie di contatti sono abilitate
     * @return bool
     */
    public function contactCategories(): bool
    {
        return $this->con_categories;
    }

    /**
     * @fn contactCategoriesPublic
     * @note Controlla se le categorie sono pubbliche
     * @return bool
     */
    public function contactCategoriesPublic(): bool
    {
        return $this->con_categories_public;
    }

    /**
     * @fn contactCategoriesStaff
     * @note Controlla se le categorie sono assegnabili solo dallo staff o meno
     * @return bool
     */
    public function contactCategoriesStaff(): bool
    {
        return $this->con_categories_staff;
    }

    /*** PERMISSIONS */

    /**
     * @fn contactView
     * @note Controlla se si hanno i permessi per vedere i contatti o se sono i propri
     * @param int $pg
     * @return bool
     */
    public function contactView(int $pg): bool
    {
        return (Personaggio::isMyPg($pg) || Permissions::permission('VIEW_CONTACTS'));
    }

    /**
     * @fn contactUpdate
     * @note Controlla se si hanno i permessi per aggiornare le note dei contatti propri o altrui
     * @param int $pg
     * @return bool
     */
    public function contactUpdate(int $pg): bool
    {
        return (Personaggio::isMyPg($pg) || Permissions::permission('UPDATE_CONTACTS'));
    }

    /**
     * @fn contactDelete
     * @note Controlla se si hanno i permessi per eliminare i contatti
     * @param int $pg
     * @return bool
     */
    public function contactDelete(int $pg): bool
    {
        return (Personaggio::isMyPg($pg) || Permissions::permission('DELETE_CONTACTS'));
    }

    /**
     * @fn contactPublic
     * @note Controlla se le categorie sono Pubbliche, altrimenti solo chi ha il permesso può vederle
     * @return bool
     */
    public function categoriePublic(): bool
    {
        return ($this->contactCategoriesPublic() || Permissions::permission('VIEW_CONTACTS_CATEGORIES'));
    }

    /**
     * @fn contactStaff
     * @note Controlla se le categorie sono visibili allo staff e se si hanno i permessi per vederle
     * @return bool
     */
    public function categoriesStaff(): bool
    {
        return ($this->contactCategoriesStaff() || Permissions::permission('VIEW_CONTACTS_CATEGORIES'));
    }

    /*** TABLES HELPERS ***/

    /**
     * @fn getAllCharacterContact
     * @note Query di tutti i contatti di un personaggio
     * @param string $val
     * @param int $pg
     * @return bool|int|mixed|string
     */
    public function getAllCharacterContact(int $pg, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM contatti WHERE personaggio = '{$pg}'  ", 'result');
    }

    /**
     * @fn getContact
     * @note Ottiene il singolo contatto
     * @param string $val
     * @param int $id
     * @return bool|int|mixed|string
     */
    public function getContact(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM contatti WHERE id = '{$id}' ");
    }

    /*** CONTACT INDEX ***/

    /**
     * TODO CONTROLLARE
     * @fn renderContactList
     * @note Render html lista contatti pg
     * @param object $list
     * @param string $page
     * @param int $id_pg
     * @return array
     */
    public function renderContactList(object $list, string $page, int $id_pg): array
    {
        $row_data = [];
        $path = 'scheda_contatti';
        $pg = Personaggio::nameFromId($id_pg);
        foreach ($list as $row) {
            $id = Filters::in($row['id']);

            $id_contatto = Filters::in($row['contatto']);
            $id_categoria = Filters::in($row['categoria']);
            $categoria = ContattiCategorie::getInstance()->getCategory($id_categoria, 'nome');

            $contatto = Personaggio::nameFromId($id_contatto);
            $pop_up_modifica = 'javascript:modalWindow("edit", "Modifica Contatto","popup.php?page=scheda_contatti_nota&id=' . $id . '&op=edit") ';


            $array = [
                'id' => $id,
                'contatto' => $contatto,
                'categoria' => $categoria['nome'],
                'contatti_view_permission' => $this->contactView($id_pg),
                'contatti_update_permission' => $this->contactUpdate($id_pg),
                'contatti_delete_permission' => $this->contactDelete($id_pg),
                'contatti_categories_enabled' => $this->contactCategories(),
                'contatti_categories_public' => $this->categoriePublic(),
                'contatti_categories_staff' => $this->categoriesStaff(),
                'pop_up_modifica' => $pop_up_modifica,
                'id_pg' => $id_pg,
                'pg' => $pg

            ];

            $row_data[] = $array;
        }

        $cells = [

            'Nome',
            'Categoria',
            'Controlli'
        ];
        $links = [
            //  ['href' => "/main.php?page={$path}&op=new&id_pg={$id_pg}&pg={$pg}", 'text' => 'Nuovo contatto']
            // ['href' => "/main.php?page={$backlink}&id_pg={$id_pg}&pg={$pg}", 'text' => 'Indietro']
        ];

        return [
            'body' => 'scheda/contatti/list',
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
            'path' => $path,
            'page' => $page

        ];
    }

    /***** LISTS *****/

    /**
     * @fn ContactList
     * @note Render html della lista dei contatti
     * @param int $pg
     * @return string
     */
    public function ContactList(int $pg): string
    {
        $pg = Filters::int($pg);
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllCharacterContact($pg, 'contatto, categoria, id');
        return $template->renderTable(
            'scheda/contatti/list',
            $this->renderContactList($list, 'scheda', $pg)
        );
    }

    /**
     * @fn listContactCategories
     * @note Crea le option con le categorie
     * @return string
     */
    public function listContactCategories(): string
    {
        $categorie = ContattiCategorie::getInstance()->getAllCategories();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $categorie);
    }

    /**
     * @fn contattiPresenti
     * @note Estrai gli ID di tutti i contatti che il pg ha già inserito
     * @param $pg
     * @return string
     */
    public function contattiPresenti($pg): string
    {
        $pg = Filters::int($pg);
        $tot = $this->getAllCharacterContact($pg, 'contatto');
        $contatti_presenti = "{$pg},";
        foreach ($tot as $con) {
            $contatti_presenti .= "{$con['contatto']},";
        }
        return rtrim($contatti_presenti, ",");
    }

    /*** FUNCTIONS ***/

    /**
     * @fn newContatto
     * @note Inserisce un nuovo contatto
     * @param array $post
     * @return array
     */
    public function newContatto(array $post): array
    {
        $personaggio = Filters::int($post['id_pg']);
        $contatto = Filters::int($post['contatto']);
        $creato_da = Filters::int($post['id_pg']);
        $categoria = Filters::int($post['categoria']);

        DB::query("INSERT INTO contatti(personaggio, contatto, categoria, creato_da, creato_il) VALUES('{$personaggio}', '{$contatto}', {$categoria},'{$creato_da}', NOW())");

        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Contatto creato con successo.',
            'swal_type' => 'success'
        ];
    }

    /**
     * @fn editContatto
     * @note Modifica la categoria del contatto
     * @param array $post
     * @return array
     */
    public function editContatto(array $post): array
    {
        $id = Filters::int($post['id']);
        $categoria = Filters::int($post['categoria']);

        DB::query("UPDATE contatti SET categoria = '{$categoria}'  WHERE id= {$id}");
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Contatto modificato con successo.',
            'swal_type' => 'success'
        ];
    }

    /**
     * @fn deleteContatto
     * @note Rimuove un contatto
     * @param int $id
     * @return array
     */
    public function deleteContatto(int $id): array
    {
        $id = Filters::int($id);
        $id_pg = DB::query("SELECT personaggio FROM contatti WHERE id = '{$id}' "); //recupero l'id del personaggio per ricaricare la lista dei contatti
        DB::query("DELETE FROM contatti WHERE id = '{$id}' LIMIT 1"); //Cancello il contatto
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Contatto rimosso correttamente.',
            'swal_type' => 'success',
            'contatti_list' => $this->ContactList(Filters::int($id_pg['personaggio']))
        ];
    }
}