<?php

class ChatAbilita extends Chat
{
    /*** FUNCTIONS ***/

    /**
     * @fn allAbility
     * @note Estrae id e nome delle abilita' generali che non dipendono dalla razza
     * @return array
     * @throws Throwable
     */
    private function getGenericAbility(): array
    {
        $ids = [];

        # Estraggo le abilita secondo i parametri
        $abilita = PersonaggioAbilita::getInstance()->getPgGenericAbility($this->me_id, 'abilita.id,abilita.nome,personaggio_abilita.grado');

        # Per ogni abilita' aggiungo il suo id all'arra y globale
        foreach ( $abilita as $abi ) {
            $id_abilita = Filters::int($abi['id']);
            $nome_abilita = Filters::out($abi['nome']);
            $grado = Filters::int($abi['grado']);

            if ( $grado > 0 || !$this->chat_skill_buyed ) {
                $ids[$id_abilita] = $nome_abilita;
            }
        }

        # Ritorno l'insieme d'id
        return $ids;
    }

    /**
     * @fn getRaceAbility
     * @note Aggiunge le abilita' relative alla razza
     * @param array $ids
     * @return array
     * @throws Throwable
     */
    private function getRaceAbility(array $ids): array
    {
        # Estraggo le abilita secondo i parametri
        $abilita = PersonaggioAbilita::getInstance()->getPgRaceAbility($this->me_id, 'abilita.id,abilita.nome,personaggio_abilita.grado');

        # Per ogni abilita' aggiungo il suo id ad array globale
        foreach ( $abilita as $abi ) {
            $id_abilita = Filters::int($abi['id']);
            $nome_abilita = Filters::out($abi['nome']);
            $grado = Filters::int($abi['grado']);

            if ( $grado > 0 || !$this->chat_skill_buyed ) {
                $ids[$id_abilita] = $nome_abilita;
            }
        }

        # Ritorno l'insieme d'id
        return $ids;
    }

    /*** LIST ***/

    /**
     * @fn listChatAbilita
     * @note Trasforma le coppie id/abilita' nelle option necessarie
     * @param array $ids
     * @param string $label
     * @return string
     * @throws Throwable
     */
    private function listChatAbilita(array $ids, string $label = 'Abilità'): string
    {

        # Riordino gli id in base al nome
        asort($ids, SORT_ASC);

        # Creo le opzioni necessarie
        $options = [];
        foreach ( $ids as $index => $value ) {
            $id = Filters::int($index);
            $nome = Filters::out($value);

            $options [] = [
                "id" => $id,
                "nome" => $nome,
            ];
        }

        # Ritorno le opzioni
        return Template::getInstance()->startTemplate()->renderSelect('id', 'nome', 0, $options, $label);
    }

    /*** RENDER ***/

    /**
     * @fn renderChatAbilita
     * @note Genera la lista delle abilita'
     * @return string
     * @throws Throwable
     */
    public function renderChatAbilita(): string
    {
        # Estraggo le abilita' generiche
        $ids = $this->getGenericAbility();

        # Estraggo le abilita' di razza
        $ids = $this->getRaceAbility($ids);

        # Ritorno la lista formattata
        return $this->listChatAbilita($ids);
    }

}