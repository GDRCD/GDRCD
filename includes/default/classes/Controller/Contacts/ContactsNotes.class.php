<?php

/**
 * @class ContactsNotes
 * @note Classe per la gestione centralizzata delle note dei contatti
 * @required PHP 7.1+
 */
class ContactsNotes extends Contacts
{

    /**** ROUTING ***/

    /**
     * @fn loadManagementContactPage
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementContactNotePage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'dettaglio_nota.php';
                break;

            case 'edit_nota':
                $page = 'modifica_nota.php';
                break;
        }

        return $page;
    }

    /**
     * @fn newNota
     * @note Inserisce una nuova nota al contatto
     * @param array $post
     * @return array
     */
    public function newNota(array $post): array
    {
        $id_contatto = Filters::in($post['id_contatto']);
        $nota = Filters::in($post['nota']);
        $pubblica=Filters::in($post['pubblica']);
        $titolo= Filters::in($post['titolo']);
        $creato_da=Filters::in($post['id_pg']);

        DB::query("INSERT INTO contatti_nota(id_contatto,titolo, nota, pubblica, creato_da, creato_il) VALUES('{$id_contatto}', '{$titolo}','{$nota}', '{$pubblica}','{$creato_da}', NOW())");
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Nota creata con successo.',
            'swal_type' => 'success'
        ];
    }

    /**
     * @fn editNota
     * @note Modifca una nuova nota al contatto
     * @param array $post
     * @return array
     */
    public function editNota(array $post): array
    {
        $id= Filters::in($post['id']);
        $nota = Filters::in($post['nota']);
        $pubblica=Filters::in($post['pubblica']);
        $titolo= Filters::in($post['titolo']);

        DB::query("UPDATE contatti_nota SET titolo = '{$titolo}', nota='{$nota}', pubblica='{$pubblica}' WHERE id= {$id}");
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Nota modificata con successo.',
            'swal_type' => 'success'
        ];
    }


    /**
     * @fn getAllNote
     * @note Estrae le note di un contatto
     * @param array $post
     * @return array
     */
    public function getAllNote($id,$id_pg)
    {
        if($this->contactManage($id_pg))
        { //se sei proprietario vedi tutte le note pubbliche e non, ma che non sono state eliminate
            return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND eliminato=0", 'result');
        }
        else if($this->contactManageManager($id_pg))
        {//se hai i permessi di controllo sui contatti, puoi visionare tutti le note
            return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id}", 'result');
        }else{
            //altrimenti, vedi solo le note pubbliche e che non sono eliminate
            return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND pubblica='si' AND eliminato=0", 'result');
        }
    }
    /**
     * @fn getNota
     * @note Estrae le note di un contatto
     * @param array $post
     * @return array
     */
    public function getNota(string $val = '*', string $id)
    {
        return DB::query("SELECT {$val} FROM contatti_nota WHERE id = '{$id}' ");
    }

    /**
     * @fn deleteNota
     * @note Rimuove una nota
     * @param array $post
     * @return array
     */
    public function deleteNota(int $id)
    {
        $id = Filters::int($id);
        $id_pg=DB::query("SELECT personaggio FROM contatti WHERE id = {$id} "); //recupero l'id del personaggio per ricaricare la lista dei contatti
        $id_contatto=$this->getNota('id_contatto', $id);

        DB::query("UPDATE contatti_nota SET eliminato = '1' WHERE id = '{$id}' LIMIT 1"); //Cancello il contatto
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Nota rimossa correttamente.',
            'swal_type' => 'success',
            'note_list' => $this->NoteList($id_contatto['id_contatto'])
        ];
    }


    /*** CONTACT INDEX ***/

    /**
     * @fn NoteList
     * @note Render html della lista delle note di un contatto
     * @return string
     */

    public function NoteList($id_contatto): string
    {
        $template = Template::getInstance()->startTemplate();
        $id_pg=$this->getContact('personaggio', $id_contatto);
        $list = $this->getAllNote($id_contatto, $id_pg['personaggio']);
        return $template->renderTable(
            'scheda/contatti/note_list',
            $this->renderNoteList($list, 'scheda_contatti', $id_pg['personaggio'])
        );
    }
    /**
     * @fn renderNoteList
     * @note Render html lista contatti pg
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderNoteList(object $list, string $page, $id_pg): array
    {
        $row_data = [];
        $path =  'scheda_contatti';
        $backlink = 'scheda';
        $pg=Personaggio::nameFromId($id_pg);
        foreach ($list as $row) {
            $id=Filters::in($row['id']);

            $id_contatto = Filters::in($row['contatto']);
            $pubblica=Filters::in($row['pubblica']);
            $titolo= Filters::out($row['titolo']);
            $nota=substr(Filters::out($row['nota']), 0, 10);
            $creato_il=Filters::date($row['creato_il'],'d/m/Y');
            $creato_da=Personaggio::nameFromId($row['creato_da']);
            $pop_up='javascript:modalWindow("note", "Dettaglio nota","popup.php?page=scheda_contatti_nota&id='.$id.'") ';
            $pop_up_modifica='javascript:modalWindow("note", "Modifica nota","popup.php?page=scheda_contatti_nota&id='.$id.'&op=edit_nota") ';
            $array = [
                'id'=>$id,
                'titolo'=>$titolo,
                'nota' => $nota,
                'pubblica'=>$pubblica,
                'contatti_view_permission'=> $this->contactManage($id_pg),
                'id_pg'=>$id_pg,
                'pg'=>$pg,
                'creato_il'=> $creato_il,
                'creato_da'=>$creato_da,
                'pop_up'=>$pop_up,
                'pop_up_modifica'=>$pop_up_modifica

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Titolo',

            'Nota',

            'Controlli'
        ];
        $links = [
            //  ['href' => "/main.php?page={$path}&op=new&id_pg={$id_pg}&pg={$pg}", 'text' => 'Nuovo contatto']
            // ['href' => "/main.php?page={$backlink}&id_pg={$id_pg}&pg={$pg}", 'text' => 'Indietro']
        ];

        return [
            'body' => 'scheda/contatti/note_list',
            'body_rows'=> $row_data,
            'cells' => $cells,
            'links' => $links,
            'path'=>$path,
            'page'=>$page

        ];
    }


}