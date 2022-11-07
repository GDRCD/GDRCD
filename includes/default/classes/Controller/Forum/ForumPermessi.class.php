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
            $this->getForumPermissionForPg($forum_id, $this->me_id)->getNumRows() > 0
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

        return $this->permissionForum($post_data['id_forum']) &&$this->permissionForumEdit($post_id);
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
}