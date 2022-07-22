<?php

/**
 * @class ContattiNote
 * @note Classe per la gestione centralizzata delle note dei contatti
 * @required PHP 7.1+
 */
class ContattiNote extends Contatti
{

    /*** TABLE HELPERS ***/

    /**
     * @fn getAllNote
     * @note Estrae le note di un contatto
     * @param int $id
     * @param int $id_pg
     * @return mixed
     */
    public function getAllNote(int $id, int $id_pg)
    {
        if ($this->contactEnables()) {
            if ($this->contactView($id_pg)) {
                //se sei il proprietario, visualizzi le tue note
                return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND eliminato=0", 'result');
            } else if ($this->contactSecret() && (Permissions::permission('VIEW_CONTACTS'))) {
                //se le note sono segrete e hai il permesso di visualizzarle
                return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND eliminato=0", 'result');
            } else if ($this->contactPublic()) {
                //se la configurazione è impostata su pubblico
                return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND eliminato=0", 'result');
            } else {
                //altrimenti, prende solo quelli impostati come pubblico dall'utente
                return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND pubblica IN ('si', '') AND eliminato=0", 'result');
            }
        }

        return [];
    }

    /**
     * @fn getNota
     * @note Estrae le note di un contatto
     * @param string $val
     * @param string $id
     * @return mixed
     */
    public function getNota(string $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM contatti_nota WHERE id = '{$id}' ");
    }

    /*** CONTACT INDEX ***/

    /**
     * @fn renderNoteList
     * @note Render html lista contatti pg
     * @param object $list
     * @param string $page
     * @param int $id_pg
     * @return array
     */
    public function renderNoteList(object $list, string $page, int $id_pg): array
    {
        $row_data = [];
        $path = 'scheda_contatti';
        $pg = Personaggio::nameFromId($id_pg);
        foreach ($list as $row) {
            $id = Filters::in($row['id']);

            $id_contatto = Filters::in($row['contatto']);
            $pubblica = Filters::in($row['pubblica']);
            $titolo = Filters::out($row['titolo']);
            $nota = substr(Filters::out($row['nota']), 0, 10);
            $creato_il = Filters::date($row['creato_il'], 'd/m/Y');
            $creato_da = Personaggio::nameFromId($row['creato_da']);
            $pop_up = 'javascript:modalWindow("note", "Dettaglio nota","popup.php?page=scheda_contatti_nota&id=' . $id . '") ';
            $pop_up_modifica = 'javascript:modalWindow("note_edit", "Modifica nota","popup.php?page=scheda_contatti_nota&id=' . $id . '&op=note_edit") ';
            $array = [
                'id' => $id,
                'titolo' => $titolo,
                'nota' => $nota,
                'pubblica' => $pubblica,
                'contatti_view_permission' => $this->contactView($id_pg),
                'contatti_update_permission' => $this->contactUpdate($id_pg),
                'contatti_delete_permission' => $this->contactDelete($id_pg),
                'id_pg' => $id_pg,
                'pg' => $pg,
                'creato_il' => $creato_il,
                'creato_da' => $creato_da,
                'pop_up' => $pop_up,
                'pop_up_modifica' => $pop_up_modifica
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Titolo',
            'Nota',
            'Controlli'
        ];
        $links = [];

        return [
            'body' => 'scheda/contatti/note_list',
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
            'path' => $path,
            'page' => $page
        ];
    }

    /*** LIST ***/

    /**
     * @fn NoteList
     * @note Render html della lista delle note di un contatto
     * @param int $id_contatto
     * @return string
     */
    public function NoteList(int $id_contatto): string
    {
        $template = Template::getInstance()->startTemplate();
        $contact_data = $this->getContact($id_contatto, 'personaggio');
        $pg = Filters::int($contact_data['personaggio']);

        $list = $this->getAllNote($id_contatto, $pg);
        return $template->renderTable(
            'scheda/contatti/note_list',
            $this->renderNoteList($list, 'scheda_contatti', $pg)
        );
    }

    /*** GESTIONE ***/

    /**
     * @fn newNota
     * @note Inserisce una nuova nota al contatto
     * @param array $post
     * @return array
     */
    public function newNota(array $post): array
    {
        $id_contatto = Filters::int($post['id_contatto']);
        $nota = Filters::in($post['nota']);
        $pubblica = Filters::in($post['pubblica']);
        $titolo = Filters::in($post['titolo']);
        $creato_da = Filters::int($post['id_pg']);

        $contact_data = $this->getContact($id_contatto, 'personaggio');
        $owner = Filters::int($contact_data['personaggio']);

        if (Personaggio::isMyPg($owner)) {

            DB::query("INSERT INTO contatti_nota(id_contatto,titolo, nota, pubblica, creato_da, creato_il) VALUES('{$id_contatto}', '{$titolo}','{$nota}', '{$pubblica}','{$creato_da}', NOW())");
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota creata con successo.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Permesso negato!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }


    /**
     * @fn editNota
     * @note Modifica una nota al contatto
     * @param array $post
     * @return array
     */
    public function editNota(array $post): array
    {
        $id = Filters::int($post['id']);
        $nota = Filters::in($post['nota']);
        $pubblica = Filters::in($post['pubblica']);
        $titolo = Filters::in($post['titolo']);

        $note_data = $this->getNota($id, 'id_contatto');
        $contact_id = Filters::int($note_data['id_contatto']);
        $contact_data = $this->getContact($contact_id, 'personaggio');
        $owner = Filters::int($contact_data['personaggio']);

        if ($this->contactUpdate($owner)) {
            DB::query("UPDATE contatti_nota SET titolo = '{$titolo}', nota='{$nota}', pubblica='{$pubblica}' WHERE id= {$id}");
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota modificata con successo.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Permesso negato!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }

    }

    /**
     * @fn deleteNota
     * @note Rimuove una nota
     * @param int $id
     * @return array
     */
    public function deleteNota(int $id): array
    {
        $id = Filters::int($id);
        $nota_data = $this->getNota($id, 'id_contatto');
        $contact_id = Filters::int($nota_data['id_contatto']);

        $contact_data = $this->getContact($contact_id, 'personaggio');
        $owner = Filters::int($contact_data['personaggio']);

        if ($this->contactUpdate($owner)) {
            DB::query("UPDATE contatti_nota SET eliminato = '1' WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota rimossa correttamente.',
                'swal_type' => 'success',
                'note_list' => $this->NoteList($contact_id)
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Permesso negato!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }
}