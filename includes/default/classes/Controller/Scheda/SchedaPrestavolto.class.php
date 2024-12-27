<?php

class SchedaPrestavolto extends Scheda
{

    private bool $pv_active;
    private bool $pv_shareable;
    private bool $pv_have_image;
    private bool $pv_insert_edit;
    private int $pv_insert_day_edit;
    private bool $pv_auto_remove;
    /**
     * @fn __construct
     * @note Class constructor
     * @throws Throwable
     */
    protected function __construct()
    {
        parent::__construct();

        $this->pv_active= Functions::get_constant('PRESTAVOLTO_ENABLED');
        $this->pv_shareable= Functions::get_constant('PRESTAVOLTO_SHAREABLE');
        $this->pv_have_image= Functions::get_constant('PRESTAVOLTO_HAVE_IMAGE');
        $this->pv_insert_edit= Functions::get_constant('PRESTAVOLTO_INSERT_EDIT');
        $this->pv_auto_remove= Functions::get_constant('PRESTAVOLTO_AUTO_REMOVE');
        $this->pv_insert_day_edit= Functions::get_constant('PRESTAVOLTO_INSERT_EDIT_TIMER');
        
    }

    /*** TABLE HELPERS ***/
 

    /***** CONFIG ****/

    /**
     * @fn pvActive
     * @note Restituisce se il prestavolto è attivo
     * @return bool
     */
    public function PvActive(): bool
    {
        return $this->pv_active;
    }
    /**
     * @fn pvShareable
     * @note Restituisce se il prestavolto è condiviso
     * @return bool
     */
    public function PvShareable(): bool
    {
        return $this->pv_shareable;
    }
    /**
     * @fn pvHaveImage
     * @note Restituisce se si permette l'inserimento dell'immagine del prestavolto - solo se è condiviso
     * @return bool
     */
    public function PvHaveImage(): bool
    {
        return $this->pv_have_image;    
    }
    /**
     * @fn pvInsertEdit
     * @note Restituisce se è possibile cambiare il pv dopo l'inserimento
     * @return bool
     */
    public function PvInsertEdit(): bool
    {
        return $this->pv_insert_edit;    
    }
     /**
     * @fn PvInsertDayEdit
     * @note Restituisce i giorni entro cui è possibile cambiare il prestavolto
     * @return bool
     */
    public function PvInsertDayEdit(): int
    {
        return $this->pv_insert_day_edit;    
    }
/**
     * @fn pvAutoRemove
     * @note Restituisce se viene rimosso il pv per inattività
     * @return bool
     */
    public function PvAutoRemove(): bool
    {
        return $this->pv_auto_remove;    
    }

    /***** PERMESSI ***/

    /**
     * @fn permissionUpdatePrestavolto
     * @note Controlla se il prestavolto puo' essere modificato.
     * @param int $id_pg
     * @return bool
     * @throws Throwable
     */
    public function permissionUpdatePrestavolto(int $id_pg): bool
    {
        return Personaggio::isMyPg($id_pg) || Permissions::permission('PRESTAVOLTO_EDIT');
    }

    public function updateCharacterPV(array $post): array{
        $id_pg = Filters::int($post['pg']);

        if ( $this->permissionUpdatePrestavolto($id_pg) ) {
            $nome = Filters::in($post['pv_nome']);
            $cognome = Filters::in($post['pv_cognome']);
            $fonte = Filters::in($post['fonte']);
            $condivisibile = (Filters::checkbox($post['pv_condivisibile']))? Filters::checkbox($post['pv_condivisibile']):0;
            $condivisibile_img = (Filters::in($post['pv_condivisibile_immagine']))?Filters::in($post['pv_condivisibile_immagine']):"";
            $condivisibile_descr = (Filters::in($post['pv_condivisibile_descrizione']))?Filters::in($post['pv_condivisibile_descrizione']):"";
            
            $check_pv= Prestavolto::getPvData($id_pg)->getNumRows();
            if(!$check_pv){
                //nuovo
                DB::queryStmt(
                    "INSERT INTO prestavolto (`personaggio`,`nome`, `cognome`, `fonte`, `condivisibile`, `condivisibile_descrizione`, `condivisibile_immagine`, `creato_il`)  
                            VALUES (:personaggio, :nome, :cognome, :fonte, :condivisibile, :condivisibile_descr, :condivisibile_img, NOW())",
                    [
                        'personaggio' => $id_pg,
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'fonte' => $fonte,
                        'condivisibile' => $condivisibile,
                        'condivisibile_descr' => $condivisibile_descr,
                        'condivisibile_img' => $condivisibile_img
                    ]
                );
            }else{
                //update
                DB::queryStmt(
                    "UPDATE prestavolto 
                          SET `nome` = :nome, `cognome` = :cognome, `fonte` = :fonte,`condivisibile_descrizione` = :condivisibile_descr, `condivisibile_immagine` = :condivisibile_img, `condivisibile` = :condivisibile, `modificato_il` = NOW()
                          WHERE personaggio = :personaggio",
                    [
                        'personaggio' => $id_pg,
                        'nome' => $nome,
                        'cognome' => $cognome,
                        'fonte' => $fonte,
                        'condivisibile' => $condivisibile,
                        'condivisibile_descr' => $condivisibile_descr,
                        'condivisibile_img' => $condivisibile_img
                    ]
                );

            }
            return [
                'response' => true,
                'swal_title' => 'Operazione riuscita!',
                'swal_message' => 'Nota aggiunta correttamente.',
                'swal_type' => 'success',
            ];

            
        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Permesso negato.',
            'swal_type' => 'error',
        ];


    }


}