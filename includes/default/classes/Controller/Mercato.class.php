<?php

class Mercato extends BaseClass
{

    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

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

        switch ($op) {
            case 'objects':
                $page = 'mercato_objects.php';
                break;
            default:
                $page = 'mercato_shops.php';
                break;
        }

        return $page;
    }

    /*** PERMISSIONS ***/

    /**
     * @fn manageMercatoPermission
     * @note Controllo se se ho i permessi per gestire il mercato
     * @return bool
     */
    public function manageShopObjectsPermission(): bool
    {
        return Permissions::permission('MANAGE_SHOPS_OBJECTS');
    }

    /**
     * @fn manageShopPermission
     * @note Controllo se se ho i permessi per gestire un negozio
     * @return bool
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
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getObject(int $id, int $shop, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM mercato WHERE oggetto='{$id}' LIMIT 1");
    }

    /**
     * @fn getAllObjects
     * @note Estrae la lista di tutti gli oggetti esistenti
     * @param int $shop
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getAllShopObjects(int $shop, string $val = 'oggetto.*,mercato.*')
    {
        return DB::query("SELECT {$val} 
                FROM mercato 
                LEFT JOIN oggetto ON (oggetto.id = mercato.oggetto)
                WHERE mercato.negozio='{$shop}' AND oggetto.id IS NOT NULL AND mercato.quantity > 0
                ORDER BY nome", 'result');
    }

    /**
     * @fn getAllShops
     * @note Estrae la lista di tutti i negozi
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getAllShops(string $val = '*')
    {
        return DB::query("SELECT {$val} 
                FROM mercato_negozi 
                WHERE 1 ORDER BY nome", 'result');
    }

    /**
     * @fn getShop
     * @note Estrae tutti i negozi presenti
     * @param int $obj
     * @param string $val
     * @return bool|int|mixed|string
     */
    private function getShopFromObject(int $obj, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM mercato WHERE oggetto='{$obj}' LIMIT 1");
    }

    /**
     * @fn getShop
     * @note Estrae tutti i negozi presenti
     * @param int $obj
     * @param string $val
     * @return bool|int|mixed|string
     */
    public function getShop(int $shop, string $val = '*')
    {
        return DB::query("SELECT {$val} FROM mercato_negozi WHERE id='{$shop}' LIMIT 1");
    }

    /*** AJAX ***/

    /**
     * @fn ajaxGetShop
     * @note Ajax data for shop
     * @param array $post
     * @return array
     */
    public function ajaxGetShop(array $post): array
    {
        if ($this->manageShopPermission()) {
            $shop = Filters::int($post['shop']);

            return $this->getShop($shop);
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
     */
    public function existObject(int $id, int $shop): bool
    {
        $data = DB::query("SELECT * FROM mercato WHERE oggetto='{$id}' AND negozio='{$shop}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
    }

    /**
     * @fn existShop
     * @note Controlla se un negozio esiste
     * @param int $id
     * @return bool
     */
    public function existShop(int $id): bool
    {
        $data = DB::query("SELECT * FROM mercato_negozi  WHERE id='{$id}' LIMIT 1");
        return (DB::rowsNumber($data) > 0);
    }

    /*** LISTS ***/

    /**
     * @fn listObjectTypes
     * @note Crea le select delle tipologie oggetto
     * @param int $selected
     * @return string
     */
    public function listShops(int $selected = 0): string
    {
        $html = '<option value=""></option>';
        $list = $this->getAllShops('id,nome');

        foreach ($list as $row) {
            $id = Filters::int($row['id']);
            $nome = Filters::in($row['nome']);
            $sel = ($selected == $id) ? 'selected' : '';

            $html .= "<option value='{$id}' {$sel}>{$nome}</option>";
        }

        return $html;
    }


    /*** SHOPS VISUAL */

    /**
     * @fn getShopVisual
     * @note Estrae la visualizzazione della lista dei negozi
     * @return string
     */
    public function getShopVisual(): string
    {

        $html = '';
        $list = $this->getAllShops();

        foreach ($list as $shop) {
            $id = Filters::int($shop['id']);
            $nome = Filters::out($shop['nome']);
            $img = Filters::out($shop['immagine']);
            $descr = Filters::text($shop['descrizione']);

            $html .= "<div class='single_shop'>";
            $html .= "<div class='shop_img'><img src='/themes/advanced/imgs/shops/{$img}'></div>";
            $html .= "<div class='shop_name'>
                        <a href='/main.php?page=servizi_mercato&op=objects&shop={$id}'>{$nome}</a>
                      </div>";
            $html .= "</div>";
        }

        return $html;
    }

    /*** OBJECTS VISUAL ***/

    /**
     * @fn buyObject
     * @note Azione di acquisto di un oggetto
     * @param array $post
     * @return array
     */
    public function buyObject(array $post): array
    {

        $obj_class = Oggetti::getInstance();
        $object = Filters::int($post['object']);
        $shop = Filters::int($post['shop']);

        if ($obj_class->existObject($object) && $this->existObject($object, $shop)) {

            $mercato_data = $this->getObject($object, $shop);

            $costo = Filters::int($mercato_data['costo']);
            $quantita = Filters::Int($mercato_data['quantity']);

            if ($quantita > 0) {
                $personaggio = Personaggio::getPgData($this->me_id, 'soldi,banca');
                $soldi = Filters::int($personaggio['soldi']);
                $banca = Filters::int($personaggio['banca']);

                if ($costo <= $soldi) {
                    $cell = 'soldi';
                    $text = 'Portafoglio';
                } elseif ($costo <= $banca) {
                    $cell = 'banca';
                    $text = 'Banca';
                } else {
                    return ['response' => false, 'mex' => 'Non hai abbastanza denaro.'];
                }

                Personaggio::updatePgData($this->me_id, "{$cell}={$cell}-{$costo}");
                Oggetti::addObjectToPg($object, $this->me_id);

                if ($quantita > 1) {
                    DB::query("UPDATE mercato SET quantity = quantity - 1 WHERE oggetto='{$object}' LIMIT 1");
                } else {
                    DB::query("DELETE FROM mercato WHERE oggetto='{$object}'");
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
     */
    public function insertShopObj(array $post): array
    {

        if ($this->manageShopObjectsPermission()) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);
            $quantity = Filters::int($post['quantity']);
            $costo = Filters::int($post['costo']);

            if (!$this->existObject($obj, $shop)) {

                DB::query("INSERT INTO mercato(oggetto, negozio, quantity,costo) 
                            VALUES ('{$obj}','{$shop}','{$quantity}','{$costo}')");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto inserito correttamente in negozio.',
                    'swal_type' => 'success'
                ];
            } else {

                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto già presente in negozio. Procedere alla modifica o alla sua eliminazione.',
                    'swal_type' => 'error'
                ];
            }

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
     * @fn editShopObj
     * @note Modifica un oggetto in un negozio
     * @param array $post
     * @return array
     */
    public function editShopObj(array $post): array
    {

        if ($this->manageShopObjectsPermission()) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);
            $quantity = Filters::int($post['quantity']);
            $costo = Filters::int($post['costo']);

            if ($this->existObject($obj, $shop)) {

                DB::query("UPDATE mercato SET quantity='{$quantity}',costo='{$costo}' WHERE oggetto='{$obj}' AND negozio='{$shop}' LIMIT 1");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto modificato correttamente.',
                    'swal_type' => 'success'
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto non presente in negozio. Procedere al suo inserimento.',
                    'swal_type' => 'error'
                ];
            }

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
     * @fn deleteShopObj
     * @note Elimina un oggetto in un negozio
     * @param array $post
     * @return array
     */
    public function deleteShopObj(array $post): array
    {

        if ($this->manageShopObjectsPermission()) {

            $shop = Filters::int($post['negozio']);
            $obj = Filters::int($post['oggetto']);

            if ($this->existObject($obj, $shop)) {

                DB::query("DELETE FROM mercato WHERE oggetto='{$obj}' AND negozio='{$shop}'");


                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Oggetto eliminato correttamente.',
                    'swal_type' => 'success'
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Oggetto non presente in negozio. Procedere al suo inserimento.',
                    'swal_type' => 'error'
                ];
            }
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
     * @fn insertShop
     * @note Inserisce un nuovo negozio
     * @param array $post
     * @return array
     */
    public function insertShop(array $post): array
    {

        if ($this->manageShopPermission()) {

            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);

            DB::query("INSERT INTO mercato_negozi(nome, descrizione, immagine) 
                            VALUES ('{$nome}','{$descr}','{$img}')");

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Negozio creato correttamente.',
                'swal_type' => 'success',
                'shop_list' => $this->listShops()
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
     * @fn editShop
     * @note Modifica un negozio
     * @param array $post
     * @return array
     */
    public function editShop(array $post): array
    {

        if ($this->manageShopPermission()) {

            $shop = Filters::int($post['id']);
            $nome = Filters::in($post['nome']);
            $descr = Filters::in($post['descrizione']);
            $img = Filters::in($post['immagine']);

            if ($this->existShop($shop)) {

                DB::query("UPDATE mercato_negozi SET nome='{$nome}',descrizione='{$descr}',immagine='{$img}' WHERE id='{$shop}' LIMIT 1");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Negozio modificato correttamente.',
                    'swal_type' => 'success',
                    'shop_list' => $this->listShops()
                ];
            } else {

                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Negozio inesistente',
                    'swal_type' => 'error'
                ];
            }

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
     * @fn deleteShop
     * @note Elimina un negozio
     * @param array $post
     * @return array
     */
    public function deleteShop(array $post): array
    {

        if ($this->manageShopPermission()) {

            $shop = Filters::int($post['id']);

            if ($this->existShop($shop)) {

                DB::query("DELETE FROM mercato_negozi WHERE id='{$shop}'");

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Negozio eliminato correttamente.',
                    'swal_type' => 'success',
                    'shop_list' => $this->listShops()
                ];
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Operazione fallita!',
                    'swal_message' => 'Negozio inesistente',
                    'swal_type' => 'error'
                ];
            }
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