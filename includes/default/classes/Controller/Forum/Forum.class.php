<?php

# TODO Logica segnalazioni post via messaggio - Attesa sezione messaggi

class Forum extends BaseClass
{

    protected bool
        $forum_active;

    /**
     * @fn __construct
     * @note Costruttore della classe
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();
        $this->forum_active = Functions::get_constant('FORUM_ACTIVE');
    }


    /*** GETTER ***/

    /**
     * @fn isActive
     * @note Ritorna true se il forum è attivo, false altrimenti
     * @return bool|string
     */
    public function isActive(): bool|string
    {
        return $this->forum_active;
    }


    /*** TABLES HELPER ***/

    /**
     * @fn getForum
     * @note Ottiene i dati del forum
     * @param int $id
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getForum(int $id, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum WHERE id=:id LIMIT 1", ['id' => $id]);
    }

    /**
     * @fn getAllForums
     * @note Ottiene tutti i forum
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllForums(string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum WHERE 1");
    }

    /**
     * @fn getAllForumsByType
     * @note Ottieni tutti i forum di un tipo
     * @param int $type
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getAllForumsByType(int $type, string $val = '*'): DBQueryInterface
    {
        return DB::queryStmt("SELECT {$val} FROM forum WHERE tipo = :tipo  ORDER BY tipo DESC", ["tipo" => $type]);
    }


    /*** ROUTING ****/

    /**
     * @fn loadPage
     * @note Routing delle pagine del forum
     * @param string $op
     * @return string
     */
    public function loadPage(string $op): string
    {
        $op = Filters::out($op);

        switch ( $op ) {
            default:
                $page = 'forums/index.php';
                break;

            // Lista Posts
            case 'posts':
                $page = 'posts/index.php';
                break;

            // Singolo Post
            case 'post':
                $page = 'post/index.php';
                break;

            // Singolo Post Nuovo
            case 'post_new':
                $page = 'post/new.php';
                break;

            // Singolo Post Modifica
            case 'post_edit':
                $page = 'post/edit.php';
                break;

            // Singolo Post Commenta
            case 'post_comment':
                $page = 'post/comment.php';
                break;
        }

        return $page;
    }


    /*** AJAX ***/

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

    /**
     * @fn get_forum_data
     * @note Ritorna il testo del frame laterale
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function ajaxForumData(array $post): array
    {
        return $this->getForum($post['id'])->getData()[0];
    }


    /*** LISTS ***/

    /**
     * @fn listForums
     * @note Ritorna la lista dei forum
     * @param int $selected
     * @return string
     * @throws Throwable
     */
    public function listForums(int $selected = 0): string
    {
        $forums = $this->getAllForums();
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', $selected, $forums);
    }


    /*** RENDER ***/

    /**
     * @fn renderForumName
     * @note Renderizza il nome del forum
     * @param int $forum_id
     * @return string
     * @throws Throwable
     */
    public function renderForumName(int $forum_id): string
    {
        return $this->getForum($forum_id, 'nome')['nome'];
    }

    /**
     * @fn forumsList
     * @note Ritorna la lista renderizzata dei forum
     * @return string
     * @throws Throwable
     */
    public function forumsList(): string
    {
        return Template::getInstance()->startTemplate()->render(
            'forum/forums_list',
            $this->renderForumsList()
        );
    }

    /**
     * @fn renderForumsList
     * @note Renderizza la lista dei forum
     * @return array
     * @throws Throwable
     */
    public function renderForumsList(): array
    {
        $forums = [];
        $forum_types = ForumTipo::getInstance()->getAllForumTypes();

        foreach ( $forum_types as $forum_type ) {
            $type_id = Filters::int($forum_type['id']);

            if ( ForumPermessi::getInstance()->permissionForumType($type_id) ) {

                $forums[$type_id] = [
                    'type_data' => $forum_type,
                    'forums' => [],
                ];

                $forums_list = $this->getAllForumsByType($type_id);

                foreach ( $forums_list as $forum ) {
                    $forum_id = Filters::int($forum['id']);

                    if ( ForumPermessi::getInstance()->permissionForum($forum_id) ) {
                        $forums[$type_id]['forums'][] = [
                            'id' => $forum_id,
                            'tipo' => Filters::out($forum['tipo']),
                            'nome' => Filters::out($forum['nome']),
                            'descrizione' => Filters::out($forum['descrizione']),
                            'proprietario' => Filters::out($forum['proprietario']),
                            'nuovi' => ForumPosts::getInstance()->getPostsToReadByForum($forum_id),
                        ];
                    }
                }
            }
        }

        return ["forums" => $forums];
    }

    /**
     * @fn renderFrameText
     * @note Renderizza il testo del frame laterale
     * @return string
     * @throws Throwable
     */
    public function renderFrameText(): string
    {
        $new_post = ForumPosts::getInstance()->getCountPostsAllForum();

        return Template::getInstance()->startTemplate()->render(
            'forum/forums_frame',
            [
                'new_post' => $new_post,
            ]
        );
    }


    /**** GESTIONE ****/

    /**
     * @fn newForum
     * @note Inserisce un nuovo forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newForum(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $type = Filters::in($post['tipo']);

            DB::queryStmt("INSERT INTO forum (nome, descrizione,tipo) VALUES (:nome, :descrizione,:tipo)", [
                'nome' => $name,
                'descrizione' => $description,
                'tipo' => $type,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Forum creato correttamente.',
                'swal_type' => 'success',
                'forums_list' => $this->listForums(),
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
     * @fn modForum
     * @note Modifica un forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function modForum(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $id = Filters::int($post['id']);
            $name = Filters::in($post['nome']);
            $description = Filters::in($post['descrizione']);
            $type = Filters::in($post['tipo']);

            DB::queryStmt("UPDATE forum SET nome=:nome, descrizione=:description, tipo=:tipo WHERE id=:id", [
                'id' => $id,
                'nome' => $name,
                'description' => $description,
                'tipo' => $type,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Forum modificato correttamente.',
                'swal_type' => 'success',
                'forums_list' => $this->listForums(),
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
     * @fn delForum
     * @note Elimina un forum
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delForum(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $id = Filters::int($post['id']);

            DB::queryStmt("DELETE FROM forum WHERE id=:id", [
                'id' => $id,
            ]);

            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Forum eliminato correttamente.',
                'swal_type' => 'success',
                'forums_list' => $this->listForums(),
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