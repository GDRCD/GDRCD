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
    public function getAllShopObjects(int $shop, string $val = 'oggetto.*,mercato.id,mercato.oggetto,mercato.quantity')
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
        $html = '';
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

            $obj_data = $obj_class->getObject($object);
            $mercato_data = $this->getObject($object, $shop);

            $costo = Filters::int($obj_data['costo']);
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
                Personaggio::addObject($object,$this->me_id);

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


}