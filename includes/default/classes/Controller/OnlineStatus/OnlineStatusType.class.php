<?php

class OnlineStatusType extends OnlineStatus
{

    /*** TABLES HELPERS **/

    /**
     * @fn getStatusByType
     * @note Estrae i dati di uno stato in base al tipo
     * @param int $type
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getStatusByType(int $type, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM online_status WHERE type=:type ORDER by text", [
            'type' => $type,
        ]);
    }

    /**
     * @fn getStatusTypes
     * @note Estrae tutti i tipi di stato esistenti
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getStatusTypes(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT * FROM online_status_type WHERE 1 ORDER BY label", []);
    }

    /**
     * @fn getStatusType
     * @note Estrae tutti i dati di uno stato specifico
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getStatusType(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM online_status_type WHERE id=:id LIMIT 1", [
            'id' => $id,
        ]);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxStatusTypeData
     * @note Estrae i dati di un tipo di stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxStatusTypeData(array $post):array
    {

        if ( $this->manageStatusPermission() ) {
            $id = Filters::int($post['id']);
            $data = OnlineStatusType::getInstance()->getStatusType($id);
            return [
                'label' => Filters::out($data['label']),
                'request' => Filters::out($data['request']),
            ];
        }

        return [];
    }


    /**** LIST ****/

    /**
     * @fn listStatusType
     * @note Crea la lista select dei tipi
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listStatusType(int $selected = 0): string
    {
        $types = OnlineStatusType::getInstance()->getStatusTypes();
        return Template::getInstance()->startTemplate()->renderSelect('id','label',$selected,$types);
    }


    /*** MANAGEMENT FUNCTIONS **/

    /**
     * @fn insertStatusType
     * @note Funzione d'inserimento di un nuovo tipo di stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertStatusType(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $label = Filters::in($post['label']);
            $request = Filters::in($post['request']);

            DB::queryStmt("INSERT INTO online_status_type (label,request) VALUES (:label,:request)",[
                'label' => $label,
                'request' => $request,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo di stato inserito correttamente.',
                'swal_type' => 'success',
                'status_list' => OnlineStatusType::getInstance()->listStatusType(),
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
     * @fn editStatusType
     * @note Funzione di modifica di un tipo di stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editStatusType(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $id = Filters::int($post['id']);
            $label = Filters::in($post['label']);
            $request = Filters::in($post['request']);

            DB::queryStmt("UPDATE online_status_type SET label=:label,request=:request WHERE id=:id LIMIT 1",[
                'id' => $id,
                'label' => $label,
                'request' => $request,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo di stato modificato correttamente.',
                'swal_type' => 'success',
                'status_list' => OnlineStatusType::getInstance()->listStatusType(),
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
     * @fn deleteStatusType
     * @note Funzione di eliminazione di un tipo di stato
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteStatusType(array $post): array
    {

        if ( $this->manageStatusPermission() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM online_status_type WHERE id=:id LIMIT 1",[
                'id' => $id,
            ]);
            DB::queryStmt("DELETE FROM online_status WHERE type=:id",[
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Tipo di stato eliminato correttamente.',
                'swal_type' => 'success',
                'status_list' => OnlineStatusType::getInstance()->listStatusType(),
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