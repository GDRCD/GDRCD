function modalWindow(name, title, url, width, height) {
    // per width ed height imposto dei valori di default così non occorre specificarli in ogni occasione
    width = typeof width === 'undefined'? 800 : width;
    height = typeof height === 'undefined'? 600 : height;

    // verifichiamo se nel body non esiste il sorgente per la dialog
    if (top.$('#dialog-'+name).length === 0) {
        // in questo caso lo creiamo:
        top.$('body').append('<div id="dialog-'+name+'" title="'+title+'" style="padding:0;"><iframe src="'+ url +'" frameborder="no" style="position:absolute;width:100%;height:100%;" scrolling="yes"></div>');
    } else {
        // se il sorgente invece esiste già assegnamo la nuova url all´iframe:
        top.$('#dialog-'+name).attr('title', title);
        top.$('#dialog-'+name+' iframe').attr('src', url);
    }

    // Ok, adesso siamo pronti per lanciare la modale!
    top.$('#dialog-'+name).dialog({
        width: width,
        height: height,
        modal: true,
        close: function () {
            // Quando la modale viene chiusa, cancello il precedente contenuto
            top.$('#dialog-'+name+' iframe').attr('src', '');
        }
    });
}