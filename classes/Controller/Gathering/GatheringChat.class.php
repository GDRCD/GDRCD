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

            case 'new_chat':
                $page = 'gathering_chat_new.php';
                break;

            case 'edit_chat':
                $page = 'gathering_chat_edit.php';
                break;
        }

        return $page;
    }


    /*** TABLES HELPERS ***/

    /**
     * @fn getAllGatheringChat
     * @note Ottiene delle chat che hanno degli oggetti droppabili
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllGatheringChat(string $val = '*', string $order = '')
    {
        $where = ($this->gatheringManage()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM mappa WHERE id IN (SELECT id_chat FROM gathering_chat)", 'result');
    }

    /**
     * @fn getAllGatheringChat
     * @note Ottiene delle chat che hanno degli oggetti droppabili
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getGatheringChat(int $id)
    {
        return DB::query("SELECT gathering_chat.*, gathering_item.nome FROM gathering_chat 
    LEFT JOIN gathering_item ON id_item = gathering_item.id WHERE id_chat={$id}", 'result');
    }

    /**
     * @fn getOneGatheringChatItem
     * @note Ottiene delle chat che hanno degli oggetti droppabili
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getOneGatheringChatItem($id_chat, $id_item)
    {
        return DB::query("SELECT * FROM gathering_chat WHERE id_chat='{$id_chat}' AND id_item='{$id_item}'", 'result');
    }


    /*** GATHERING INDEX ***/

    /**
     * @fn GatheringChatList
     * @note Render html della lista delle combinazioni
     * @return string
     */

    public function GatheringChatList(): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllGatheringChat( 'nome, id, drop_rate', '  ');
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
            $nome = Filters::in($row['nome']);
            $drop_rate = Filters::int($row['drop_rate']);

            $array = [
                'id'=>$id,
                'nome' => $nome,
                'drop_rate'=>$drop_rate,

                'gathering_view_permission'=> $this->gatheringManage()

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Chat',
            'Percentuale di drop',
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

    /**
     * @fn GatheringChatItemList
     * @note Render html della lista delle combinazioni
     * @return string
     */

    public function GatheringChatItemList(int $id): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getGatheringChat( $id);
        return $template->renderTable(
            'gestione/gathering/chat/item_list',
            $this->renderGatheringChatItemList($list, 'gestione')
        );
    }
    /**
     * @fn renderGatheringChatList
     * @note Render html lista categorie gathering
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderGatheringChatItemList(object $list, string $page): array
    {
        $row_data = [];
        $path =  'gestione_gathering_chat';
        $backlink = 'gestione';
        foreach ($list as $row) {

            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $percentuale = Filters::int($row['percentuale']);
            $quantita_min=(Filters::int($row['quantita_min'])) ? Filters::int($row['quantita_min']) : 0;
            $quantita_max= (Filters::int($row['quantita_max'])) ? Filters::int($row['quantita_max']) : 0;
            $livello_abi=(Filters::int($row['livello_abi'])) ? Filters::int($row['livello_abi']) : 0;
            $drop_rate=(Filters::int($row['drop_rate'])) ? Filters::int($row['drop_rate']) : 0;

            $array = [
                'id'=>$id,
                'nome' => $nome,
                'percentuale'=>$percentuale,
                'quantita_min'=>$quantita_min,
                'quantita_max'=>$quantita_max,
                'livello_abi'=>$livello_abi,
                'gathering_rarity'=> $this->gatheringRarity(),
                'gathering_ability'=> $this->gatheringAbility(),
                'gathering_view_permission'=> $this->gatheringManage()

            ];

            $row_data[] = $array;
        }

        $cells = [
            'Chat',
            'Percentuale di drop',
            'Quantità minima',
            'Quantità massima',
            'Livello Abilità',
            'Controlli'
        ];


        return [
            'body' => 'gestione/gathering/chat/item_list',
            'body_rows'=> $row_data,
            'cells' => $cells,

            'path'=>$path,
            'page'=>$page

        ];
    }

    /**
     * @fn GatheringChatItemList
     * @note Render html della lista delle combinazioni
     * @return string
     */

    public function GatheringChatItemNew($id):string
    {
        $template = Template::getInstance()->startTemplate();
        $list=GatheringItem::getInstance()->listGatheringItem();
        return $template->renderTable(
            'gestione/gathering/chat/add_item',
            $this->renderGatheringChatItemNew($list, 'gestione', $id)
        );
    }
    /**
     * @fn renderGatheringChatList
     * @note Render html lista per inserimento nuovo oggetto in chat
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderGatheringChatItemNew( $list, string $page, $id): array
    {
        $path =  'gestione_gathering_chat';
        $cells = [
            'Oggetto',
            'Percentuale di drop',
            'Quantità minima',
            'Quantità massima',
            'Livello Abilità',
            'Controlli'
        ];
        return [
            'body' => 'gestione/gathering/chat/add_item',
            'body_rows'=> $list,
            'cells' => $cells,
            'id_chat'=>$id,
            'gathering_rarity'=> $this->gatheringRarity(),
            'gathering_ability'=> $this->gatheringAbility(),
            'gathering_view_permission'=>$this->gatheringManage(),
            'path'=>$path,
            'page'=>$page

        ];
    }


    /*** GATHERING ***/
    /**
     * @fn deleteAllGatheringChat
     * @note Rimuove una combinazione. Da rivedere in quanto vanno rimossi tutti gli oggetti in una volta
     * @param array $post
     * @return array
     */
    public function deleteAllGatheringChat(int $id)
    {
        $id = Filters::int($id);
        if ($this->gatheringManage()) {

            DB::query("DELETE FROM gathering_chat WHERE id_chat = '{$id}' LIMIT 1");

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
    /**
     * @fn deleteGatheringChatItem
     * @note Rimuove una combinazione oggetti nella chat
     * @param array $post
     * @return array
     */
    public function deleteGatheringChatItem(int $id)
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

    /*** NEW ***/
    /**
     * @fn newGatheringChat
     * @note Inserisce una nuova categoria
     * @param array $post
     * @return array
     */
    public function newGatheringChat(array $post): array
    {
        if ($this->gatheringManage()) {
            $id_chat= Filters::int($post['chat']);
            $id_item= Filters::int($post['item']);
            $percentuale= Filters::int($post['percentuale']);
            $quantita_min=(Filters::int($post['quantita_min'])) ? Filters::int($post['quantita_min']) : 0;
            $quantita_max= (Filters::int($post['quantita_max'])) ? Filters::int($post['quantita_max']) : 0;
            $livello_abi=(Filters::int($post['livello_abi'])) ? Filters::int($post['livello_abi']) : 0;
            $drop_rate=(Filters::int($post['drop_rate'])) ? Filters::int($post['drop_rate']) : 0;
            DB::query("UPDATE mappa SET drop_rate = '{$drop_rate}'  WHERE id={$id_chat}");
            DB::query("INSERT INTO gathering_chat(id_chat, id_item,percentuale, quantita_min, quantita_max, livello_abi) VALUES('{$id_chat}', '{$id_item}', '{$percentuale}', '{$quantita_min}', '{$quantita_max}', '{$livello_abi}')");
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
    /**
     * @fn newGatheringChat
     * @note Inserisce una nuova categoria
     * @param array $post
     * @return array
     */
    public function newGatheringChatItem(array $post): array
    {
        if ($this->gatheringManage()) {
            $id_chat= Filters::int($post['id_chat']);
            $id_item= Filters::int($post['item']);
            $percentuale= Filters::int($post['percentuale']);
            $quantita_min=(Filters::int($post['quantita_min'])) ? Filters::int($post['quantita_min']) : 0;
            $quantita_max= (Filters::int($post['quantita_max'])) ? Filters::int($post['quantita_max']) : 0;
            $livello_abi=(Filters::int($post['livello_abi'])) ? Filters::int($post['livello_abi']) : 0;

            if($this->getOneGatheringChatItem($id_chat, $id_item)){
                DB::query("UPDATE gathering_chat SET percentuale= '{$percentuale}', quantita_min= '{$quantita_min}', quantita_max= '{$quantita_max}', livello_abi = '{$livello_abi}'  WHERE id_chat={$id_chat} AND id_item={$id_item}");
            }else{
               $query="INSERT INTO gathering_chat(id_chat, id_item,percentuale, quantita_min, quantita_max, livello_abi) VALUES('{$id_chat}', '{$id_item}', '{$percentuale}', '{$quantita_min}', '{$quantita_max}', '{$livello_abi}')";
                DB::query("INSERT INTO gathering_chat(id_chat, id_item,percentuale, quantita_min, quantita_max, livello_abi) VALUES('{$id_chat}', '{$id_item}', '{$percentuale}', '{$quantita_min}', '{$quantita_max}', '{$livello_abi}')");
            }

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => $query,
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


    /*** EDIT ***/
    /**
     * @fn editGatheringChatItem
     * @note Modifica una categoria
     * @param array $post
     * @return array
     */
    public function editGatheringChatItem(array $post): array
    {
        if ($this->gatheringManage()) {
            $id = Filters::in($post['id']);
            $percentuale= Filters::int($post['percentuale']);
            $quantita_min=(Filters::int($post['quantita_min'])) ? Filters::int($post['quantita_min']) : 0;
            $quantita_max= (Filters::int($post['quantita_max'])) ? Filters::int($post['quantita_max']) : 0;
            $livello_abi=(Filters::int($post['livello_abi'])) ? Filters::int($post['livello_abi']) : 0;
            DB::query("UPDATE gathering_chat SET percentuale = '{$percentuale}', quantita_min= '{$quantita_min}', quantita_max='{$quantita_max}', livello_abi='{$livello_abi}' WHERE id={$id}");
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Combinazione modificata con successo.',
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
    /**
     * @fn editGatheringChat
     * @note Modifica una categoria
     * @param array $post
     * @return array
     */
    public function editGatheringChat(array $post): array
    {
        if ($this->gatheringManage()) {
            $id_chat = Filters::in($post['id_chat']);
            $drop_rate= Filters::int($post['drop_rate']);

            DB::query("UPDATE mappa SET drop_rate = '{$drop_rate}' WHERE id={$id_chat}");
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Chat aggiornata con successo.',
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


    /*** AJAX ***/

    /**
     * @fn ajaxObjectData
     * @note Estrae i dati di un oggetto alla modifica
     * @param array $post
     * @return array|void
     */
    public function ajaxObjectData(array $post)
    {
        if ($this->gatheringRarity()) {

            return [
                'drop_rate' => Filters::in($post['drop_rate']),

            ];
        }
    }


}
