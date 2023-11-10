<?php

class Mercato extends BaseClass
{
    /*** ROUTER ***/
    /**
     * @fn loadShopsPage
     * @note Routing delle pagine del mercato
     * @param string $op
     * @return string
     */
    public function loadShopsPage(string $op): string
    {
        $op = Filters::out($op);

        return match ($op) {
            'objects' => 'mercato_objects.php',
            default => 'mercato_shops.php',
        };
    }

    /*** PERMISSIONS ***/

    /**
     * @fn manageMercatoPermission
     * @note Controllo se ho i permessi per gestire il mercato
     * @return bool
     * @throws Throwable
     */
    public function manageShopObjectsPermission(): bool
    {
        return Permissions::permission('MANAGE_SHOPS_OBJECTS');
    }

    /**
     * @fn manageShopPermission
     * @note Controllo se ho i permessi per gestire un negozio
     * @return bool
     * @throws Throwable
     */
    public function manageShopPermission(): bool
    {
        return Permissions::permission('MANAGE_SHOPS');
    }

    /*** MERCATO TABLE HELPERS ***/

    /**
     * @fn getObject
     * @note Estrae i dati di un singolo oggetto
     * @param int $id
     * @param int $shop
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    private function getObject(int $id, int $shop, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM mercato WHERE oggetto=:id AND negozio=:shop LIMIT 1", [
            'id' => $id,
            'shop' => $shop,
        ]);
    }

    /**
     * @fn getAllObjects
     * @note Estrae la lista di tutti gli oggetti esistenti
     * @param int $shop
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllShopObjects(int $shop, string $val = 'oggetto.*,mercato.*'): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT {$val}  FROM mercato 
                LEFT JOIN oggetto ON (oggetto.id = mercato.oggetto)
                WHERE mercato.negozio=:shop AND oggetto.id IS NOT NULL AND mercato.quantity > 0
                ORDER BY nome",
            [
                'shop' => $shop,
            ]
        );
    }

    /**
     * @fn getAllShops
     * @note Estrae la lista di tutti i negozi
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    private function getAllShops(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} 
                FROM mercato_negozi 
                WHERE 1 ORDER BY nome", []);
    }

    /**
     * @fn getShop
     * @note Estrae tutti i negozi presenti
     * @param int $obj
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    private function getShopFromObject(int $obj, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM mercato WHERE oggetto=:obj LIMIT 1",[
            'obj' => $obj,
        ]);
    }

    /**
     * @fn getShop
     * @note Estrae tutti i negozi presenti
     * @param int $shop
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getShop(int $shop, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM mercato_negozi WHERE id=:shop LIMIT 1",[
            'shop' => $shop,
        ]);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxGetShop
     * @note Ajax's data for shop
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxGetShop(array $post): array
    {
        if ( $this->manageShopPermission() ) {
            $shop = Filters::int($post['shop']);
            return $this->getShop($shop)->getData()[0];
        } else {
            return [];
        }
    }

    /*** MERCATO TABLE CONTROLS ***/

    /**
     * @fn existObject
     * @note Controlla se un oggetto esiste
     * @param int $id
     * @param int $shop
     * @return bool
     * @throws Throwable
     */
    public function existObject(int $id, int $shop): bool
    {
        $data = DB::queryStmt("SELECT * FROM mercato WHERE oggetto=:id AND negozio=:negozio LIMIT 1", ['id' => $id, 'negozio' => $shop]);
        return ($data->getNumRows() > 0);
    }

    /**
     * @fn existShop
     * @note Controlla se un negozio esiste
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function existShop(int $id): bool
    {
        $data = DB::queryStmt("SELECT * FROM mercato_negozi  WHERE id=:id LIMIT 1",[
            'id' => $id,
        ]);
        return ($data->getNumRows() > 0);
    }

    /*** LISTS ***/

    /**
     * @fn listShops
     * @note Crea le select dei negozi
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listShops(int $selected = 0): string
    {
        $list = $this->getAllShops('id,nome');
        return Template::getInstance()->startTemplate()->renderSelect('id','nome',$selected,$list);
    }


    /*** SHOPS VISUAL */

    /**
     * @fn getShopVisual
     * @note Estrae la visualizzazione della lista dei negozi
     * @return string
     * @throws Throwable
     */
    public function getShopVisual(): string
    {

        $list = $this->getAllShops();
        $data = [];

        foreach ( $list as $shop ) {
            $id = Filters::int($shop['id']);
            $nome = Filters::out($shop['nome']);
            $img = Filters::out($shop['immagine']);
            $descr = Filters::text($shop['descrizione']);

            $data[] = [
                'id' => $id,
                'nome' => $nome,
                'img' => $img,
                'descr' => $descr,
            ];

        }

        return Template::getInstance()->startTemplate()->render('mercato/shop',['data' => $data]);
    }

    /*** OBJECTS VISUAL ***/

    /**
     * @fn buyObject
     * @note Azione di acquisto di un oggetto
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function buyObject(array $post): array
    {

        $obj_class = Oggetti::getInstance();
        $object = Filters::int($post['object']);
        $shop = Filters::int($post['shop']);

        if ( $obj_class->existObject($object) && $this->existObject($object, $shop) ) {

            $mercato_data = $this->getObject($object, $shop);

            $costo = Filters::int($mercato_data['costo']);
            $quantity = Filters::Int($mercato_data['quantity']);

            if ( $quantity > 0 ) {
                $personaggio = Personaggio::getPgData($this->me_id, 'soldi,banca');
                $soldi = Filters::int($personaggio['soldi']);
                $banca = Filters::int($personaggio['banca']);

                if ( $costo <= $soldi ) {
                    $cell = 'soldi';
                    $text = 'Portafoglio';
                } else if ( $costo <= $banca ) {
                    $cell = 'banca';
                    $text = 'Banca';
                } else {
                    return ['response' => false, 'mex' => 'Non hai abbastanza denaro.'];
                }

                Personaggio::updatePgData($this->me_id, "{$cell}={$cell}-:costo", ['costo' => $costo]);
                Oggetti::getInstance()->addObjectToPg($object, $this->me_id);

                if ( $quantity > 1 ) {
                    DB::queryStmt("UPDATE mercato SET quantity = quantity - 1 WHERE oggetto=:object LIMIT 1",[
                        'object' => $object,
                    ]);
                } else {
                    DB::queryStmt("DELETE FROM mercato WHERE oggetto=:object",[
                        'object' => $object,
                    ]);
                }

                return ['response' => true, 'mex' => "Oggetto acquistato. Soldi sottratti da '{$text}'."];
            } else {
                return ['response' => false, 'mex' => "Oggetto non disponibile."];
            }
        } else {
            return ['response' => false, 'mex' => "Oggetto inesistente o non esistente in questo negozio."];
        }

    }


    /*** MANAGEMENT ****/

    /**
     * @fn insertShopObj
     * @note Inserisce un nuovo oggetto in un negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertShopObj(array $post): array
    {

        if ( $this->manageShopObjectsPermission() ) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);
            $quantity = Filters::int($post['quantity']);
            $costo = Filters::int($post['costo']);

            if ( !$this->existObject($obj, $shop) ) {

                DB::queryStmt("INSERT INTO mercato (negozio, oggetto, quantity, costo) VALUES (:negozio, :oggetto, :quantity, :costo)",[
                    'negozio' => $shop,
                    'oggetto' => $obj,
                    'quantity' => $quantity,
                    'costo' => $costo,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto inserito correttamente in negozio.',
                    'swal_type' => 'success',
                ];
            } else {

                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto già presente in negozio. Procedere alla modifica o alla sua eliminazione.',
                    'swal_type' => 'error',
                ];
            }

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
     * @fn editShopObj
     * @note Modifica un oggetto in un negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editShopObj(array $post): array
    {

        if ( $this->manageShopObjectsPermission() ) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);
            $quantity = Filters::int($post['quantity']);
            $costo = Filters::int($post['costo']);

            if ( $this->existObject($obj, $shop) ) {

                DB::queryStmt("UPDATE mercato SET quantity=:quantity, costo=:costo WHERE negozio=:negozio AND oggetto=:oggetto LIMIT 1",[
                    'negozio' => $shop,
                    'oggetto' => $obj,
                    'quantity' => $quantity,
                    'costo' => $costo,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto modificato correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto non presente in negozio. Procedere al suo inserimento.',
                    'swal_type' => 'error',
                ];
            }

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
     * @fn deleteShopObj
     * @note Elimina un oggetto in un negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteShopObj(array $post): array
    {

        if ( $this->manageShopObjectsPermission() ) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);

            if ( $this->existObject($obj, $shop) ) {

                DB::queryStmt("DELETE FROM mercato WHERE oggetto=:obj AND negozio=:shop",[
                    'obj' => $obj,
                    'shop' => $shop,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto eliminato correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto non presente in negozio. Procedere al suo inserimento.',
                    'swal_type' => 'error',
                ];
            }
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
     * @fn insertShop
     * @note Inserisce un nuovo negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function insertShop(array $post): array
    {

        if ( $this->manageShopPermission() ) {

            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);

            DB::queryStmt("INSERT INTO negozi (nome, descrizione, immagine) VALUES (:nome, :descrizione, :immagine)",[
                'nome' => $nome,
                'descrizione' => $descr,
                'immagine' => $img,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Negozio creato correttamente.',
                'swal_type' => 'success',
                'shop_list' => $this->listShops(),
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
     * @fn editShop
     * @note Modifica un negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editShop(array $post): array
    {

        if ( $this->manageShopPermission() ) {

            $shop = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);

            if ( $this->existShop($shop) ) {

                DB::queryStmt("UPDATE negozi SET nome=:nome, descrizione=:descrizione, immagine=:immagine WHERE id=:id LIMIT 1",[
                    'id' => $shop,
                    'nome' => $nome,
                    'descrizione' => $descr,
                    'immagine' => $img,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Negozio modificato correttamente.',
                    'swal_type' => 'success',
                    'shop_list' => $this->listShops(),
                ];
            } else {

                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Negozio inesistente',
                    'swal_type' => 'error',
                ];
            }

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
     * @fn deleteShop
     * @note Elimina un negozio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deleteShop(array $post): array
    {

        if ( $this->manageShopPermission() ) {

            $shop = Filters::int($post['id']);

            if ( $this->existShop($shop) ) {

                DB::queryStmt("DELETE FROM negozi WHERE id=:id LIMIT 1",[
                    'id' => $shop,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Negozio eliminato correttamente.',
                    'swal_type' => 'success',
                    'shop_list' => $this->listShops(),
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Negozio inesistente',
                    'swal_type' => 'error',
                ];
            }
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