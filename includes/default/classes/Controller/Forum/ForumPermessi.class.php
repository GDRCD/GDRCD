<?php

class ForumPermessi extends Forum
{
    /*** PERMISSIONS ***/

    /**
     * @fn permissionForumType
     * @note Controlla se l'utente ha i permessi per accedere al tipo di forum
     * @param int $type
     * @return bool|DBQueryInterface
     * @throws Throwable
     */
    public function permissionForumType(int $type): bool|DBQueryInterface
    {
        if ( Permissions::permission('FORUM_VIEW_ALL') || ForumTipo::getInstance()->isTypePublic($type) ) {
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

        if ( Permissions::permission('FORUM_VIEW_ALL') || ForumTipo::getInstance()->isTypePublic($type_id) ) {
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

        return (Permissions::permission('FORUM_VIEW_ALL') || $this->getForumPermissionForPg($forum_id, $pg_id)->getNumRows() > 0);
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