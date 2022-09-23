<?php

class MeteoVenti extends Meteo
{

    /*** PERMESSI ****/

    /**
     * @fn permissionManageWeatherWinds
     * @note Controlla se si hanno i permessi per gestire i venti
     * @return bool
     */
    public function permissionManageWeatherWinds(): bool
    {
        return Permissions::permission('MANAGE_WEATHER');
    }


    /** GETTER */

    /**
     * @fn getAllWinds
     * @note Estrae tutti i venti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllWinds(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM meteo_venti ORDER BY nome", []);
    }

    /**
     * @fn getWind
     * @note Estrae un vento
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getWind(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM meteo_venti WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**** LIST ****/

    /**
     * @fn selectVento
     * @note Genera gli option per il vento
     * @return string
     * @throws Throwable
     */
    public function listWinds(): string
    {
        $winds = $this->getAllWinds();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', '', $winds);
    }

    /**
     * @fn selectVento
     * @note Genera gli option per il vento
     * @param $selected
     * @return string
     * @throws Throwable
     */
    public function listWindsByText($selected): string
    {
        $winds = $this->getAllWinds();
        return Template::getInstance()->startTemplate()->renderSelect('nome', 'nome', $selected, $winds);
    }

    /**** AJAX ****/

    /**
     * @fn ajaxWindList
     * @note Estrae la lista di venti
     * @return array
     * @throws Throwable
     */
    public function ajaxWindList(): array
    {
        return ['List' => $this->listWinds()];
    }

    /**
     * @fn ajaxWindData
     * @note Estrae i dati di un vento
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxWindData(array $post): array
    {

        if ( $this->permissionManageWeatherWinds() ) {
            $id = Filters::int($post['id']);

            $data = $this->getWind($id);

            $nome = Filters::out($data['nome']);

            return [
                'response' => true,
                'nome' => $nome,
            ];

        }

        return ['response' => false];
    }

    /**** GESTIONE ****/

    /**
     * @fn NewWind
     * @note Inserisce una nuova condizione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function NewWind(array $post): array
    {
        if ( $this->permissionManageWeatherWinds() ) {

            $nome = Filters::in($post['nome']);

            DB::queryStmt(
                "INSERT INTO meteo_venti (nome)  VALUES (:nome) ",
                [
                    'nome' => $nome,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento creato.',
                'swal_type' => 'success',
                'meteo_venti' => $this->listWinds(),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn ModWind
     * @note Aggiorna una condizione meteo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ModWind(array $post): array
    {
        if ( $this->permissionManageWeatherWinds() ) {
            $id = Filters::in($post['id']);
            $nome = Filters::in($post['nome']);

            DB::queryStmt(
                "UPDATE  meteo_venti SET nome = :nome WHERE id=:id",
                [
                    'id' => $id,
                    'nome' => $nome,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento modificato.',
                'swal_type' => 'success',
                'meteo_venti' => $this->listWinds(),

            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

    /**
     * @fn DelWind
     * @note Cancella una condizione meteo
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function DelWind(array $post): array
    {
        if ( $this->permissionManageWeatherWinds() ) {

            $id = Filters::in($post['id']);

            DB::queryStmt("DELETE FROM meteo_venti WHERE id=:id", ['id' => $id]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Vento eliminato.',
                'swal_type' => 'success',
                'meteo_venti' => $this->listWinds(),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }
    }

}