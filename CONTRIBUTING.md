# Linee guida per contribuire alla release di GDRCD 5.7.0

Questo documento serve a chiarire il flusso di lavoro per chiunque voglia contribuire allo sviluppo della nuova versione di GDRCD, in particolare per la milestone 5.7.0.

L'obiettivo è mantenere un processo ordinato, trasparente e soprattutto coordinato, così da evitare lavoro duplicato o interventi non allineati con la roadmap del progetto.

---

## 1. Dove iniziare

Il punto di partenza è **sempre** GitHub.

Bisogna:

- [consultare le issue](https://github.com/GDRCD/GDRCD/issues?q=is%3Aissue%20state%3Aopen%20milestone%3A5.7.0%20type%3AFeature%2CBug%20no%3Aassignee) etichettate come `Feature` o `Bug` associate alla **milestone 5.7.0**
- assicurarsi che non abbiano già un assegnatario

> [!IMPORTANT]
> E' possibile prendere in carico solo le issue "libere".

---

## 2. Candidatura su una issue

Una volta individuata una issue su cui si vuole lavorare:

- intervenire direttamente nella discussione della issue su GitHub
- dichiarare chiaramente la propria disponibilità a occuparsene
- descrivere brevemente l'approccio che si intende seguire per la risoluzione

> [!NOTE]
> Questo passaggio serve a evitare sovrapposizioni e a valutare la coerenza della proposta con l'evoluzione del progetto.

---

## 3. Validazione da parte del team

Dopo la candidatura:

- un membro del team risponderà nella issue
- verrà confermato se l'attività può essere presa in carico oppure no

> [!IMPORTANT]
> Alcune issue potrebbero essere già incluse in lavorazioni più ampie (refactor o revisioni strutturali), quindi non sempre è possibile procedere in autonomia anche se l'idea è valida.

---

## 4. Sviluppo su fork

Una volta ottenuto il via libera:

- si può procedere creando un fork del repository
- il branch base su cui lavorare **deve essere obbligatoriamente [`dev57`](https://github.com/GDRCD/GDRCD/tree/dev57)**

> [!IMPORTANT]
> Vale la regola per cui bisogna operare le modifiche minime necessarie per lo sviluppo della feature, o la realizzazione del fix, per cui viene creata la pull request. Modificare del codice che non ha a che vedere col task è assolutamente da evitare e potrebbe esservi chiesto di annullare tali modifiche.

---

## 5. Comunicazione e supporto

Per qualsiasi chiarimento:

- il canale principale resta sempre la issue su GitHub
- eventuali discussioni più rapide possono avvenire anche su [Discord](https://discord.gg/zh69CDUf3V)

> [!IMPORTANT]
> Ogni decisione presa fuori da GitHub deve essere riportata e sintetizzata nella relativa issue, la tracciabilità delle decisioni è obbligatoria.

---

## 6. Invio della Pull Request

Completata l'implementazione:

- si apre una Pull Request verso il repository principale
- la descrizione della PR deve descrivere sommariamente cosa è stato fatto
- nella descrizione va linkata la issue di riferimento

> [!IMPORTANT]
> Una volta inviata la Pull Request il vostro operato verrà sottoposto a revisione. Se tutto in regola le modifiche vengono validate e aggiunte alla release, ma potrebbero essere richiesti dei cambiamenti se ci sono problemi.

---

## 7. Modifiche strutturali importanti e linee guida generali

Molti cambiamenti chiave sono già presenti nella versione in sviluppo, pertanto sarebbe buona norma non dare per scontato che le cose funzionino esattamente come prima.

In particolare, ecco alcune linee guida su questioni mandatorie.

### Il riferimento primario dei personaggi non è più `nome`

La codebase ha subito una modifica rilevante: **non viene più utilizzato il nome del personaggio come identificativo primario**.

Da ora in avanti l'identificativo corretto è `id_personaggio`.

Questo valore è presente:
- in `$_SESSION`
- in tutte le tabelle del database

In alcuni casi, per evitare conflitti di naming, il campo può avere nomi espliciti, ad esempio:

- `id_personaggio_mittente`
- `id_personaggio_destinatario`

È fondamentale rispettare questa convenzione per evitare bug o incoerenze nei dati.

### Nuove funzioni per creare query al database sicure

Le query database devono essere effettuate tramite i nuovi helper:

- `gdrcd_stmt`
- `gdrcd_stmt_one`
- `gdrcd_stmt_all`

Questi metodi sono già parametrizzati, quindi non è più necessario utilizzare `gdrcd_filter`, in quanto sono sicure by design contro input malevolo.

Esempi pratici:
```php
gdrcd_stmt(
  'INSERT INTO personaggio (nome, password) VALUES (?, ?)',
  ['SuperTest', gdrcd_encript('password123')]
);
```
```php
gdrcd_stmt(
  'UPDATE personaggio SET nome = ? WHERE id_personaggio = ?',
  ['SuperTest', 1]
);
```
```php
gdrcd_stmt(
  'DELETE FROM personaggio WHERE id_personaggio = ?',
  [1]
);
```
```php
$personaggio = gdrcd_stmt_one(
  'SELECT nome FROM personaggio WHERE id_personaggio = ?',
  [ $_GET['id'] ]
);

echo $personaggio['nome']; // Super
```

```php
$oggetti = gdrcd_stmt_all(
  'SELECT nome FROM oggetto WHERE tipo = ? AND ubicabile = ?',
  [1, 2]
);

foreach ($oggetti as $oggetto) {
  echo $oggetto['nome']; // Ascia
}
```

### Le modifiche al database vanno sviluppate come migrazioni

GDRCD usa un sistema di migrazioni che permettono di versionare le modifiche operate sul database all'interno di files php. Pertanto, l'aggiunta di nuove tabelle, la modifica/rimozione di colonne esistenti e qualunque altro tipo di operazione che coinvolge la struttura del database deve fare uso di questo sistema.

Per maggiori dettagli, rimandiamo alla [pagina dedicata nella wiki](https://github.com/GDRCD/GDRCD/wiki/Database:-Migrazioni).

---

## 8. Uso dell'IA

Non ci sono limiti nell'uso di agenti IA, vale la semplice regola che si è responsabili di ciò che si è prodotto: se una feature non viene sviluppata correttamente vi verrà richiesto di sistemarne i problemi fino a soddisfare gli standard indicati in questo documento.

---

## 9. Principio fondamentale del processo

- non si devono risolvere issue in autonomia senza confronto
- ogni intervento deve essere prima discusso e validato
- l'obiettivo è evitare lavoro duplicato o potenzialmente inutile

> [!IMPORTANT]
> Questo flusso è necessario coordinare il lavoro e valorizzare i contributi di tutti. Apprezziamo ogni aiuto esterno e vogliamo evitare in tutti i modi di far fare lavoro che risulti poi inutilizzabile all'atto pratico.

---

## 10. Continuità del contributo e gestione inattività

La presa in carico di una issue implica un impegno di continuità nella comunicazione e nello sviluppo.

In caso di assenza di progressi o comunicazioni per 7 giorni consecutivi, ci riserviamo la possibilità di riassegnare internamente la issue, così da non bloccare l'avanzamento del progetto.

Questo non significa che una issue debba necessariamente essere completata entro 7 giorni.

Significa invece che è richiesta costanza nel mantenere attivo il lavoro e il confronto, in modo da portare a compimento ciò per cui ci si è offerti.

L'obiettivo è quello di evitare che una lavorazione resti ferma indefinitamente e possa quindi rallentare il lavoro complessivo di tutti.
