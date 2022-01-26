<?php

class ChatAbilita extends Chat {

    private $pg_class;

    public function __construct()
    {
        parent::__construct();

        $this->pg_class = PersonaggioAbilita::getInstance();
    }

    /*** FUNCTIONS ***/

    /**
     * @fn allAbility
     * @note Estrae id e nome delle abilita' generali che non dipendono dalla razza
     * @return array
     */
    private function getGenericAbility(): array
    {
        $ids = [];

        # Estraggo le abilita secondo i parametri
        $abilita = $this->pg_class->getPgGenericAbility($this->me_id,'abilita.id,abilita.nome,personaggio_abilita.grado');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = Filters::int($abi['id']);
            $nome_abilita = Filters::out($abi['nome']);
            $grado = Filters::int($abi['grado']);

            if ($grado > 0 || !$this->chat_skill_buyed) {
                $ids[$id_abilita] = $nome_abilita;
            }
        }

        # Ritorno l'insieme di id
        return $ids;
    }

    /**
     * @fn getRaceAbilita
     * @note Aggiunge le abilita' relative alla razza
     * @param array $ids
     * @return array
     */
    private function getRaceAbilita(array $ids): array
    {
        # Estraggo le abilita secondo i parametri
        $abilita = $this->pg_class->getPgRaceAbility($this->me_id,'abilita.id,abilita.nome,personaggio_abilita.grado');

        # Per ogni abilita' aggiungo il suo id all'array globale
        foreach ($abilita as $abi) {
            $id_abilita = Filters::int($abi['id']);
            $nome_abilita = Filters::out($abi['nome']);
            $grado = Filters::int($abi['grado']);

            if ($grado > 0 || !$this->chat_skill_buyed) {
                $ids[$id_abilita] = $nome_abilita;
            }
        }

        # Ritorno l'insieme di id
        return $ids;
    }

    /*** LIST ***/

    /**
     * @fn listChatAbilita
     * @note Trasforma le coppie id/abilita' nelle option necessarie
     * @param array $ids
     * @return string
     */
    private function listChatAbilita(array $ids): string
    {
        # Inizializzo le variabili necessarie
        $html = '';

        # Riordino gli id in base al nome
        asort($ids, SORT_ASC);

        # Per ogni id creo una option per la select
        foreach ($ids as $index => $value) {
            $id = Filters::int($index);
            $nome = Filters::out($value);

            $html .= "<option value='{$id}'>{$nome}</option>";
        }

        # Ritorno le option per la select
        return $html;
    }

    /*** RENDER ***/

    /**
     * @fn renderChatAbilita
     * @note Genera la lista delle abilita'
     * @return string
     */
    public function renderChatAbilita(): string
    {
        # Estraggo le abilita' generiche
        $ids = $this->getGenericAbility();

        # Estraggo le abilita' di razza
        $ids = $this->getRaceAbilita($ids);

        # Metto in ordine la lista per nome e ne creo le option per la select
        $html = $this->listChatAbilita($ids);

        # Ritorno la lista formattata
        return $html;
    }

}