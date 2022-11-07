<?php

class ForumPermessi extends Forum
{
    /*** CONTROLS ***/

    /**
     * @fn existPermission
     * @note Controlla se esiste un permesso per un personaggio su un forum specifico
     * @param int $forum
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public function existPermission(int $forum, int $pg): bool
    {
        return $this->getForumPermissionForPg($forum, $pg)->getNumRows() > 0;
    }

    /*** PERMISSIONS ***/

    /**
     * @fn permissionForumAdmin
     * @note Controllo permessi per l'amministrazione del forum
     * @return bool
     * @throws Throwable
     */
    public function permissionForumAdmin(): bool
    {
        return Permissions::permission('FORUM_ADMIN');
    }

    /**
     * @fn permissionForumType
     * @note Controlla se l'utente ha i permessi per accedere al tipo di forum
     * @param int $type
     * @return bool|DBQueryInterface
     * @throws Throwable
     */
    public function permissionForumType(int $type): bool|DBQueryInterface
    {
        return (
            $this->permissionForumAdmin() ||
            Permissions::permission('FORUM_VIEW_ALL') ||
            ForumTipo::getInstance()->isTypePublic($type) ||
            $this->getForumsPermissionsByType($type)->getNumRows() > 0
        );
    }

    /**
     * @fn permissionForum
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $forum_id
     * @return bool
     * @throws Throwable
     */
    public function permissionForum(int $forum_id): bool
    {
        return (
            $this->permissionForumAdmin() ||
            Permissions::permission('FORUM_VIEW_ALL') ||
            ForumTipo::getInstance()->isForumPublic($forum_id) ||
            $this->existPermission($forum_id, $this->me_id)
        );
    }

    /**
     * @fn permissionEditForum
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionForumEdit(int $post_id): bool
    {

        $post_data = ForumPosts::getInstance()->getPost($post_id, 'autore,eliminato');

        return (
            $this->permissionForumAdmin() ||
            Permissions::permission('FORUM_EDIT_ALL') ||
            (!Filters::bool($post_data['eliminato']) && (Filters::int($post_data['autore']) == $this->me_id))
        );
    }

    /**
     * @fn haveForumPermissionByPostId
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionForumByPostId(int $post_id): bool
    {
        $post_data = ForumPosts::getInstance()->getPost($post_id, 'id_forum,eliminato');
        return ($this->permissionForum($post_data['id_forum']) && $this->permissionPost($post_id));
    }

    /**
     * @fn permissionPost
     * @note Controlla se l'utente ha i permessi per accedere al post
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionPost(int $post_id): bool
    {
        $post_data = ForumPosts::getInstance()->getPost($post_id, 'autore,eliminato');

        return (
            $this->permissionForumAdmin() ||
            Permissions::permission('FORUM_VIEW_ALL') ||
            !Filters::bool($post_data['eliminato'])
        );
    }

    /**
     * @fn permissionPostEdit
     * @note Controlla se l'utente ha i permessi per modificare il post
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionPostEdit(int $post_id): bool
    {

        $original_id = ForumPosts::getInstance()->getOriginalPostId($post_id);
        $post_data = ForumPosts::getInstance()->getPost($original_id, 'id_forum,autore,eliminato');

        return $this->permissionForum($post_data['id_forum']) && $this->permissionForumEdit($post_id);
    }

    /**
     * @fn permissionPostComment
     * @note Controlla se l'utente ha i permessi per commentare il post
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionPostComment(int $post_id): bool
    {
        $post_data = ForumPosts::getInstance()->getPost($post_id, 'id_forum,chiuso,eliminato');
        return ($this->permissionForum($post_data['id_forum']) && !Filters::bool($post_data['eliminato']) && !Filters::bool($post_data['chiuso']));
    }


    /*** TABLES HELPERS ***/

    /**
     * @fn getForumsPermissionsByType
     * @note Ottieni i permessi dei forum di un tipo
     * @param int $type_id
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getForumsPermissionsByType(int $type_id): DBQueryInterface
    {
        return DB::queryStmt(
            "SELECT forum_permessi.id FROM forum LEFT JOIN forum_permessi ON forum_permessi.forum = forum.id    
                  WHERE forum.tipo = :tipo AND forum_permessi.pg = :me",
            ['tipo' => $type_id, 'me' => $this->me_id]
        );
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
        return DB::queryStmt("SELECT {$val} FROM forum_permessi WHERE forum=:forum AND pg=:pg LIMIT 1",
            ['forum' => $forum, 'pg' => $pg]
        );
    }

    /**
     * @fn getForumPermissionFiltered
     * @note Ottiene i dati del permesso di un forum specifico o di un personaggio specifico, o entrambi
     * @param int $forum
     * @param int $pg
     * @param string $val
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function getForumPermissionFiltered(int $forum = 0, int $pg = 0, string $val = '*'): DBQueryInterface
    {

        $extra_query = '';

        if ( $pg > 0 ) {
            $extra_query .= " AND pg = {$pg}";
        }

        if ( $forum > 0 ) {
            $extra_query .= " AND forum = {$forum}";
        }

        return DB::queryStmt("SELECT {$val} FROM forum_permessi WHERE 1 {$extra_query}", []);
    }

    /*** AJAX ***/

    /**
     * @fn ajaxForumPermissions
     * @note Ottiene i permessi di un forum o di un personaggio, o entrambi
     * @param $post
     * @return array
     * @throws Throwable
     */
    public function ajaxForumPermissions($post): array
    {
        $pg = Filters::int($post['personaggio']);
        $forum = Filters::int($post['forum']);
        return ['list' => $this->permissionList($forum, $pg)];
    }

    /**** RENDER ****/

    /**
     * @fn permissionList
     * @note Ottiene la lista dei permessi di un forum o di un personaggio, o entrambi
     * @param int $forum
     * @param int $pg
     * @return string
     * @throws Throwable
     */
    public function permissionList(int $forum, int $pg): string
    {
        return Template::getInstance()->startTemplate()->renderTable(
            'gestione/forum/forum_permission',
            $this->renderPermissionList($forum, $pg)
        );
    }

    /**
     * @fn renderPermissionList
     * @note Ottiene la lista dei permessi di un forum o di un personaggio, o entrambi
     * @param int $forum
     * @param int $pg
     * @return array
     * @throws Throwable
     */
    public function renderPermissionList(int $forum, int $pg): array
    {
        $list = $this->getForumPermissionFiltered($forum, $pg);
        $row_data = [];

        foreach ( $list as $row ) {

            $forum_data = Forum::getInstance()->getForum($row['forum']);

            $row_data[] = [
                'user_id' => Filters::int($row['pg']),
                'user_name' => Personaggio::nameFromId($row['pg']),
                'forum_id' => Filters::int($row['forum']),
                'forum_name' => Filters::out($forum_data['nome']),
            ];
        }

        $cells = [
            'Personaggio',
            'Nome',
            'Controlli'
        ];
        $links = [];

        return [
            'body_rows' => $row_data,
            'cells' => $cells,
            'links' => $links,
        ];
    }

    /**** GESTIONE ****/

    /**
     * @fn newPermission
     * @note Inserisce un nuovo permesso
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function newPermission(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $pg = Filters::in($post['personaggio']);
            $forum = Filters::in($post['forum']);

            if ( !$this->existPermission($forum, $pg) ) {
                DB::queryStmt("INSERT INTO forum_permessi (pg, forum,assegnato_da) VALUES (:pg, :forum,:assigned_by)", [
                    'pg' => $pg,
                    'forum' => $forum,
                    'assigned_by' => $this->me_id,
                ]);
                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Permesso forum assegnato correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => true,
                    'swal_title' => 'Operazione negata!',
                    'swal_message' => 'Permesso gia presente per questa coppia forum-personaggio.',
                    'swal_type' => 'info',
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
     * @fn delPermission
     * @note Elimina un permesso
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function delPermission(array $post): array
    {

        if ( ForumPermessi::getInstance()->permissionForumAdmin() ) {

            $pg = Filters::in($post['personaggio']);
            $forum = Filters::in($post['forum']);

            if ( $this->existPermission($forum, $pg) ) {
                DB::queryStmt("DELETE FROM forum_permessi WHERE pg=:pg AND forum=:forum", [
                    'pg' => $pg,
                    'forum' => $forum,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Permesso forum rimosso correttamente.',
                    'swal_type' => 'success',
                ];
            } else {
                return [
                    'response' => true,
                    'swal_title' => 'Operazione negata!',
                    'swal_message' => 'Permesso non presente per questa coppia forum-personaggio.',
                    'swal_type' => 'info',
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