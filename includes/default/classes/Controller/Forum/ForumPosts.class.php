<?php

class ForumPosts extends Forum
{

    private string|bool $historyActive;

    /**
     * @fn __construct
     * @note Costruttore
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();
        $this->historyActive = Functions::get_constant('FORUM_POST_HISTORY');
    }

    /*** GETTERS ***/

    /**
     * @fn isHistoryActive
     * @note Ritorna se la funzione di storico è attiva
     * @return bool|string
     */
    public function isHistoryActive(): bool|string
    {
        return $this->historyActive;
    }

    /*** TABLES HELPERS ***/

    /**
     * @fn getPost
     * @note Estrapola uno specifico post dal database
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPost(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum_posts WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getPost
     * @note Estrapola uno specifico post dal database
     * @param int $id
     * @param int $pagination
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPostCommentsPaginated(int $id, int $pagination, string $val = '*'): DBQueryInterface
    {
        $comments_number = Functions::get_constant('FORUM_COMMENTS_FOR_PAGE');
        $initial_index = ($pagination - 1) * $comments_number;

        return DB::queryStmt("SELECT {$val} FROM forum_posts 
             WHERE (id=:id OR id_padre =:id_padre )
             ORDER BY id_padre ASC, data ASC
             LIMIT {$initial_index}, {$comments_number}
             ", ['id' => $id, 'id_padre' => $id]);
    }

    /**
     * @fn getForumsPermissionsByType
     * @note Ottieni i permessi dei forum di un tipo
     * @param int $forum_id
     * @return int
     * @throws Throwable
     */
    public function getPostsToReadByForum(int $forum_id): int
    {
        $response = DB::queryStmt(
            "SELECT forum_posts_letti.id FROM forum_posts 
                    LEFT JOIN forum_posts_letti ON forum_posts_letti.post = forum_posts.id AND forum_posts_letti.personaggio = :me
                    WHERE forum_posts.id_forum = :forum AND forum_posts_letti.id IS NULL AND forum_posts.id_padre = 0  AND eliminato=0",
            ['forum' => $forum_id, 'me' => $this->me_id]
        );
        return $response->getNumRows();
    }

    /**
     * @fn getAllPostsByForumPaginated
     * @note Ottiene tutti i post di un forum paginati
     * @param int $forum_id
     * @param int $pagination
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllPostsByForumPaginated(int $forum_id, int $pagination = 1): DBQueryInterface
    {
        $posts_number = Functions::get_constant('FORUM_POSTS_FOR_PAGE');
        $initial_index = ($pagination - 1) * $posts_number;

        return DB::queryStmt("SELECT * FROM forum_posts WHERE id_forum = :forum AND eliminato=0 AND id_padre = 0 ORDER BY importante DESC, `data` DESC LIMIT {$initial_index}, {$posts_number}",
            ['forum' => $forum_id]
        );
    }

    /**
     * @fn getCountPostsByForum
     * @note Ottiene il numero di post di un forum
     * @param int $forum_id
     * @return int
     * @throws Throwable
     */
    public function getCountPostsByForum(int $forum_id): int
    {
        return DB::queryStmt("SELECT * FROM forum_posts WHERE id_forum = :forum AND eliminato=0 AND id_padre = 0 ORDER BY importante DESC, `data` DESC",
            ['forum' => $forum_id]
        )->getNumRows();
    }

    /**
     * @fn getCountPostsAllForum
     * @note Ottiene il conto di tutti i post non letti nel forum
     * @return int
     * @throws Throwable
     */
    public function getCountPostsAllForum(): int
    {
        $forums = Forum::getInstance()->getAllForums();
        $total = 0;

        foreach ( $forums as $forum ) {
            if ( ForumPermessi::getInstance()->permissionForum($forum['id']) ) {
                $total += $this->getPostsToReadByForum($forum['id']);
            }
        }

        return $total;
    }

    /**
     * @fn getOriginalPostId
     * @note Ottiene l'id del post originale di un commento
     * @param int $post_id
     * @return int
     * @throws Throwable
     */
    public function getOriginalPostId(int $post_id): int
    {
        $post_data = $this->getPost($post_id, 'id_padre');
        $id_padre = Filters::int($post_data['id_padre']);

        return ($id_padre > 0) ? $id_padre : $post_id;
    }

    /**
     * @fn getPostHistory
     * @param int $post_id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getPostHistory(int $post_id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum_posts_updates WHERE post = :post ORDER BY modificato_il DESC", ['post' => $post_id]);
    }

    /*** CONTROLS ***/

    /**
     * @fn existRead
     * @note Controlla se un post è stato letto
     * @param int $post_id
     * @param int $pg_id
     * @return bool
     * @throws Throwable
     */
    public function existRead(int $post_id, int $pg_id): bool
    {
        return DB::queryStmt("SELECT * FROM forum_posts_letti WHERE post = :post AND personaggio = :pg", ['post' => $post_id, 'pg' => $pg_id])->getNumRows() > 0;
    }


    /*** AJAX ***/

    /**
     * @fn newPost
     * @note Crea un nuovo post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newPost(array $post): array
    {

        $forum_id = Filters::int($post['forum_id']);

        if ( ForumPermessi::getInstance()->permissionForum($forum_id) ) {

            DB::queryStmt("INSERT INTO forum_posts(id_forum, id_padre, titolo, testo, autore) VALUES (:forum, :padre, :titolo, :testo, :autore)",
                [
                    'forum' => $forum_id,
                    'padre' => 0,
                    'titolo' => Filters::text($post['titolo']),
                    'testo' => Filters::text($post['testo']),
                    'autore' => $this->me_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post inserito correttamente.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn editPost
     * @note Modifica un post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function editPost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);

        if ( ForumPermessi::getInstance()->permissionPostEdit($post_id) ) {

            # Lo storico del post viene salvato comunque, in modo che se viene attivato
            # si possa vedere anche lo storico per post ormai vecchi
            $post_data = $this->getPost($post_id, 'titolo,testo');

            DB::queryStmt("INSERT INTO forum_posts_updates(post,titolo,testo,modificato_da) VALUES(:post,:title,:text,:updated_by)", [
                'post' => $post_id,
                'title' => Filters::text($post_data['titolo']),
                'text' => Filters::text($post_data['testo']),
                'updated_by' => $this->me_id,
            ]);

            DB::queryStmt("UPDATE forum_posts SET titolo=:title, testo=:text, modificato_il=:updated_at, modificato_da=:updated_by WHERE id=:id",
                [
                    'id' => $post_id,
                    'title' => Filters::text($post['titolo']),
                    'text' => Filters::text($post['testo']),
                    'updated_at' => Filters::date(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
                    'updated_by' => $this->me_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post modificato correttamente.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn deletePost
     * @note Elimina un post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deletePost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);
        $pagination = Filters::int($post['pagination']);

        if ( ForumPermessi::getInstance()->permissionPostEdit($post_id) ) {

            DB::queryStmt("UPDATE forum_posts SET eliminato=1 WHERE id=:id",
                [
                    'id' => $post_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post eliminato correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->singlePost($this->getOriginalPostId($post_id), $pagination),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn commentPost
     * @note Crea un nuovo commento
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function commentPost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);

        if ( ForumPermessi::getInstance()->permissionPostComment($post_id) ) {

            # Elimino tutte le letture per quel post, così da farlo comparire come non letto
            DB::queryStmt("DELETE FROM forum_posts_letti WHERE post = :id_post", ['id_post' => $post_id]);

            # Inserisco il commento
            DB::queryStmt("INSERT INTO forum_posts(id_forum, id_padre, titolo, testo, autore) VALUES (:forum, :padre, :titolo, :testo, :autore)",
                [
                    'forum' => 0,
                    'padre' => $post_id,
                    'titolo' => Filters::text($post['titolo']),
                    'testo' => Filters::text($post['testo']),
                    'autore' => $this->me_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post commentato correttamente.',
                'swal_type' => 'success',
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn restorePost
     * @note Ripristina un post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function restorePost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);
        $pagination = Filters::int($post['pagination']);

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            DB::queryStmt("UPDATE forum_posts SET eliminato=0 WHERE id=:id",
                [
                    'id' => $post_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post ripristinato correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->singlePost($this->getOriginalPostId($post_id), $pagination),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn lockPost
     * @note Sblocca/Blocca un post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function lockPost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);
        $pagination = Filters::int($post['pagination']);

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            DB::queryStmt("UPDATE forum_posts SET chiuso = NOT chiuso WHERE id=:id",
                [
                    'id' => $post_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post modificato correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->singlePost($this->getOriginalPostId($post_id), $pagination),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }

    /**
     * @fn importantPost
     * @note Sblocca/Blocca un post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function importantPost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);
        $pagination = Filters::int($post['pagination']);

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            DB::queryStmt("UPDATE forum_posts SET importante = NOT importante WHERE id=:id",
                [
                    'id' => $post_id,
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post modificato correttamente.',
                'swal_type' => 'success',
                'new_view' => $this->singlePost($this->getOriginalPostId($post_id), $pagination),
            ];
        } else {
            return [
                'response' => false,
                'swal_title' => 'Errore!',
                'swal_message' => 'Permesso negato.',
                'swal_type' => 'error',
            ];
        }

    }


    /*** RENDER ***/

    /**
     * @fn renderPostName
     * @note Renderizza il nome di un post
     * @param int $post_id
     * @return string
     * @throws Throwable
     */
    public function renderPostName(int $post_id): string
    {
        return Filters::string($this->getPost($post_id, 'titolo')['titolo']);
    }

    /**
     * @fn forumsList
     * @note Ritorna la lista renderizzata dei forum
     * @param int $forum_id
     * @param int $pagination
     * @return string
     * @throws Throwable
     */
    public function postsList(int $forum_id, int $pagination): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'forum/posts_list',
            $this->renderPostsList($forum_id, $pagination)
        );
    }

    /**
     * @fn renderForumsList
     * @note Renderizza la lista dei forum
     * @param int $forum_id
     * @param int $pagination
     * @return array
     * @throws Throwable
     */
    public function renderPostsList(int $forum_id, int $pagination): array
    {
        $row_data = [];

        $posts = $this->getAllPostsByForumPaginated($forum_id, $pagination);

        foreach ( $posts as $post ) {
            $array = [
                'id' => Filters::int($post['id']),
                'author_id' => Filters::int($post['autore']),
                'author' => Personaggio::nameFromId(Filters::int($post['autore'])),
                'title' => Filters::out($post['titolo']),
                'date' => Filters::date($post['data'], 'd/m/Y H:i:s'),
                'date_last' => Filters::date($post['data_ultimo'], 'd/m/Y H:i'),
                'closed' => Filters::bool($post['chiuso']),
                'important' => Filters::bool($post['importante']),
                'to_read' => !$this->existRead($post['id'], $this->me_id),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Titolo',
            'Autore',
            'Data',
            'Ultimo messaggio',
        ];

        $links = [
            ['href' => "/main.php?page=forum/index&op=post_new&forum_id=" . $forum_id, 'text' => 'Nuovo post'],
            ['href' => "/main.php?page=forum/index", 'text' => 'Indietro'],
        ];

        return [
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
            'footer_pagination' => $this->renderPostsPagination($forum_id, $pagination),
        ];
    }

    /**
     * @fn renderPagination
     * @note Renderizza la paginazione per la lista posts
     * @param int $forum_id
     * @param int $pagination
     * @return array
     * @throws Throwable
     */
    public function renderPostsPagination(int $forum_id, int $pagination = 1): array
    {

        $posts_number = $this->getCountPostsByForum($forum_id);
        $posts_number_for_page = Functions::get_constant('FORUM_POSTS_FOR_PAGE');

        $pages = [];

        for ( $i = 1; $i <= ceil($posts_number / $posts_number_for_page); $i++ ) {
            $pages[] = [
                'url' => "/main.php?page=forum/index&op=posts&forum_id={$forum_id}&pagination={$i}",
                'page' => $i,
            ];
        }

        return [
            "current" => $pagination,
            "pages" => $pages,
        ];
    }

    /**
     * @fn singlePost
     * @note Ritorna il post renderizzato
     * @param int $post_id
     * @param int $pagination
     * @return string
     * @throws Throwable
     */
    public function singlePost(int $post_id, int $pagination = 1): string
    {
        $array = [];
        $original_post = $this->getPost($post_id);
        $admin_permission = ForumPermessi::getInstance()->permissionForumAdmin();

        if ( !Filters::bool($original_post['eliminato']) || $admin_permission ) {
            $posts = $this->getPostCommentsPaginated($post_id, $pagination);

            foreach ( $posts as $post ) {
                $author_data = Personaggio::getPgData(Filters::int($post['autore']), 'url_img, nome, cognome');
                $deleted = Filters::bool($post['eliminato']);

                if ( !$deleted || $admin_permission ) {
                    $array[] = [
                        'id' => Filters::int($post['id']),
                        'author_id' => Filters::int($post['autore']),
                        'author_name' => Personaggio::nameFromId(Filters::int($post['autore'])),
                        'author_avatar' => Filters::out($author_data['url_img']),
                        'text' => Filters::out($post['testo']),
                        'date' => Filters::date($post['data'], 'd/m/Y H:i:s'),
                        'admin_permission' => $admin_permission,
                        'edit_permission' => ForumPermessi::getInstance()->permissionPostEdit($post['id']),
                        'delete_permission' => ForumPermessi::getInstance()->permissionPostEdit($post['id']),
                        'closed' => Filters::bool($post['chiuso']),
                        'important' => Filters::bool($post['importante']),
                        'deleted' => $deleted,
                        'padre' => Filters::int($post['id_padre']) === 0,
                        'updated_at_date' => Filters::date($post['modificato_il'], 'd/m/Y'),
                        'updated_at_time' => Filters::date($post['modificato_il'], 'H:i'),
                        'updated_by_id' => Filters::int($post['modificato_da']),
                        'updated_by' => Personaggio::nameFromId(Filters::int($post['modificato_da'])),
                    ];
                }
            }

        }

        return Template::getInstance()->startTemplate()->render(
            'forum/post',
            [
                'posts' => $array,
                'pagination' => $pagination,
                'history_permission' => ForumPermessi::getInstance()->permissionHistory(),
            ]
        );

    }

    /**
     * @fn viewUpdateHistory
     * @note Ritorna la lista delle modifiche del post
     * @param int $post_id
     * @return string
     * @throws Throwable
     */
    public function viewUpdateHistory(int $post_id): string
    {
        return Template::getInstance()->startTemplate()->render(
            'forum/post_history',
            $this->renderUpdateHistory($post_id)
        );
    }

    /**
     * @fn renderUpdateHistory
     * @note Renderizza la lista delle modifiche del post
     * @param int $post_id
     * @return array
     * @throws Throwable
     */
    public function renderUpdateHistory(int $post_id): array
    {

        $post = $this->getPost($post_id);
        $post_data = [
            'id' => Filters::int($post['id']),
            'author_id' => Filters::int($post['autore']),
            'author_name' => Personaggio::nameFromId(Filters::int($post['autore'])),
            'title' => Filters::out($post['titolo']),
            'text' => Filters::out($post['testo']),
            'date' => Filters::date($post['data'], 'd/m/Y H:i:s'),
            'closed' => Filters::bool($post['chiuso']),
            'important' => Filters::bool($post['importante']),
            'deleted' => Filters::bool($post['eliminato']),
        ];

        $post_history = $this->getPostHistory($post_id);
        $post_history_data = [];

        foreach ( $post_history as $history ) {
            $post_history_data[] = [
                'id' => Filters::int($history['id']),
                'title' => Filters::out($history['titolo']),
                'text' => Filters::out($history['testo']),
                'date' => Filters::date($history['modificato_il'], 'd/m/Y H:i:s'),
                'author_id' => Filters::int($history['modificato_da']),
                'author_name' => Personaggio::nameFromId(Filters::int($history['modificato_da'])),
            ];
        }

        return [
            'post' => $post_data,
            'history' => $post_history_data,
        ];
    }

    /*** FUNCTIONS ***/

    /**
     * @fn readPost
     * @note Legge il post
     * @param int $post_id
     * @return void
     * @throws Throwable
     */
    public function readPost(int $post_id): void
    {

        if ( !$this->existRead($post_id, $this->me_id) ) {
            DB::queryStmt("INSERT INTO forum_posts_letti (post, personaggio) VALUES (:post, :pg)", [
                'post' => $post_id,
                'pg' => $this->me_id,
            ]);
        }
    }

    /**
     * @fn sharePostFromForum
     * @note Condivide un post del forum in una conversazione
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function sharePost(array $post): array
    {

        $post_id = Filters::int($post['post_id']);

        if ( ForumPermessi::getInstance()->permissionPost($post_id) ) {

            $conv_id = Filters::int($post['conversation']);
            $text = Filters::in($post['testo']);

            Conversazioni::getInstance()->sendMessage([
                'id' => $conv_id,
                'testo' => $text,
                'allegati' => [
                    [
                        'tipo' => 'forum',
                        'allegato' => $post_id,
                    ],
                ]
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post condiviso correttamente.',
                'swal_type' => 'success',
            ];

        } else {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Non hai i permessi per condividere questo post.',
                'swal_type' => 'error',
            ];
        }

    }
}