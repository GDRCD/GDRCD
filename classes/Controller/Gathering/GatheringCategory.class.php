<?php

/**
 * @class GatheringCategory
 * @note Classe per la gestione delle categorie di oggetti trovabili
 * @required PHP 7.1+
 */
class GatheringCategory extends Gathering
{
    /**** ROUTING ***/

    /**
     * @fn loadManagementGatheringCatPage
     * @note Routing delle pagine di gestione
     * @param string $op
     * @return string
     */
    public function loadManagementGatheringCatPage(string $op): string
    {
        $op = Filters::out($op);

        switch ($op) {
            default:
                $page = 'gathering_cat_list.php';
                break;

            case 'new_cat':
                $page = 'gathering_cat_new.php';
                break;

            case 'edit_cat':
                $page = 'gathering_cat_edit.php';
                break;

            case 'delete':
                $page = 'gathering_cat_delete.php';
                break;

        }

        return $page;
    }


    /*** TABLES HELPERS ***/

    /**
     * @fn getAllGatheringCat
     * @note Ottiene la lista degli esiti
     * @param string $val
     * @param string $order
     * @return bool|int|mixed|string
     */
    public function getAllGatheringCat(string $val = '*', string $order = '')
    {
        $where = ($this->gatheringManage()) ? '1' : "(master = '0' OR master = '{$this->me_id}')";

        return DB::query("SELECT {$val} FROM gathering_cat WHERE {$where} {$order}", 'result');
    }
    /*** GATHERING INDEX ***/

     /**
     * @fn GatheringCatList
     * @note Render html della lista delle categorie
     * @return string
     */
    public function GatheringCatList()
    {
        $list = $this->getAllGatheringCat( '*', 'ORDER BY nome ASC');
        return $this->renderGatheringCatList($list);
    }

    /**
     * @fn renderGatheringCatList
     * @note Render html lista esiti
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderGatheringCatList(object $list)
    {

        $html = '<div class="tr header">
                    <div class="td">Nome</div>
                    <div class="td">Descrizione</div>                 
                    <div class="td">Controlli</div>
                </div>';


        foreach ($list as $row) {

            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $descrizione = Filters::in($row['descrizione']);


            $html .= "<div class='tr'>";
            $html .= "<div class='td'>" . $nome. '</div>';
            $html .= "<div class='td'>" . $descrizione. '</div>';
            $html .= "<div class='td commands'>";

            if ($this->gatheringManage()) {
                $html .= "<a href='/main.php?page=gestione_gathering_category&op=edit_cat&id={$id}' title='Modifica'><i class='fas fa-edit'></i></a>";
            }

            if ($this->gatheringManage()) {
                $html .= " <a class='ajax_link' data-id='{$id}' data-action='delete_cat' href='#' title='Elimina'><i class='far fa-trash'></i></a>";
            }


            $html .= "</div>";
            $html .= "</div>";
        }

        $html .= "<div class='tr footer'>";

            $html .= "<a href = 'main.php?page=gestione_gathering_category&op=new_cat' >
                            Nuova Categoria
                        </a > |
                    <a href = '/main.php?page=gestione' > Indietro</a >";


        $html .= "</div > ";

        return $html;
    }


    /*** GATHERING ***/
    /**
     * @fn deleteGatheringCat
     * @note Rimuove una categoria
     * @param array $post
     * @return array
     */
    public function deleteGatheringCat(int $id)
    {

        $id = Filters::int($id);


        if ($this->gatheringManage()) {

            DB::query("DELETE FROM gathering_cat WHERE id = '{$id}' LIMIT 1");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria rimossa correttamente.',
                'swal_type' => 'success',
                'gathering_list' => $this->GatheringCatList()

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
     * @fn newGatheringCat
     * @note Inserisce una nuova categoria
     * @param array $post
     * @return array
     */
    public function newGatheringCat(array $post): array
    {

        if ($this->gatheringManage()) {

            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("INSERT INTO gathering_cat(nome, descrizione) VALUES('{$nome}', '{$descrizione}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Categoria creata con successo.',
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
     * @fn editGatheringCat
     * @note Modifica una  categoria
     * @param array $post
     * @return array
     */
    public function editGatheringCat(array $post): array
    {

        if ($this->gatheringManage()) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);
            $descrizione = Filters::in($post['descrizione']);

            DB::query("UPDATE gathering_cat SET nome = '{$nome}', descrizione= '{$descrizione}' WHERE id={$id}");

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


    /*** GET CATEGORY ***/

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


}
