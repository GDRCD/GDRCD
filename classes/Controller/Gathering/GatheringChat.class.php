<?php

/**
 * @class GatheringChat
 * @note Classe per la gestione delle categorie di oggetti trovabili
 * @required PHP 7.1+
 */
class GatheringChat extends Gathering
{
    /**** ROUTING ***/

    /**
     * @fn loadManagementGatheringChatPage
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementGatheringChatPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'gathering_chat_list.php';
                break;

            case 'new_cat':
                $page = 'gathering_chat_new.php';
                break;

            case 'edit_cat':
                $page = 'gathering_chat_edit.php';
                break;
        }

        return $page;
    }


    /*** TABLES HELPERS ***/

    /**
     * @fn getAllGatheringChat
     * @note Ottiene la lista degli esiti
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllGatheringChat(string $val = '*', string $order = '')
    {
        $where = ($this->gatheringManage()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM gathering_chat WHERE {$where} {$order}", 'result');
    }
    /*** GATHERING INDEX ***/

    /**
     * @fn GatheringChatList
     * @note Render html della lista delle categorie
     * @return string
     */

    public function GatheringChatList(): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllGatheringChat( '*', 'ORDER BY nome ASC');
        return $template->renderTable(
            'gestione/gathering/chat/list',
            $this->renderGatheringChatList($list, 'gestione')
        );
    }
    /**
     * @fn renderGatheringChatList
     * @note Render html lista categorie gathering
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderGatheringChatList(object $list, string $page): array
    {
        $row_data = [];
        $path =  'gestione_gathering_chat';
        $backlink = 'gestione';

        foreach ($list as $row) {

            $id = Filters::int($row['id']);

            $array = [
                'id' => $id,
                'nome'=> Filters::in($row['nome']),
                'descrizione'=> Filters::in($row['descrizione']),
                'gathering_view_permission'=> $this->gatheringManage()

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Chat',
            'Descrizione',
            'Controlli'
        ];
        $links = [
            ['href' => "/main.php?page={$path}&op=new_chat", 'text' => 'Nuova combinazione'],
            ['href' => "/main.php?page={$backlink}", 'text' => 'Indietro']
        ];

        return [
            'body' => 'gestione/gathering/chat/list',
            'body_rows'=> $row_data,
            'cells' => $cells,
            'links' => $links,
            'path'=>$path,
            'page'=>$page

        ];
    }


    /*** GATHERING ***/
    /**
     * @fn deleteGatheringChat
     * @note Rimuove una combinazione. Da rivedere in quanto vanno rimossi tutti gli oggetti in una volta
     * @param array $post
     * @return array
     */
    public function deleteGatheringChat(int $id)
    {

        $id = Filters::int($id);


        if ($this->gatheringManage()) {

            DB::query("DELETE FROM gathering_chat WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Combinazione rimossa correttamente.',
                'swal_type' => 'success',
                'gathering_list' => $this->GatheringChatList()

            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }

    }
    /*** NEW CATEGORY ***/

    /**
     * @fn newGatheringChat
     * @note Inserisce una nuova categoria
     * @param array $post
     * @return array
     */
    public function newGatheringChat(array $post): array
    {

        if ($this->gatheringManage()) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $abilita= (Filters::int($post['abilita'])) ? Filters::int($post['abilita']) : 0;

            DB::query("INSERT INTO gathering_chat(nome, descrizione, abilita) VALUES('{$nome}', '{$descrizione}', '{$abilita}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Combinazione creata con successo.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

    /*** EDIT CATEGORY ***/

    /**
     * @fn editGatheringChat
     * @note Modifica una categoria
     * @param array $post
     * @return array
     */
    public function editGatheringChat(array $post): array
    {

        if ($this->gatheringManage()) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);
            $abilita= (Filters::int($post['abilita'])) ? Filters::int($post['abilita']) : 0;

            DB::query("UPDATE gathering_chat SET nome = '{$nome}', descrizione= '{$descrizione}', abilita='{$abilita}' WHERE id={$id}");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria modificata con successo.',
                'swal_type' => 'success'
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error'
            ];
        }
    }

}
