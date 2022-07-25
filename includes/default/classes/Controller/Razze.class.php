<?php

class Razze extends BaseClass
{
    protected function __construct()
    {
        parent::__construct();
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getRace
     * @note Ottieni una razza
     * @param int $id
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getRace(int $id, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM razze WHERE id='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllRaces
     * @note Ottieni tutte le razze
     * @return bool|int|mixed|string
     */
    public function getAllRaces(string $val = '*')
    {
        return DB::query("SELECT {$val} FROM razze WHERE 1", 'result');
    }


    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageGenders
     * @note Controlla se il personaggio può gestire i generi
     * @return bool
     */
    public function permissionManageRaces(): bool
    {
        return Permissions::permission('MANAGE_RACES');
    }

    /**** LISTS ***/

    /**
     * @fn listRaces
     * @note Lista delle razze disponibili
     * @param int $selected
     * @return mixed
     */
    public function listRaces(int $selected = 0)
    {
        $genders = $this->getAllRaces();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $genders);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxRaceData
     * @note Estrae i dati di una razza
     * @param array $post
     * @return array|false[]|void
     */
    public function ajaxRaceData(array $post)
    {
        if ($this->permissionManageRaces()) {
            $id = Filters::int($post['id']);
            return $this->getRace($id);
        }
    }

    /**** GESTIONE ****/


    /**
     * @fn newRace
     * @note Inserisce una nuova razza
     * @param array $post
     * @return array
     */
    public function newRace(array $post): array
    {

        if ($this->permissionManageRaces()) {

            $nome = Filters::in($post['nome']);
            $sing_m = Filters::in($post['sing_m']);
            $sing_f = Filters::in($post['sing_f']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $icon = Filters::in($post['icon']);
            $url_site = Filters::in($post['url_site']);
            $iscrizione = Filters::checkbox($post['iscrizione']);
            $visibile = Filters::checkbox($post['visibile']);

            DB::query("INSERT INTO razze(nome, sing_m, sing_f, descrizione, immagine, icon, url_site, iscrizione, visibile) VALUES('{$nome}', '{$sing_m}', '{$sing_f}', '{$descrizione}', '{$immagine}', '{$icon}', '{$url_site}', '{$iscrizione}', '{$visibile}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Razza creata correttamente.',
                'swal_type' => 'success',
                'races_list' => $this->listRaces()
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
     * @fn modRace
     * @note Modifica una razza
     * @param array $post
     * @return array
     */
    public function modRace(array $post): array
    {

        if ($this->permissionManageRaces()) {

            $id = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $sing_m = Filters::in($post['sing_m']);
            $sing_f = Filters::in($post['sing_f']);
            $descrizione = Filters::in($post['descrizione']);
            $immagine = Filters::in($post['immagine']);
            $icon = Filters::in($post['icon']);
            $url_site = Filters::in($post['url_site']);
            $iscrizione = Filters::checkbox($post['iscrizione']);
            $visibile = Filters::checkbox($post['visibile']);


            DB::query("UPDATE razze SET nome='{$nome}', sing_m='{$sing_m}', sing_f='{$sing_f}', descrizione='{$descrizione}', immagine='{$immagine}', icon='{$icon}', url_site='{$url_site}', iscrizione='{$iscrizione}', visibile='{$visibile}' WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Razza modificata correttamente.',
                'swal_type' => 'success',
                'races_list' => $this->listRaces()
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
     * @fn delRace
     * @note Elimina una razza
     * @param array $post
     * @return array
     */
    public function delRace(array $post): array
    {

        if ($this->permissionManageRaces()) {

            $id = Filters::int($post['id']);


            DB::query("DELETE FROM razze WHERE id='{$id}'");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Razza eliminata correttamente.',
                'swal_type' => 'success',
                'races_list' => $this->listRaces()
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