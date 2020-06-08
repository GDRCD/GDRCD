
# Change Log
Tutti i cambiamenti dalla versione 5.5 verranno inseriti qui.


## [5.5.0] - 2020-06-01
  
Elenco delle modifiche al codice
 
### Aggiunto

- __$PARAMETERS['mode']['check_forum'] = 'OFF';__ serve per abilitare la notifica di nuovi messaggiaraldo (thread) nel box messaggi.inc.php.
- __$PARAMETERS['mode']['check_messages'] = 'ON';__ serve per abilitare la notifica di nuovi messaggi privati nel box messaggi.inc.php.
- __$PARAMETERS['text']['check_forum']['new'] = '(Nuovo)';__ permette di personalizzare il testo da far comparire nel caso ci siano nuovi messaggiaraldo (thread)
- __$PARAMETERS['info']['GDRCD'] = '5.5';__ serve per indicare la versione di GDRCD.
- __$PARAMETERS['mode']['exp_in_private'] == 'ON')__ serve per abilitare l'esperienza nelle chat private.
- __$PARAMETERS['settings']['auto_salary'] = 'OFF'__ serve per abilitare l'accredito automatico dello stipendio al primo login.
- __gdrcd_list('personaggi')__ come datalist per aiutare a trovare il nome dei personaggi durante la creazione di un messaggio.
- __$PARAMETERS['settings']['exp_by_chat']['value'] = '0';__ serve per impostare quanti punti experienza assegnare nelle azioni.
- __$PARAMETERS['mode']['allow_new_chat_audio'] = 'ON';__ serve per abilitare l'avviso sonoro quando ci sono nuovi messaggi in chat.
- __COME-FARE-LA-LAND---LEGGIMI!.txt__ aggiunto in home page al fine di ripristinare il link.
### Modificato
  
-  Impostato come unico sistema di criptaggio password BCRYPT
- Refactoring di tutto il codice 
- __forum.inc.php__ e' stato diviso in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/forum).
- __gestione_manutenzione.inc.php__ e' stato divisa in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/gestione/manutenzione).
- __messages_center.inc.php__ e' stato diviso in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/messages).
- __scheda.inc.php__ e' stato divisa in piu' parti per migliorne la leggibilita'. E' stato creato un controllo delle richieste che verranno inoltrate alla pagina richiesta (/pages/scheda).
- __main.css__ l'altezza di .iframe.iframe-messaggi e' stata portata da 20px a 33px.
- __PasswordHash.php__ aggiornato dalla versione 0.3 alla versione 0.5.
- __< audio >__ aggiornato allo standard HTML5 in scheda.inc.php
- __visit.inc.php__ i thread vengono visualizzati in base all'ultima risposta e non piu' in base alla creazione.
- __jQuery__ aggiornato alla versione 3.5.1.
- __jQueryUI__ aggiornato alla versione 1.12.1.
- __HTML5__ modificato il doctype in accordo con lo standard HTML5.

### Rimosso

- Rimosso il sistema di salvataggio delle password in chiaro.
- Rimosso il sistema di criptaggio password SHA-1.
- Rimosso il sistema di criptaggio password MD5.
 
### Corretto
 
-  [BugFix] conflitti con merge precedenti.
- [BugFix] __$PARAMETERS['settings']['protection_password'] = 'gdrcd';__ non era stato implementato correttamente.
- [BugFix] Elimina messaggi selezionati.