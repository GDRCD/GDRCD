<?php

class ForumPosts extends Forum
{

    /*** TABLES HELPERS ***/

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
                    LEFT JOIN forum_posts_letti ON forum_posts_letti.post = forum_posts.id AND forum_posts_letti.pg = :me
                    WHERE forum_posts.id_forum = :forum AND forum_posts_letti.id IS NULL AND forum_posts.id_padre = 0  AND eliminato=0",
            ['forum' => $forum_id, 'me' => $this->me_id]
        );
        return $response->getNumRows();
    }

    /**
     * @fn getForumPermissionForPg
     * @note Ottiene i dati del permesso di un forum specifico
     * @param int $forum
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getForumPermissionForPg(int $forum, int $pg, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum_permessi WHERE forum=:forum  AND eliminato=0 AND pg=:pg LIMIT 1",
            ['forum' => $forum, 'pg' => $pg]
        );
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


    /*** AJAX ***/

    /**
     * @fn newPost
     * @note Crea un nuovo post
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newPost(array $post):array{

        $forum_id = Filters::int($post['forum_id']);

        if(ForumPermessi::getInstance()->haveForumPermission($forum_id)){

            DB::queryStmt("INSERT INTO forum_posts(id_forum, id_padre, titolo, testo, autore) VALUES (:forum, :padre, :titolo, :testo, :autore)",
                [
                    'forum' => $forum_id,
                    'padre' => 0,
                    'titolo' => Filters::text($post['titolo']),
                    'testo' => Filters::text($post['testo']),
                    'autore' => $this->me_id
                ]
            );

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Post inserito correttamente.',
                'swal_type' => 'success'
            ];
        } else{
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
     * @fn forumsList
     * @note Ritorna la lista renderizzata dei forum
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
                'date_last' => Filters::date($post['data_ultimo'], 'd/m/Y H:i:s'),
                'closed' => Filters::bool($post['chiuso']),
                'important' => Filters::bool($post['importante']),
            ];

            $row_data[] = $array;
        }

        $cells = [
            'Nome',
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
            'footer_pagination' => $this->renderPagination($forum_id, $pagination)
        ];
    }

    /**
     * @fn renderPagination
     * @note Renderizza la paginazione per la lista posts
     * @param $forum_id
     * @param $pagination
     * @return array
     * @throws Throwable
     */
    public function renderPagination($forum_id, $pagination): array
    {

        $posts_number = $this->getCountPostsByForum($forum_id);
        $posts_number_for_page = Functions::get_constant('FORUM_POSTS_FOR_PAGE');

        $pages = [];

        for($i = 1; $i <= ceil($posts_number / $posts_number_for_page); $i++){
            $pages[] = [
                'url' => "/main.php?page=forum/index&op=posts&forum_id={$forum_id}&pagination={$i}",
                'page' => $i
            ];
        }

        return [
            "current" => $pagination,
            "pages" => $pages
        ];
    }
}