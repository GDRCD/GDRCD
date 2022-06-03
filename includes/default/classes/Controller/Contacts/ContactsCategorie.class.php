<?php

/**
 * @class ContactsCategories
 * @note Classe per la gestione centralizzata delle categorie dei contatti
 * @required PHP 7.1+
 */
class ContactsCategories extends Contacts
{
    protected

        $con_categories,
        $con_categories_public,
        $con_categories_staff;
    public function __construct()
    {
        parent::__construct();
        # attivi/disattivi le categorie contatto - default true
        $this->con_categories = Functions::get_constant('CONTACT_CATEGORIES');

        # Se si, tutti vedono le categorie, altrimenti solo chi ha il permesso VIEW_CONTACTS_CATEGORIES - default true
        $this->con_categories_public = Functions::get_constant('CONTACT_CATEGORIES_PUBLIC');

        # Solo lo staff puo' assegnare le categorie di contatto, true/false - default false
        $this->con_categories_staff = Functions::get_constant('CONTACT_CATEGORIES_STAFF_ONLY');
    }

    public function contatcCategories(): bool
    {
        return $this->con_categories;
    }
    public function contatcCategoriesPublic(): bool
    {
        return $this->con_categories_public;
    }
    public function contatcStaff(): bool
    {
        return $this->con_categories_staff;
    }

    public function checkCategories(): bool
    {
        if(($this->contatcCategoriesPublic())&&(Permissions::permission('VIEW_CONTACTS_CATEGORIES'))){
            return true;
        }else if(($this->contatcStaff()) && (Permissions::permissionInGroups('MASTER')) ){
             return true;
        }else {
            return false;
        }



    }




}