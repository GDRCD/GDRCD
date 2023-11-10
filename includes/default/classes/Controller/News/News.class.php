<?php

class News extends BaseClass
{
    public bool $news_enabled = false;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();
        $this->news_enabled = Functions::get_constant('NEWS_ENABLED');
    }


    /*** GETTER ***/

    /**
     * @fn newsEnabled
     * @note Controllo se le news sono abilitate
     * @return bool
     */
    public function newsEnabled(): bool
    {
        return $this->news_enabled;
    }


    /*** ROUTING ***/

    /**
     * @fn loadPage
     * @note Carica la pagina richiesta
     * @param string $op
     * @return string
     */
    public function loadPage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            default:
                $page = 'list/index.php';
                break;

            // Lista news
            case 'read':
                $page = 'read/index.php';
                break;
        }

        return $page;
    }


    /**** TABLE HELPERS ****/

    /**
     * @fn getNews
     * @note Ottieni una news
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getNews
    (int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM news WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllNews
     * @note Ottieni tutte le news
     * @param bool $active
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllNews(bool $active = true, string $val = '*'): DBQueryInterface
    {
        $extra_query = '';

        if ( $active ) {
            $extra_query = ' AND attiva = true ';
        }

        return DB::queryStmt("SELECT {$val} FROM news WHERE 1 {$extra_query} ORDER BY creata_il DESC", []);
    }

    /**
     * @fn getAllNewsByType
     * @note Ottieni tutte le news
     * @param int $type
     * @param bool $active
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllNewsByType(int $type, bool $active = true, string $val = '*'): DBQueryInterface
    {
        $extra_query = '';

        if ( $active ) {
            $extra_query = ' AND attiva = true ';
        }

        return DB::queryStmt("SELECT {$val} FROM news WHERE tipo=:type {$extra_query}", [
            'type' => $type,
        ]);
    }

    /**
     * @fn getNewsRead
     * @note Ottieni la riga di lettura di una news
     * @param int $news_id
     * @param int $pg_id
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getNewsRead(int $news_id, int $pg_id): DBQueryInterface
    {
        return DB::queryStmt("SELECT * FROM news_lette WHERE news = :news AND personaggio = :pg", ['news' => $news_id, 'pg' => $pg_id]);
    }

    /**
     * @fn getCountNewsToRead
     * @note Ottiene il numero di news da leggere
     * @param int $pg_id
     * @return int
     * @throws Throwable
     */
    public function getCountNewsToRead(int $pg_id): int
    {
        $news_list = News::getInstance()->getAllNews();
        $total = 0;

        foreach ( $news_list as $news ) {
            $total += !$this->existRead($news['id'], $pg_id);
        }

        return $total;
    }

    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageNews
     * @note Controlla se l'utente ha i permessi per gestire le news
     * @return bool
     * @throws Throwable
     */
    public function permissionManageNews(): bool
    {
        return Permissions::permission('MANAGE_NEWS');
    }

    /**
     * @fn permissionReadNews
     * @note Controlla se l'utente ha i permessi per leggere le news
     * @param int $news_id
     * @return bool
     * @throws Throwable
     */
    public function permissionReadNews(int $news_id): bool
    {
        $news_data = $this->getNews($news_id, 'tipo,attiva');
        return Permissions::permission('MANAGE_NEWS') || (Filters::bool($news_data['attiva']) && NewsTipo::getInstance()->isTypeActive(Filters::int($news_data['tipo'])));
    }


    /*** CONTROLS ***/

    /**
     * @fn existRead
     * @note Controlla se una news è stata letta
     * @param int $news_id
     * @param int $pg_id
     * @return bool
     * @throws Throwable
     */
    public function existRead(int $news_id, int $pg_id): bool
    {
        return $this->getNewsRead($news_id, $pg_id)->getNumRows() > 0;
    }

    /**** LISTS ***/

    /**
     * @fn listNews
     * @note Ottieni una lista di news
     * @param bool $active
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listNews(bool $active = true, int $selected = 0): string
    {
        $news = $this->getAllNews($active);
        return Template::getInstance()->startTemplate()->renderSelect('id', 'titolo', $selected, $news);
    }


    /*** AJAX ***/

    /**
     * @fn ajaxNewsData
     * @note Estrae i dati di una news
     * @param array $post
     * @return DBQueryInterface|void
     * @throws Throwable
     */
    public function ajaxNewsData(array $post)
    {
        if ( $this->permissionManageNews() ) {
            $id = Filters::int($post['id']);
            return $this->getNews($id)->getData()[0];
        }
    }

    /**
     * @fn ajaxFrameText
     * @note Ritorna il testo del frame laterale
     * @return array
     * @throws Throwable
     */
    public function ajaxFrameText(): array
    {
        return ['text' => $this->renderFrameText()];
    }

    /*** RENDER ***/

    /**
     * @fn renderFrameText
     * @note Renderizza il frame per la news
     * @return string
     * @throws Throwable
     */
    public function renderFrameText(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'news/news_frame',
            [
                'new_news' => $this->getCountNewsToRead($this->me_id),
            ]
        );
    }

    /**
     * @fn renderNewsTitle
     * @note Renderizza il titolo della news
     * @param int $news_id
     * @return string
     * @throws Throwable
     */
    public function renderNewsTitle(int $news_id): string
    {
        $news_data = $this->getNews($news_id);
        return Filters::out($news_data['titolo']);
    }


    /**** RENDER LIST ****/

    /**
     * @fn newsList
     * @note Renderizza la lista delle news
     * @return string
     * @throws Throwable
     */
    public function newsList(): string
    {

        return Template::getInstance()->startTemplate()->renderTable(
            'news/news_list',
            $this->renderNewsList()
        );

    }

    /**
     * @fn renderNewsList
     * @note Renderizza la lista delle news
     * @return array
     * @throws Throwable
     */
    public function renderNewsList(): array
    {
        $news = [];
        $manage_permission = $this->permissionManageNews();
        $news_types = NewsTipo::getInstance()->getAllNewsType(!$manage_permission);

        foreach ( $news_types as $news_type ) {
            $type_id = Filters::int($news_type['id']);

            $news[$type_id] = [
                'type_data' => $news_type,
                'news' => [],
            ];

            $news_list = $this->getAllNewsByType($type_id, !$manage_permission);

            foreach ( $news_list as $news_data ) {
                $news_id = Filters::int($news_data['id']);

                $news[$type_id]['news'][] = [
                    'id' => $news_id,
                    'title' => Filters::out($news_data['titolo']),
                    'description' => Filters::out($news_data['descrizione']),
                    'author' => Filters::out($news_data['autore']),
                    'real_author_id' => Filters::int($news_data['creata_da']),
                    'real_author_name' => Personaggio::nameFromId(Filters::int($news_data['creata_da'])),
                    'date' => Filters::date($news_data['creata_il'], 'd/m/Y'),
                    'to_read' => !$this->existRead($news_id, $this->me_id),
                    'active' => Filters::bool($news_data['attiva']),
                ];
            }

        }

        return ["news" => $news, 'manage_permission' => $manage_permission];
    }

    /**** RENDER NEWS ****/

    /**
     * @fn newsRead
     * @note Renderizza la pagina di lettura di una news
     * @param int $news_id
     * @return string
     * @throws Throwable
     */
    public function newsRead(int $news_id): string
    {
        return Template::getInstance()->startTemplate()->render(
            'news/news_read',
            $this->renderNewsRead($news_id)
        );
    }

    /**
     * @fn renderNewsRead
     * @note Renderizza la pagina di lettura di una news
     * @param int $news_id
     * @return array
     * @throws Throwable
     */
    public function renderNewsRead(int $news_id): array
    {
        $news = $this->getNews($news_id);

        $this->newsSetRead($news_id, $this->me_id);

        $news_data = [
            "id" => Filters::int($news['id']),
            "title" => Filters::out($news['titolo']),
            "text" => Filters::out($news['testo']),
            "author" => Filters::out($news['autore']),
            "real_author_id" => Filters::int($news['creata_da']),
            "real_author_name" => Personaggio::nameFromId(Filters::int($news['creata_da'])),
            "date" => Filters::date($news['creata_il'], 'd/m/Y'),
            'manage_permission' => $this->permissionManageNews(),
        ];

        return ["news" => $news_data];
    }


    /*** FUNCTIONS ***/

    /**
     * @fn newsSetRead
     * @note Imposta la news come letta
     * @param int $news_id
     * @param int $pg_id
     * @return void
     * @throws Throwable
     */
    public function newsSetRead(int $news_id, int $pg_id): void
    {
        if ( !$this->existRead($news_id, $pg_id) ) {
            DB::queryStmt("INSERT INTO news_lette (personaggio,news) VALUES (:personaggio,:news)", [
                'personaggio' => $pg_id,
                'news' => $news_id,
            ]);
        }
    }

    /**** GESTIONE ****/

    /**
     * @fn newNews
     * @note Inserisce una nuova news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newNews(array $post): array
    {

        if ( $this->permissionManageNews() ) {

            $author = Filters::in($post['autore']);
            $title = Filters::in($post['titolo']);
            $description = Filters::in($post['testo']);
            $type = Filters::int($post['tipo']);
            $active = Filters::checkbox($post['attiva']);

            DB::queryStmt("INSERT INTO news (autore, titolo, testo, tipo, attiva,creata_da) VALUES (:author, :title, :description, :type, :active,:created_by)", [
                'author' => $author,
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'active' => $active,
                'created_by' => $this->me_id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'News creata correttamente.',
                'swal_type' => 'success',
                'news_list' => $this->listNews(false),
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
     * @fn modNews
     * @note Modifica una news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modNews(array $post): array
    {

        if ( $this->permissionManageNews() ) {

            $id = Filters::int($post['id']);
            $author = Filters::in($post['autore']);
            $title = Filters::in($post['titolo']);
            $description = Filters::in($post['testo']);
            $type = Filters::int($post['tipo']);
            $active = Filters::checkbox($post['attiva']);

            DB::queryStmt("UPDATE news SET autore=:author, titolo=:title, testo=:description, tipo=:type, attiva=:active WHERE id=:id", [
                'id' => $id,
                'author' => $author,
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'active' => $active,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'News modificata correttamente.',
                'swal_type' => 'success',
                'news_list' => $this->listNews(false),
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
     * @fn delNews
     * @note Elimina una news
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delNews(array $post): array
    {

        if ( $this->permissionManageNews() ) {

            $news = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM news WHERE id=:id", [
                'id' => $news,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'News eliminata correttamente.',
                'swal_type' => 'success',
                'news_list' => $this->listNews(false),
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