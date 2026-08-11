# Pipeline CI/CD Altervista Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Attivare il deploy automatico verso il sito di test Altervista agganciandolo a un branch `dev` reale, e aggiungere un secondo deploy automatico verso un sito di produzione Altervista agganciato a `master`.

**Architecture:** Due workflow GitHub Actions indipendenti, ciascuno con un solo job che fa checkout del repo e pubblica via FTP (`SamKirkland/FTP-Deploy-Action@4.1.0`) verso una destinazione diversa. `dev-deploy.yml` (esistente) viene corretto per triggerare su `dev` invece del branch inesistente `dev6`. `prod-deploy.yml` (nuovo) è la sua controparte per `master`, con secrets e directory di destinazione diversi.

**Tech Stack:** GitHub Actions (YAML), git.

## Global Constraints

- Nessuna modifica al comportamento applicativo: solo infrastruttura CI/CD.
- `dev-deploy.yml` deve continuare a usare i secrets esistenti `ftp_server`, `ftp_user`, `ftp_password` e la destinazione esistente `/gdrcd-deploy/GDRCD-stack-dev/service/` — invariati.
- `prod-deploy.yml` deve usare i secrets `ftp_server_prod`, `ftp_user_prod`, `ftp_password_prod` — questo piano li referenzia soltanto; la creazione dei secrets con i valori reali su GitHub (Settings → Secrets and variables → Actions) resta responsabilità dell'utente e non fa parte di nessun task qui.
- Destinazione di `prod-deploy.yml`: root del sito (`/`).
- Entrambi i workflow usano la stessa action e versione: `SamKirkland/FTP-Deploy-Action@4.1.0`.

---

### Task 1: Creare il branch `dev` da `master`

**Files:**
- Nessun file modificato — solo operazione git

**Interfaces:**
- Consumes: nessuna
- Produces: branch `dev` su `origin`, punto di partenza per i Task 2-3 (che vivono sul branch `feature/cicd-altervista`, non su `dev` — `dev` è solo il branch di destinazione del trigger CI, creato qui perché il Task 2 lo referenzia)

- [ ] **Step 1: Verificare lo stato del repo prima di creare il branch**

Run: `git status --short && git fetch origin && git branch -a`
Expected: working tree pulito; tra i branch remoti compare `origin/master` ma non `origin/dev`.

- [ ] **Step 2: Creare il branch `dev` da `master` e pusharlo**

```bash
git branch dev master
git push -u origin dev
```

- [ ] **Step 3: Verificare che il branch esista su origin**

Run: `git ls-remote --heads origin`
Expected: l'output include sia `refs/heads/master` che `refs/heads/dev`, entrambi allo stesso commit (quello di `master` al momento della creazione).

Nessun commit da fare in questo task (non si modifica alcun file tracciato sul branch di lavoro `feature/cicd-altervista`).

---

### Task 2: Correggere il trigger di `dev-deploy.yml`

**Files:**
- Modify: `.github/workflows/dev-deploy.yml`

**Interfaces:**
- Consumes: branch `dev` creato nel Task 1
- Produces: workflow di deploy test funzionante, che i futuri push su `dev` attiveranno

- [ ] **Step 1: Applicare la modifica al trigger**

In `.github/workflows/dev-deploy.yml`:

old_string:
```yaml
on:
  # Triggers the workflow on push or pull request events but only for the dev branch
  push:
    branches: [ dev6 ]
```

new_string:
```yaml
on:
  # Triggers the workflow on push or pull request events but only for the dev branch
  push:
    branches: [ dev ]
```

- [ ] **Step 2: Verificare che sia l'unica differenza rispetto alla versione precedente**

Run: `git diff .github/workflows/dev-deploy.yml`
Expected: una sola riga modificata (`branches: [ dev6 ]` → `branches: [ dev ]`), nessun'altra differenza — stessi secrets, stessa server-dir, stessa action.

- [ ] **Step 3: Validare la sintassi YAML**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/workflows/dev-deploy.yml'))" && echo "YAML valido"`
Expected: `YAML valido`, nessun errore di parsing.

- [ ] **Step 4: Commit**

```bash
git add .github/workflows/dev-deploy.yml
git commit -m "Corregge il trigger di dev-deploy.yml: dev6 (branch inesistente) -> dev"
```

---

### Task 3: Creare `prod-deploy.yml`

**Files:**
- Create: `.github/workflows/prod-deploy.yml`

**Interfaces:**
- Consumes: nessuna (workflow indipendente da `dev-deploy.yml`)
- Produces: workflow di deploy produzione, attivato dai futuri push su `master`

- [ ] **Step 1: Creare il file**

Crea `.github/workflows/prod-deploy.yml` con questo contenuto esatto:

```yaml
# Workflow di deploy verso il sito di produzione (Altervista)

name: prod-CD

# Controls when the workflow will run
on:
  # Triggers the workflow on push events but only for the master branch
  push:
    branches: [ master ]

  # Allows you to run this workflow manually from the Actions tab
  workflow_dispatch:

# A workflow run is made up of one or more jobs that can run sequentially or in parallel
jobs:
  # This workflow contains a single job called "deploy"
  deploy:
    # The type of runner that the job will run on
    runs-on: ubuntu-latest

    # Steps represent a sequence of tasks that will be executed as part of the job
    steps:
      # Checks-out so the job can access code
      - uses: actions/checkout@v2

      # Runs a single command using the runners shell
      - name: Upload
        uses: SamKirkland/FTP-Deploy-Action@4.1.0
        with:
          server: ${{ secrets.ftp_server_prod }}
          username: ${{ secrets.ftp_user_prod }}
          password: ${{ secrets.ftp_password_prod }}
          server-dir: /
```

- [ ] **Step 2: Validare la sintassi YAML**

Run: `python3 -c "import yaml; yaml.safe_load(open('.github/workflows/prod-deploy.yml'))" && echo "YAML valido"`
Expected: `YAML valido`, nessun errore di parsing.

- [ ] **Step 3: Verificare che i nomi dei secrets siano quelli concordati e diversi da quelli di dev-deploy.yml**

Run: `grep -n "secrets\." .github/workflows/prod-deploy.yml .github/workflows/dev-deploy.yml`
Expected: `prod-deploy.yml` referenzia `secrets.ftp_server_prod`, `secrets.ftp_user_prod`, `secrets.ftp_password_prod`; `dev-deploy.yml` referenzia `secrets.ftp_server`, `secrets.ftp_user`, `secrets.ftp_password` — nessuna sovrapposizione di nomi tra i due file.

- [ ] **Step 4: Commit**

```bash
git add .github/workflows/prod-deploy.yml
git commit -m "Aggiunge il workflow di deploy verso il sito di produzione Altervista"
```

---

### Task 4: Verifica finale

**Files:**
- Nessuna modifica — solo verifica

**Interfaces:**
- Consumes: il lavoro dei Task 1-3
- Produces: conferma che i due workflow sono corretti e indipendenti, e che il branch `dev` esiste

- [ ] **Step 1: Confermare l'esistenza del branch `dev`**

Run: `git branch -a`
Expected: compare sia `dev` (locale, se ancora presente nel checkout) sia `remotes/origin/dev`.

- [ ] **Step 2: Validare entrambi i file YAML**

Run: `for f in .github/workflows/dev-deploy.yml .github/workflows/prod-deploy.yml; do python3 -c "import yaml,sys; yaml.safe_load(open(sys.argv[1])); print(sys.argv[1], 'OK')" "$f"; done`
Expected: entrambe le righe terminano con `OK`.

- [ ] **Step 3: Confrontare i due workflow per assicurarsi che restino indipendenti**

Run: `diff <(grep -v "^\s*#" .github/workflows/dev-deploy.yml) <(grep -v "^\s*#" .github/workflows/prod-deploy.yml)`
Expected: differenze solo su `name:`, `branches:`, e i tre valori `secrets.*`/`server-dir` — nessun'altra riga strutturale diversa (stessa action, stessa versione, stesso runner, stesso trigger `workflow_dispatch`).

- [ ] **Step 4: Riepilogo per l'utente (nessun comando — nota per il report finale)**

Segnalare esplicitamente nel report che restano da fare, a cura dell'utente, fuori dal perimetro di questo piano:
- creare su GitHub (Settings → Secrets and variables → Actions, sul repo `egodi99/GDRCD`) i tre secrets `ftp_server_prod`, `ftp_user_prod`, `ftp_password_prod` con i valori reali del sito Altervista di produzione;
- verificare con un push reale su `dev` e uno su `master` che i due deploy vadano effettivamente a buon fine (un agente non può osservare l'esito di un deploy FTP verso un hosting esterno).
