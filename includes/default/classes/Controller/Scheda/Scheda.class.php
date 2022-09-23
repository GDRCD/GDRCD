<?php

class Scheda extends BaseClass
{

    /**
     * @fn __construct
     * @note Class constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }


    /*** CONTROLS ***/

    /**
     * @fn available
     * @note Controlla se una scheda e' accessibile.
     * @param $id_pg
     * @return bool
     */
    public function available($id_pg): bool
    {
        $id_pg = Filters::int($id_pg);
        return (Personaggio::pgExist($id_pg));
    }

    /***** PERMESSI ***/

    /**
     * @fn permissionUpdateCharacter
     * @note Controlla se un personaggio puo' essere modificato.
     * @param int $id_pg
     * @return bool
     */
    public function permissionUpdateCharacter(int $id_pg): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('SCHEDA_UPDATE');
    }

    /**
     * @fn permissionStatusCharacter
     * @note Controlla se lo status di un personaggio puo' essere modificato.
     * @return bool
     */
    public function permissionStatusCharacter(): bool
    {
        return Permissions::permission('SCHEDA_STATUS_MANAGE');
    }

    /**
     * @fn permissionBanCharacter
     * @note Controlla se un personaggio puo' essere bannato.
     * @return bool
     */
    public function permissionBanCharacter(): bool
    {
        return Permissions::permission('SCHEDA_BAN');
    }

    /**
     * @fn permissionAdministrationCharacter
     * @note Controlla se le info fondamentali di un personaggio possono essere modificate.
     * @return bool
     */
    public function permissionAdministrationCharacter(): bool
    {
        return Permissions::permission('SCHEDA_ADMINISTRATION_MANAGE');
    }

    /**** INDEX ****/

    /**
     * @fn loadCharacterPage
     * @note Routing delle pagine della scheda
     * @param string $op
     * @return string
     */
    public function loadCharacterPage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            default:
                $page = 'main.php';
                break;

            // ABILITA
            case 'abilita':
                $page = 'abilita/index.php';
                break;

            // STATISTICHE
            case 'stats':
                $page = 'stats/index.php';
                break;

            // CONTATTi
            case 'contatti':
                $page = 'contatti/index.php';
                break;

            case 'contatti_new':
                $page = 'contatti/contact_new.php';
                break;

            case 'contatti_view':
                $page = 'contatti/note_view.php';
                break;

            case 'contatti_nota_new':
                $page = 'contatti/note_new.php';
                break;

            case 'contatti_nota_details':
                $page = 'contatti/note_details.php';
                break;

            // STORIA
            case 'storia':
                $page = 'storia.php';
                break;

            // TRANSAZIONI
            case 'transazioni':
                $page = 'transazioni/transazioni.php';
                break;

            // OGGETTI
            case 'oggetti':
                $page = 'oggetti/oggetti.php';
                break;

            // DIARIO
            case 'diario':
                $page = 'diario/index.php';
                break;

            case 'diario_view':
                $page = 'diario/view.php';
                break;

            case 'diario_new':
                $page = 'diario/new.php';
                break;

            case 'diario_edit':
                $page = 'diario/edit.php';
                break;

            //LOG
            case 'log':
                $page = 'log.php';
                break;

            //MODIFICA
            case 'modifica':
                $page = 'modifica/index.php';
                break;

            //AMMINISTRA
            case 'amministra':
                $page = 'amministra/index.php';
                break;

            //REGISTRAZIONI
            case 'registrazioni':
                $page = 'registrazioni/index.php';
                break;

            case 'registrazioni_new':
                $page = 'registrazioni/new.php';
                break;

            case 'registrazioni_edit':
                $page = 'registrazioni/edit.php';
                break;

            case 'registrazioni_view':
                $page = 'registrazioni/view.php';
                break;

        }

        return $page;
    }

    /**** RENDER ****/

    /**
     * @fn getGroupIcons
     * @note Funzione che si occupa dell'estrazione delle icone della scheda
     * @param string $pg_id
     * @return string
     */
    private function getGroupIcons(string $pg_id): string
    {
        # Filtro il mittente passato
        $pg_id = Filters::int($pg_id);

        $icons = '';

        if ( Gruppi::getInstance()->activeGroupIconChat() ) {
            $roles = PersonaggioRuolo::getInstance()->getAllCharacterRolesWithRoleData($pg_id);

            foreach ( $roles as $role ) {
                $link = Router::getImgsDir() . $role['immagine'];
                $icons .= "<img src='{$link}' title='{$role['gruppo_nome']} - {$role['nome']}' alt='{$role['nome']}'>";
            }
        }

        return $icons;
    }

    /**
     * @fn getRaceIcon
     * @note Funzione che si occupa dell'estrazione dell'icone della razza
     * @param string $pg_id
     * @return string
     */
    private function getRaceIcon(string $pg_id): string
    {
        # Filtro il mittente passato
        $pg_id = Filters::int($pg_id);
        $character_data = Personaggio::getPgData($pg_id, 'razza');
        $race_id = Filters::int($character_data['razza']);
        $race_data = Razze::getInstance()->getRace($race_id, 'icon,sing_m,sing_f');
        $icon = Filters::out($race_data['icon']);
        $name = Filters::out($race_data['nome']);

        $link = Router::getImgsDir() . $icon;
        return "<img src='{$link}' title='{$name}' alt='{$name}'>";
    }

    /**
     * @fn renderMainPage
     * @note Renderizza la scheda pg
     * @param int $id_pg
     * @return array
     */
    public function renderMainPage(int $id_pg): array
    {

        $character_data = Personaggio::getPgData($id_pg);

        return [
            'id' => Filters::out($character_data['id']),
            'character_data' => $character_data,
            'groups_icons' => $this->getGroupIcons($id_pg),
            'race_icon' => $this->getRaceIcon($id_pg),
            'registration_day' => Filters::date($character_data['data_iscrizione'], 'd/m/Y'),
            'last_login' => Filters::date($character_data['ora_entrata'], 'd/m/Y'),
        ];
    }

    /**
     * @fn characterPage
     * @note Renderizza la scheda pg
     * @param int $id_pg
     * @return string
     */
    public function characterMainPage(int $id_pg): string
    {
        return Template::getInstance()->startTemplate()->render(
            'scheda/main',
            $this->renderMainPage($id_pg)
        );
    }


    /**** FUNCTIONS ***/

    /**
     * @fn updateCharacterData
     * @note Aggiorna i dati del personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function updateCharacterData(array $post): array
    {
        $id_pg = Filters::int($post['pg']);

        if ( $this->permissionUpdateCharacter($id_pg) ) {

            $cognome = Filters::in($post['cognome']);
            $url_img = Filters::in($post['url_img']);
            $url_img_chat = Filters::in($post['url_img_chat']);
            $online_status = Filters::int($post['online_status']);
            $descrizione = Filters::in($post['descrizione']);
            $storia = Filters::in($post['storia']);
            $url_media = Filters::in($post['url_media']);
            $blocca_media = Filters::checkbox($post['blocca_media']);

            Personaggio::updatePgData(
                $id_pg,
                'cognome = :cognome, url_img = :url_img, url_img_chat = :url_img_chat, 
                        online_status = :online_status, descrizione = :descrizione, storia = :storia, 
                        url_media = :url_media, blocca_media = :blocca_media',
                [
                    'cognome' => $cognome,
                    'url_img' => $url_img,
                    'url_img_chat' => $url_img_chat,
                    'online_status' => $online_status,
                    'descrizione' => $descrizione,
                    'storia' => $storia,
                    'url_media' => $url_media,
                    'blocca_media' => $blocca_media,
                    'id' => $id_pg,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Modifica effettuata correttamente.',
                'swal_type' => 'success',
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
     * @fn updateCharacterStatus
     * @note Aggiorna lo stato del personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function updateCharacterStatus(array $post): array
    {

        if ( $this->permissionStatusCharacter() ) {

            $id_pg = Filters::int($post['pg']);
            $stato = Filters::in($post['stato']);
            $salute = Filters::int($post['salute']);

            Personaggio::updatePgData(
                $id_pg,
                'stato = :stato, salute = :salute',
                [
                    'stato' => $stato,
                    'salute' => $salute,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Modifica stato effettuata correttamente.',
                'swal_type' => 'success',
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
     * @fn updateAdministrationCharacter
     * @note Aggiorna i dati dell'amministratore del personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function updateAdministrationCharacter(array $post): array
    {

        if ( $this->permissionAdministrationCharacter() ) {

            $id_pg = Filters::int($post['pg']);
            $sesso = Filters::int($post['sesso']);
            $razza = Filters::int($post['razza']);
            $banca = Filters::int($post['banca']);
            $soldi = Filters::int($post['soldi']);

            Personaggio::updatePgData(
                $id_pg,
                'sesso = :sesso, razza = :razza, banca = :banca, soldi = :soldi',
                [
                    'sesso' => $sesso,
                    'razza' => $razza,
                    'banca' => $banca,
                    'soldi' => $soldi,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Modifica amministrazione effettuata correttamente.',
                'swal_type' => 'success',
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
     * @fn banCharacter
     * @note Ban di un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function banCharacter(array $post): array
    {

        if ( $this->permissionBanCharacter() ) {

            $id_pg = Filters::int($post['pg']);
            $esilio = Filters::in($post['esilio']);
            $motivo_esilio = Filters::in($post['motivo_esilio']);

            Personaggio::updatePgData(
                $id_pg,
                'esilio = :esilio, motivo_esilio = :motivo_esilio, autore_esilio = :autore_esilio, data_esilio=NOW()',
                [
                    'esilio' => $esilio,
                    'motivo_esilio' => $motivo_esilio,
                    'autore_esilio' => $this->me_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Ban effettuato correttamente.',
                'swal_type' => 'success',
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