# AlmaKick

AlmaKick è una piattaforma web moderna creata per semplificare l'organizzazione e la ricerca di partite di calcetto amatoriali. Consente di creare partite, gestire le iscrizioni dei giocatori (inclusa la lista d'attesa/panchina), generare le squadre, compilare i tabellini post-partita e votare l'MVP.

---

## 🚀 Come Iniziare

Segui questi passaggi per scaricare, configurare ed avviare il progetto in locale.

### 1. Clonare il Repository

Scarica il progetto sul tuo computer clonando la repository da GitHub:

```bash
git clone https://github.com/LucaPaso04/AlmaKick.git
cd AlmaKick
```

### 2. Configurare il Database (phpMyAdmin)

Il progetto necessita di un database MySQL chiamato `almakick`. Puoi importarlo facilmente tramite **phpMyAdmin**:

1. Avvia il server MySQL (tramite XAMPP, WampServer, MAMP, o installazione locale).
2. Apri **phpMyAdmin** nel tuo browser (solitamente all'indirizzo `http://localhost/phpmyadmin`).
3. Crea un nuovo database:
   - Clicca su **Nuovo** nel menu a sinistra.
   - Inserisci `almakick` come nome del database.
   - Seleziona `utf8mb4_unicode_ci` (o lascia il default) e clicca su **Crea**.
4. Importa lo schema ed i dati iniziali:
   - Clicca sul database `almakick` appena creato.
   - Vai alla scheda **Importa** nel menu in alto.
   - Clicca su **Scegli file** e seleziona il file `AlmaKick_DB.sql` presente nella cartella principale del progetto.
   - Scorri in basso e clicca su **Importa** (o **Esegui**).

> [!NOTE]
> Le credenziali di connessione predefinite del database in [config.php](file:///e:/Programmi/ProgettiGithub/AlmaKick/config.php) sono impostate per l'utente `root` senza password. Se utilizzi credenziali personalizzate, modificale nella sezione `Database settings` del file `config.php`.

### 3. Avviare il Progetto

Puoi avviare l'applicazione web in uno dei seguenti modi:

#### Opzione A: PHP Built-in Server (Consigliato per test rapidi)

Avvia il server di sviluppo integrato di PHP posizionandoti nella cartella principale del progetto ed eseguendo da terminale:

```bash
php -S localhost:8000 -t public
```

Apri quindi il browser all'indirizzo [http://localhost:8000](http://localhost:8000).

#### Opzione B: Apache (XAMPP / WampServer)

Sposta o copia la cartella del progetto all'interno di `htdocs` (per XAMPP) o `www` (per Wamp). Assicurati che Apache sia avviato e naviga all'indirizzo:
`http://localhost/AlmaKick/public/`

---

## 👥 Credenziali di Test per l'Accesso

Puoi accedere all'applicazione utilizzando uno dei seguenti utenti già presenti nel database di prova:

- **Account Amministratore (Host)**:
  - **Email**: `admin@email.it`
  - **Password**: `password`
