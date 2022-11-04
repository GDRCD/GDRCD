<?php

class ForumPermessi extends Forum
{
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
        if ( $this->permissionForumAdmin() || Permissions::permission('FORUM_VIEW_ALL') || ForumTipo::getInstance()->isTypePublic($type) ) {
            return true;
        } else {
            $permissions = ForumPermessi::getInstance()->getForumsPermissionsByType($type);
            return ($permissions->getNumRows() > 0);
        }
    }

    /**
     * @fn permissionForum
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $forum_id
     * @param int $type_id
     * @return bool
     * @throws Throwable
     */
    public function permissionForum(int $forum_id, int $type_id): bool
    {

        if ( $this->permissionForumAdmin() || Permissions::permission('FORUM_VIEW_ALL') || ForumTipo::getInstance()->isTypePublic($type_id) ) {
            return true;
        } else {
            return ForumPermessi::getInstance()->haveForumPermission($forum_id);
        }
    }

    /**
     * @fn haveForumPermission
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $forum_id
     * @param int|bool $pg_id
     * @return bool
     * @throws Throwable
     */
    public function haveForumPermission(int $forum_id, int|bool $pg_id = false): bool
    {
        if ( !$pg_id ) {
            $pg_id = $this->me_id;
        }

        return ($this->permissionForumAdmin() || Permissions::permission('FORUM_VIEW_ALL') || $this->getForumPermissionForPg($forum_id, $pg_id)->getNumRows() > 0);
    }

    /**
     * @fn haveForumPermissionByPostId
     * @note Controlla se l'utente ha i permessi per accedere al forum
     * @param int $post_id
     * @param int|bool $pg_id
     * @return bool
     * @throws Throwable
     */
    public function haveForumPermissionByPostId(int $post_id, int|bool $pg_id = false): bool
    {
        if ( !$pg_id ) {
            $pg_id = $this->me_id;
        }

        $post_data = ForumPosts::getInstance()->getPost($post_id, 'id_forum,eliminato');

        return ($this->permissionForumAdmin() || Permissions::permission('FORUM_VIEW_ALL') || (!Filters::bool($post_data['eliminato']) && ($this->getForumPermissionForPg(Filters::int($post_data['id_forum']), $pg_id)->getNumRows() > 0)));
    }

    /**
     * @fn permissionPostEdit
     * @note Controlla se l'utente ha i permessi per modificare il post
     * @param int $post_id
     * @return bool
     * @throws Throwable
     */
    public function permissionPostView(int $post_id): bool
    {
        $post_data = ForumPosts::getInstance()->getPost($post_id, 'eliminato');
        return ($this->permissionForumAdmin() || Permissions::permission('FORUM_VIEW_ALL') || !Filters::bool($post_data['eliminato']));
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
        $post_data = ForumPosts::getInstance()->getPost($post_id, 'chiuso,eliminato');
        return ($this->permissionForumAdmin() || (!Filters::bool($post_data['eliminato']) && !Filters::bool($post_data['chiuso'])));
    }

    /**
     * @fn permissionPostEdit
     * @note Controlla se l'utente ha i permessi per modificare il post
     * @param int $post_id
     * @param bool|int $pg_id
     * @return bool
     * @throws Throwable
     */
    public function permissionPostEdit(int $post_id, bool|int $pg_id = false): bool
    {
        if ( !$pg_id ) {
            $pg_id = $this->me_id;
        }

        $post_data = ForumPosts::getInstance()->getPost($post_id, 'autore,eliminato');

        return ($this->permissionForumAdmin() || Permissions::permission('FORUM_EDIT_ALL') || (!Filters::bool($post_data['eliminato']) && (Filters::int($post_data['autore']) == $pg_id)));
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
}