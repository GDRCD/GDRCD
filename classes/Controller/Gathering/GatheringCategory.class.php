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

    public function GatheringCatList(): string
    {
        $template = Template::getInstance()->startTemplate();
        $list = $this->getAllGatheringCat( '*', 'ORDER BY nome ASC');
        return $template->renderTable(
            'gestione/gathering/category/list',
            $this->renderGatheringCatList($list, 'gestione')
        );
    }
    /**
     * @fn renderGatheringCatList
     * @note Render html lista categorie gathering
     * @param object $list
     * @param string $page
     * @return string
     */
    public function renderGatheringCatList(object $list, string $page): array
    {
            $row_data = [];
            $path =  'gestione_gathering_category';
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
                'Nome',
                'Descrizione',
                'Controlli'
            ];
            $links = [
                ['href' => "/main.php?page={$path}&op=new_cat", 'text' => 'Nuova Categoria'],
                ['href' => "/main.php?page={$backlink}", 'text' => 'Indietro']
            ];

            return [
                'body' => 'gestione/gathering/category/list',
                'body_rows'=> $row_data,
                'cells' => $cells,
                'links' => $links,
                'path'=>$path,
                'page'=>$page

            ];
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
            $abilita= (Filters::int($post['abilita'])) ? Filters::int($post['abilita']) : 0;

            DB::query("INSERT INTO gathering_cat(nome, descrizione, abilita) VALUES('{$nome}', '{$descrizione}', '{$abilita}')");

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
            $abilita= (Filters::int($post['abilita'])) ? Filters::int($post['abilita']) : 0;

            DB::query("UPDATE gathering_cat SET nome = '{$nome}', descrizione= '{$descrizione}', abilita='{$abilita}' WHERE id={$id}");

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



    /***** LISTS *****/

    /**
     * @fn listGatheringCat
     * @note Ritorna una serie di option per una select contenente la lista delle categorie
     * @return string
     */
    public function listGatheringCat($selected = 0): string
    {
        $html = '<option value="0"></option>';
        $abis = $this->getAllGatheringCat();

        foreach ($abis as $abi) {
            $nome = Filters::out($abi['nome']);
            $id = Filters::int($abi['id']);
            $sel = ($id == $selected) ? 'selected' : '';
            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }


}
