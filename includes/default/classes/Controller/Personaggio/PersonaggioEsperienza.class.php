<?php

class PersonaggioEsperienza extends Personaggio
{
    private $characterId;

    public function __construct()
    {
        parent::__construct();
    }

    public function setCharacterId(int $id): self
    {
        $this->characterId = $id;
        return $this;
    }

    public function permissionManageExp(): bool
    {
        return Permissions::permission('MANAGE_EXP');
    }

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
    $action = Filters::string($post['azione']);
    $causale = Filters::string($post['causale']);
    $creatorId = Filters::int($post['created_by']);

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
        "UPDATE personaggio SET esperienza = esperienza + :amount WHERE id_personaggio = :id",
        ['amount' => $amount, 'id' => $this->characterId]
    );

    if ($updateSuccess) {
        // Log the assignment in `personaggio_esperienza`
        $logSuccess = DB::queryStmt(
            "INSERT INTO personaggio_esperienza (personaggio, punti, azione, causale, is_manual, creato_il, creato_da) 
            VALUES (:pg, :exp, :action, :causale, :is_manual, NOW(), :created_by)",
            [
                'pg' => $this->characterId,
                'exp' => $amount,
                'action' => $action,
                'causale' => $causale,
                'is_manual' => 1,
                'created_by' => $creatorId
            ]
        );

        if ($logSuccess) {
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



    public function removeExperience(int $amount): bool
    {
        if ($amount <= 0) return false;

        return DB::queryStmt(
            "UPDATE personaggio SET esperienza = GREATEST(0, esperienza - :amount) WHERE id_personaggio = :id",
            ['amount' => $amount, 'id' => $this->characterId]
        );
    }

    public function getCurrentExperience(): int
    {
        $result = DB::queryStmt(
            "SELECT esperienza FROM personaggio WHERE id_personaggio = :id",
            ['id' => $this->characterId]
        );

        return $result ? (int)$result->getData()[0]['esperienza'] : 0;
    }
}
