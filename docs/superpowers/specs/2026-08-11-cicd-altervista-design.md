# Design: pipeline CI/CD di deploy verso Altervista (test + produzione)

- **Data**: 2026-08-11
- **Repo**: fork `egodi99/GDRCD` (origin). Branch di lavoro: `feature/cicd-altervista`, creato da `master` (unico branch remoto esistente prima di questo lavoro)
- **Stato**: approvato, pronto per il piano di implementazione

## Contesto

Il repo ha già un workflow GitHub Actions, `.github/workflows/dev-deploy.yml`, che pubblica via FTP (`SamKirkland/FTP-Deploy-Action@4.1.0`) su un sito di test hostato su Altervista, usando i secrets `ftp_server`, `ftp_user`, `ftp_password` e la directory di destinazione `/gdrcd-deploy/GDRCD-stack-dev/service/`. Il trigger attuale è `push` sul branch `dev6`, ma quel branch non esiste (né localmente né su `origin`, dove l'unico branch remoto è `master`) — il workflow è di fatto inattivo.

Il progetto non ha altri meccanismi di CI (nessuna test suite automatizzata, nessun lint in pipeline): l'unico workflow esistente è questo deploy FTP.

## Obiettivo

- Rendere funzionante il deploy verso il sito di test, agganciandolo a un branch `dev` reale (da creare, oggi inesistente).
- Aggiungere un secondo deploy, verso un sito di produzione diverso (sempre su Altervista), agganciato al branch `master`.
- Nessuna modifica al comportamento applicativo: è puro lavoro di infrastruttura CI/CD.

## Design

### 1. Creazione del branch `dev`

Creato da `master` (commit `87e0a6d`, HEAD di `origin/master` al momento di questo lavoro) e pushato su `origin`. Nessun contenuto diverso da `master` al momento della creazione.

### 2. Due workflow separati, uno per ambiente

Si preferisce un file per ambiente di destinazione (invece di un unico workflow con job condizionali sul branch): ciascun file ha una responsabilità sola, resta leggibile, e replica lo stile già in uso nel repo.

**`.github/workflows/dev-deploy.yml`** (esistente, modificato):
- Trigger: `push` su `dev` (sostituisce `dev6`) + `workflow_dispatch` (invariato).
- Secrets: `ftp_server`, `ftp_user`, `ftp_password` (esistenti, invariati).
- Destinazione: `/gdrcd-deploy/GDRCD-stack-dev/service/` (invariata).
- Nessun'altra modifica: stessa action, stessa versione (`@4.1.0`).

**`.github/workflows/prod-deploy.yml`** (nuovo):
- Trigger: `push` su `master` + `workflow_dispatch`.
- Secrets: `ftp_server_prod`, `ftp_user_prod`, `ftp_password_prod` — nomi nuovi, da creare manualmente su GitHub (Settings → Secrets and variables → Actions) con i valori reali del sito Altervista di produzione. Questo lavoro non può crearli: un secret è per definizione un valore che non deve transitare per la history di git né per una sessione di agente.
- Destinazione: root del sito (`/`).
- Stessa action (`SamKirkland/FTP-Deploy-Action@4.1.0`), stessa struttura del workflow esistente.

### Diagramma del flusso

```
push su dev     → dev-deploy.yml  → FTP (ftp_server/user/password)       → sito test Altervista
push su master  → prod-deploy.yml → FTP (ftp_server_prod/user_prod/...)  → sito produzione Altervista (root)
```

## Fuori scope

- Rendere generico/parametrico il workflow (es. un singolo file riusabile con matrix/environment) — non richiesto, YAGNI per due soli ambienti.
- Aggiungere step di test/lint alla pipeline — il progetto non ha una test suite; non introdotta qui.
- Creare o valorizzare i secrets di produzione — responsabilità dell'utente, fuori dal perimetro di un agente.
- Proteggere i branch `dev`/`master` (branch protection rules, required reviews) — non richiesto, valutabile in futuro.

## Verifica

- `git branch -a` mostra `dev` creato da `master`, pushato su `origin`.
- `.github/workflows/dev-deploy.yml`: trigger `dev` invece di `dev6`, resto invariato — diff minimo.
- `.github/workflows/prod-deploy.yml`: nuovo file, sintatticamente valido YAML, stessa struttura del file esistente con secrets/destinazione diversi.
- Verifica end-to-end (un push reale su `dev` e uno su `master` che attivano i deploy) è responsabilità dell'utente dopo aver creato i secrets di produzione: un agente non può osservare l'esito di un deploy FTP verso un hosting esterno.
