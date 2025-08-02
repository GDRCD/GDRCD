<?php

class PersonaggioEsperienza extends Personaggio
{
    private $characterId;

    public function __construct()
    {
        parent::__construct();
    }

    /**** SETTERS ***/

    /**
     * @fn setCharacterId
     * @note Imposta l'ID del personaggio per le operazioni
     * @param int $id
     * @return self
     */
    public function setCharacterId(int $id): self
    {
        $this->characterId = $id;
        return $this;
    }

    /**** PERMISSIONS ***/

    /**
     * @fn permissionManageExp
     * @note Controlla che si abbiano i permessi per gestire l'esperienza
     * @return bool
     * @throws Throwable
     */
    public function permissionManageExp(): bool
    {
        return Permissions::permission('MANAGE_EXP');
    }

    /**** OPERATIONS ***/

    /**
     * @fn addExperience
     * @note Assegna esperienza ad un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function addExperience(array $post): array
    {
        // Check for permissions
        if (!$this->permissionManageExp()) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permessi insufficienti.',
                'swal_type' => 'error'
            ];
        }

        // Extract required fields
        $amount = Filters::int($post['exp']);
        $causale = Filters::string($post['causale']);
        $creatorId = Functions::getInstance()->getMyId();

        if ($amount <= 0 || !$this->characterId) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Dati non validi.',
                'swal_type' => 'error'
            ];
        }

        // Update the experience points in the `personaggio` table
        $updateSuccess = DB::queryStmt(
            "UPDATE personaggio SET esperienza = esperienza + :amount WHERE id = :id",
            ['amount' => $amount, 'id' => $this->characterId]
        );

        if ($updateSuccess) {
            // Log the assignment in `personaggio_esperienza`
            $logSuccess = DB::queryStmt(
                "INSERT INTO personaggio_esperienza (personaggio, punti, causale, is_manual, creato_il, creato_da) 
                VALUES (:pg, :exp, :causale, :is_manual, NOW(), :created_by)",
                [
                    'pg' => $this->characterId,
                    'exp' => $amount,
                    'causale' => $causale,
                    'is_manual' => 1,
                    'created_by' => $creatorId
                ]
            );

            if ($logSuccess) {
                // Add entry to the main log system for user profile
                Log::newLog([
                    'autore' => $creatorId,
                    'destinatario' => $this->characterId,
                    'tipo' => PX,
                    'testo' => "Assegnati {$amount}EXP. Causale: {$causale}"
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Esperienza assegnata correttamente.',
                    'swal_type' => 'success'
                ];
            }
        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Errore durante l\'aggiornamento.',
            'swal_type' => 'error'
        ];
    }



    /**
     * @fn removeExperience
     * @note Rimuove esperienza da un personaggio
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function removeExperience(array $post): array
    {
        // Check for permissions
        if (!$this->permissionManageExp()) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Permessi insufficienti.',
                'swal_type' => 'error'
            ];
        }

        // Extract required fields
        $amount = Filters::int($post['exp']);
        $causale = Filters::string($post['causale'] ?? 'Rimozione esperienza');
        $creatorId = Functions::getInstance()->getMyId();

        if ($amount <= 0 || !$this->characterId) {
            return [
                'response' => false,
                'swal_title' => 'Operazione fallita!',
                'swal_message' => 'Dati non validi.',
                'swal_type' => 'error'
            ];
        }

        // Update the experience points in the `personaggio` table (prevent negative values)
        $updateSuccess = DB::queryStmt(
            "UPDATE personaggio SET esperienza = GREATEST(0, esperienza - :amount) WHERE id = :id",
            ['amount' => $amount, 'id' => $this->characterId]
        );

        if ($updateSuccess) {
            // Log the removal in `personaggio_esperienza` (negative value to track removal)
            $logSuccess = DB::queryStmt(
                "INSERT INTO personaggio_esperienza (personaggio, punti, causale, is_manual, creato_il, creato_da) 
                VALUES (:pg, :exp, :causale, :is_manual, NOW(), :created_by)",
                [
                    'pg' => $this->characterId,
                    'exp' => -$amount, // Negative value to indicate removal
                    'causale' => $causale,
                    'is_manual' => 1,
                    'created_by' => $creatorId
                ]
            );

            if ($logSuccess) {
                // Add entry to the main log system for user profile
                Log::newLog([
                    'autore' => $creatorId,
                    'destinatario' => $this->characterId,
                    'tipo' => PX,
                    'testo' => "Rimossi {$amount}EXP. Causale: {$causale}"
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Operazione riuscita!',
                    'swal_message' => 'Esperienza rimossa correttamente.',
                    'swal_type' => 'success'
                ];
            }
        }

        return [
            'response' => false,
            'swal_title' => 'Operazione fallita!',
            'swal_message' => 'Errore durante l\'aggiornamento.',
            'swal_type' => 'error'
        ];
    }

    /**** TABLE HELPERS ***/

    /**
     * @fn getCurrentExperience
     * @note Ottiene l'esperienza attuale del personaggio
     * @return int
     * @throws Throwable
     */
    public function getCurrentExperience(): int
    {
        $result = DB::queryStmt(
            "SELECT esperienza FROM personaggio WHERE id = :id",
            ['id' => $this->characterId]
        );

        return $result ? (int)$result->getData()[0]['esperienza'] : 0;
    }
}
