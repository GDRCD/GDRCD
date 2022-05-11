<?php

/**
 * @class ContactsNotes
 * @note Classe per la gestione centralizzata delle note dei contatti
 * @required PHP 7.1+
 */
class ContactsNotes extends Contacts
{


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

        $creato_da=Filters::in($post['id_pg']);

        DB::query("INSERT INTO contatti_nota(id_contatto, nota, pubblica, creato_da, creato_il) VALUES('{$id_contatto}', '{$nota}', '{$pubblica}','{$creato_da}', NOW())");
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Nota creata con successo.',
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
        if($this->contactManage($id_pg)){
            return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id}", 'result');
        }else{
            return DB::query("SELECT * FROM contatti_nota WHERE id_contatto={$id} AND pubblica='si'", 'result');
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

        DB::query("DELETE FROM contatti WHERE id = '{$id}' LIMIT 1"); //Cancello il contatto
        return [
            'response' => true,
            'swal_title' => 'Operazione riuscita!',
            'swal_message' => 'Contatto rimosso correttamente.',
            'swal_type' => 'success',
            'note_list' => $this->NoteList($id_contatto)
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
            'scheda/contatti/list_note',
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
            $nota=Filters::in($row['nota']);

            $array = [
                'id'=>$id,
                'nota' => $nota,
                'pubblica'=>$pubblica,
                'contatti_view_permission'=> $this->contactManage($id_pg),
                'id_pg'=>$id_pg,
                'pg'=>$pg

            ];

            $row_data[] = $array;
        }

        $cells = [

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