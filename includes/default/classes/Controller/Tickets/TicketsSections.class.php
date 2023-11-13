<?php


class TicketsSections extends BaseClass
{

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    public function __construct()
    {
        parent::__construct();
    }


    /*** GETTERS */

    /*** CONTROLS ***/

    /*** PERMISSIONS ***/

    /**
     * @fn manageSectionsPermission
     * @note Controlla se si hanno i permessi per la gestione delle sezioni dei ticket
     * @return bool
     * @throws Throwable
     */
    public function manageSectionsPermission(): bool
    {
        return Permissions::permission('MANAGE_TICKETS_SECTIONS');
    }


    /*** TABLES HELPERS **/

    /**
     * @fn getSection
     * @note Estrae i dati di una sezione
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getSection(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM tickets_sezioni WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }

    /**
     * @fn getAllSections
     * @note Estrae i dati di tutti gli stati
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllSections(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM tickets_sezioni", []);
    }

    /**
     * @fn getTicketSection
     * @note Estrae la sezione di un ticket specifico
     * @param int $ticket
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getTicketSection(int $ticket, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM ticket LEFT JOIN tickets_sezioni ON ticket.sezione = tickets_sezioni.id WHERE ticket.id=:ticket LIMIT 1", [
            'ticket' => $ticket,
        ]);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxSectionData
     * @note Estrae i dati di una sezione ticket
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxSectionData(array $post): array
    {
        if ( $this->manageSectionsPermission() ) {
            $id = Filters::int($post['id']);
            $data = $this->getSection($id);
            return [
                'titolo' => Filters::out($data['titolo']),
                'descrizione' => Filters::out($data['descrizione']),
                'sezione_padre' => Filters::int($data['sezione_padre']),
                'creabile' => Filters::bool($data['creabile']),
            ];
        }

        return [];
    }


    /**** LIST ****/

    /**
     * @fn listSections
     * @note Render della lista delle sezioni disponibili
     * @param int $selected
     * @param string $label
     * @return string
     * @throws Throwable
     */
    public function listSections(int $selected = 0, string $label = ''): string
    {
        $sections = self::getInstance()->getAllSections();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'titolo', $selected, $sections, $label);
    }

    /*** FUNCTIONS ***/

    /*** MANAGEMENT FUNCTIONS **/

    /**
     * @fn insertSection
     * @note Funzione d'inserimento di una nuova sezione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertSection(array $post): array
    {

        if ( $this->manageSectionsPermission() ) {

            $titolo = Filters::in($post['titolo']);
            $descrizione = Filters::in($post['descrizione']);
            $sezione_padre = Filters::int($post['sezione_padre']);
            $creabile = Filters::checkbox($post['creabile']);

            DB::queryStmt("INSERT INTO tickets_sezioni (titolo,descrizione,sezione_padre,creabile,creato_da) VALUES (:titolo,:descrizione,:sezione_padre,:creabile,:creato_da)", [
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'sezione_padre' => $sezione_padre > 0 ? $sezione_padre : null,
                'creabile' => $creabile,
                'creato_da' => $this->me_id
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sezione inserita correttamente.',
                'swal_type' => 'success',
                'sections_list' => $this->listSections(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];

    }

    /**
     * @fn editSection
     * @note Funzione di modifica di una sezione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editSection(array $post): array
    {

        if ( $this->manageSectionsPermission() ) {

            $id = Filters::int($post['id']);
            $titolo = Filters::in($post['titolo']);
            $descrizione = Filters::in($post['descrizione']);
            $sezione_padre = Filters::int($post['sezione_padre']);
            $creabile = Filters::checkbox($post['creabile']);

            DB::queryStmt("UPDATE tickets_sezioni SET titolo=:titolo,descrizione=:descrizione,sezione_padre=:sezione_padre,creabile=:creabile WHERE id=:id LIMIT 1", [
                'id' => $id,
                'titolo' => $titolo,
                'descrizione' => $descrizione,
                'sezione_padre' => $sezione_padre,
                'creabile' => $creabile,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Sezione modificata correttamente.',
                'swal_type' => 'success',
                'sections_list' => $this->listSections(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];
    }

    /**
     * @fn deleteSection
     * @note Funzione di eliminazione di una sezione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteSection(array $post): array
    {

        if ( $this->manageSectionsPermission() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("UPDATE tickets_sezioni SET sezione_padre = NULL WHERE sezione_padre=:id LIMIT 1", [
                'id' => $id,
            ]);

            DB::queryStmt("DELETE FROM tickets_sezioni WHERE id=:id LIMIT 1", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Seziona eliminata correttamente.',
                'swal_type' => 'success',
                'sections_list' => $this->listSections(),
            ];

        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];
    }
}