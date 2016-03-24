# Documentazione {#mainpage}



# Indice {#sezione0}
1. [Introduzione](#sezione1)
2. [Struttura e contenuto del pacchetto](#sezione2)
3. [Ultima Versione](#sezione3)
4. [Documentazione](#sezione4)
5. [Installazione](#sezione5)
	1. [Metodo 1](#sezione5-1)
	2. [Metodo 2](#sezione5-2)


# Introduzione {#sezione1}
Questo add-on per il gdrcd 5.X nasce con l'intento di creare un supporto basilare per l'utilizzo dei template all'interno dell'open source.
Questo per diversi motivi tra cui i principali:
* Maggiore leggibilità e ordine del codice;
* Permettere lo sviluppo quasi totalmente indipendente delle due parti.

# Struttura e contenuto del pacchetto {#sezione2}
L'add-on è contenuto in un file zip denominato gdrcd_5x-template_10.zip.
Con la seguente struttura:

	gdrcd_5x-template_10.zip
	 |- _documentation
	 |   |- html
	 |   |   +- src
	 |   |- imgs
	 |   |- latex
	 |   +- txt
	 +- gdrcd
		 |- includes
		 |- pages
		 +- themes
			 +- common
				 |- css
				 |- imgs
				 |- js
				 +- template
			 
Dividendosi in due rami principali
* _documentation, contenente tutta la documentazione del pacchetto
* gdrcd, che riproduce la struttura delle cartelle del  e contenente tutti i file per l'open source

# Ultima Versione {#sezione3}
Versione 1.5 del 15/03/2016

# Documentazione {#sezione4}
La documentazione riguardante questa release è totalmente presente nel pacchetto stesso nella directory /_documentation.
E' presente in vari formati nelle cartelle specifiche.
La documentazione riguardante l'ultima versione del pacchetto può essere trovata all'indirizzo web: 

# Installazione ## {#sezione5}
1. Estrarre tutti i file dell'archivio in una cartella a piacere.
2. Nel caso non si voglia tenere il file di esempio utilizzato per la chat eliminare il file
3. Trasferire contenuto della cartella gdrcd all'interno della cartella principale dove è installato il gdrcd 5.X.
L'installazione non sovrascrive nessun file originario del gdrcd a meno che non si decida di tenere il file utilizzato come esempio.
4. Una volta eseguite le operazioni precedenti l'installazione del gestore di template può avvenire in due modi.
La scelta del metodo dipende dalle preferenze personali e da quale si trova più pratico.

## Metodo 1 {#sezione5-1}
Aprire il file functions.inc.php nella cartella includes e alla fine del file prima del tag di chiusura ?> inserire il seguente codice:
```
	require('template_functions.inc.php');
```
Una volta eseguita questa operazione il gestore di template sarà completamente utilizzabile

## Metodo 2 {#sezione5-2}
Aprire il file template_functions.inc.php nella cartella includes con un editor di testo e copiare la funzione GdrcdLoadTemplate():
Aprire il file functions.inc.php nella cartella includes con un editor di testo e incollare la funzione GdrcdLoadTemplate() alla fine del file prima del tag di chiusura ?>
Una volta eseguita questa operazione il gestore di template sarà completamente utilizzabile

# Utilizzo {#sezione6}
Il gestore di template ha un utilizzo piuttosto semplice che si basa nel salvare in un array multidimensionale e poi caricare tramite la funzione GdrcdLoadTemplate() richiamar eil template passando i dati:
