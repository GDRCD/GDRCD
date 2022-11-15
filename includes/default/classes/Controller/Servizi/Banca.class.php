<?php

class Banca extends BaseClass
{

    /**** PERMISSIONS ****/

    /**
     * @fn permissionManageBank
     * @note Controlla se l'utente ha i permessi per gestire la banca
     * @param int $pg
     * @return bool
     * @throws Throwable
     */
    public function permissionManageBank(int $pg): bool
    {
        return Personaggio::isMyPg($pg) || Permissions::permission('MANAGE_BANK');
    }

    /**** FUNCTIONS ****/

    /**
     * @fn extractBankData
     * @note Estrae i dati della banca
     * @param int $pg
     * @return DBQueryInterface
     * @throws Throwable
     */
    public function extractBankData(int $pg): DBQueryInterface
    {
        return Personaggio::getPgData($pg, 'banca,soldi');
    }

    /**
     * @fn deposit
     * @note Deposita una somma di denaro nella banca
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function deposit(array $post): array
    {
        $money = Filters::int($post['money']);

        $pg_data = $this->extractBankData($this->me_id);

        if ( $money <= Filters::int($pg_data['soldi']) ) {
            $new_money = Filters::int($pg_data['soldi']) - $money;
            $new_bank = Filters::int($pg_data['banca']) + $money;

            try {
                DB::queryStmt("UPDATE personaggio SET soldi = :soldi, banca = :bank WHERE id = :id", [
                    'soldi' => $new_money,
                    'bank' => $new_bank,
                    'id' => $this->me_id,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Depositato!',
                    'swal_message' => 'Denaro depositato con successo!',
                    'swal_type' => 'success',
                    'new_money' => $new_money,
                    'new_bank' => $new_bank
                ];
            } catch (Throwable ) {
                return [
                    'response' => false,
                    'swal_title' => 'Errore!',
                    'swal_message' => 'Errore durante il deposito!',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Deposito negato!',
                'swal_message' => 'Non hai abbastanza denaro!',
                'swal_type' => 'info',
            ];
        }
    }

    /**
     * @fn withdraw
     * @note Preleva una somma di denaro dalla banca
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function withdraw(array $post):array
    {
        $money = Filters::int($post['money']);

        $pg_data = $this->extractBankData($this->me_id);

        if ( $money <= Filters::int($pg_data['banca']) ) {
            $new_money = Filters::int($pg_data['soldi']) + $money;
            $new_bank = Filters::int($pg_data['banca']) - $money;

            try {
                DB::queryStmt("UPDATE personaggio SET soldi = :soldi, banca = :bank WHERE id = :id", [
                    'soldi' => $new_money,
                    'bank' => $new_bank,
                    'id' => $this->me_id,
                ]);

                return [
                    'response' => true,
                    'swal_title' => 'Prelevato!',
                    'swal_message' => 'Denaro prelevato con successo!',
                    'swal_type' => 'success',
                    'new_money' => $new_money,
                    'new_bank' => $new_bank
                ];
            } catch (Throwable ) {
                return [
                    'response' => false,
                    'swal_title' => 'Errore!',
                    'swal_message' => 'Errore durante il prelievo!',
                    'swal_type' => 'error',
                ];
            }
        } else {
            return [
                'response' => false,
                'swal_title' => 'Prelievo negato!',
                'swal_message' => 'Non hai abbastanza denaro!',
                'swal_type' => 'info',
            ];
        }
    }

    /**
     * @fn transfer
     * @note Trasferisce una somma di denaro da un pg all'altro
     * @param array $post
     * @return array
     * @throws Throwable
     */
    public function transfer(array $post):array{
        $money = Filters::int($post['money']);
        $pg = Filters::int($post['pg']);
        $causal = Filters::text($post['causal']);

        $pg_data = $this->extractBankData($this->me_id);

        if(Personaggio::pgExist($pg)) {
            if ( $money <= Filters::int($pg_data['banca']) ) {
                $new_bank = Filters::int($pg_data['banca']) - $money;

                $pg_name = Personaggio::nameFromId($pg);
                $pg_data = $this->extractBankData($pg);
                $pg_new_bank = Filters::int($pg_data['banca']) + $money;

                try {
                    DB::queryStmt("UPDATE personaggio SET banca = :bank WHERE id = :id", [
                        'bank' => $new_bank,
                        'id' => $this->me_id,
                    ]);

                    DB::queryStmt("UPDATE personaggio SET banca = :bank WHERE id = :id", [
                        'bank' => $pg_new_bank,
                        'id' => $pg,
                    ]);

                    Log::newLog([
                        "autore" => $this->me_id,
                        "destinatario" => $pg,
                        "tipo" => BONIFICO,
                        "testo" => "Inviati {$money} a {$pg_name} per '{$causal}'",
                    ]);

                    #TODO Aggiunta messaggio destinatario

                    return [
                        'response' => true,
                        'swal_title' => 'Inviato!',
                        'swal_message' => 'Bonifico inviato con successo!',
                        'swal_type' => 'success',
                        'new_bank' => $new_bank
                    ];
                } catch ( Throwable ) {
                    return [
                        'response' => false,
                        'swal_title' => 'Errore!',
                        'swal_message' => 'Errore durante invio del bonifico!',
                        'swal_type' => 'error',
                    ];
                }
            } else {
                return [
                    'response' => false,
                    'swal_title' => 'Bonifico negato!',
                    'swal_message' => 'Non hai abbastanza denaro in banca!',
                    'swal_type' => 'info',
                ];
            }
        } else{
            return [
                'response' => false,
                'swal_title' => 'Bonifico negato!',
                'swal_message' => 'Destinatario inesistente!',
                'swal_type' => 'error',
            ];
        }
    }

}