/**
 * Created by Andrea on 30/04/17.
 */

var modalitaAPIIndicators = {
    nessuna				: -1,
    caricaLista			: 1,
    salvaLista	        : 2
};

function Indicators() {
    // Setta la modalità a normale
    this.modalita = modalitaAPIIndicators.nessuna;

    // Callback chimato non appena si avvia un'azione
    this.callbackInizio = null;

    // Callback in caso di azione conclusa senza problemi
    this.callbackSuccesso = null;

    // Callback in caso di errori
    this.callbackErrore = null;
}

Indicators.prototype = {
    constructor: Indicators,

    caricaLista: 	function() {
        var that = this;
        this.modalita = modalitaAPIIndicators.caricaLista;
        that.lanciaCallbackInizio();

        $.ajax({
            type: "GET",
            url: "./ajax/indicators.php",
            cache: false,
            success: function(html) {
                var json = false;
                try {
                    json = $.parseJSON(html);
                }
                catch(err) {
                    that.lanciaCallbackErrore("Il formato dei dati restituito dall'elaborazione sembra essere non valido. <br/>" + err.message);
                    return;
                }

                if ( json.status !== 0 ) {
                    that.lanciaCallbackErrore(json.desc);
                    return;
                }

                // Nel caso in cui non ci fosse alcun dato presente.
                /*if (json.result.length == 0) {
                 that.lanciaCallbackSuccesso(json.desc);
                 return;
                 }*/
                that.lanciaCallbackSuccesso(json.result);
            } ,
            error: function(html) {
                that.lanciaCallbackErrore("Impossibile raggiungere la pagina di gestione degli indicatori.");
            }
        });
    },

    salvaLista: function( list ) {
        var that = this;
        this.modalita = modalitaAPIIndicators.salvaLista;
        that.lanciaCallbackInizio();

        $.ajax({
            type: "POST",
            url: "./ajax/indicators.php",
            data: { lista : JSON.stringify(list)},
            cache: false,
            success: function(html) {
                console.log(html);
                var json = false;
                try {
                    json = $.parseJSON(html);
                }
                catch(err) {
                    that.lanciaCallbackErrore("Il formato dei dati restituito dall'elaborazione sembra essere non valido. <br/>" + err.message + "<br />" + html);
                    return;
                }

                if ( json.status !== 0 ) {
                    that.lanciaCallbackErrore(json.desc);
                    return;
                }

                that.lanciaCallbackSuccesso(json.result);
            } ,
            error: function(html) {
                that.lanciaCallbackErrore("Impossibile raggiungere la pagina di gestione degli indicatori.");
            }
        });
    },

    eliminaLista: function( id ) {
        var that = this;
        that.lanciaCallbackInizio();

        $.ajax({
            type: "DELETE",
            url: "./ajax/indicators.php",
            data: id,
            cache: false,
            success: function(html) {
                var json = false;
                try {
                    json = $.parseJSON(html);
                }
                catch(err) {
                    that.lanciaCallbackErrore("Il formato dei dati restituito dall'elaborazione sembra essere non valido. <br/>" + err.message + "<br />" + html);
                    return;
                }

                if ( json.status !== 0 ) {
                    that.lanciaCallbackErrore(json.desc);
                    return;
                }

                that.lanciaCallbackSuccesso(json.result);
            } ,
            error: function(html) {
                that.lanciaCallbackErrore("Impossibile raggiungere la pagina di gestione degli indicatori.");
            }
        });
    },

    lanciaCallbackInizio: function() {
        if ( this.callbackInizio != null ) {
            window[this.callbackInizio](this.modalita);
        }
    },

    lanciaCallbackSuccesso: function(risultato) {
        if ( this.callbackSuccesso != null ) {
            window[this.callbackSuccesso](this.modalita, risultato);
        }
    },

    lanciaCallbackErrore: function(errore) {
        if ( this.callbackErrore != null ) {
            window[this.callbackErrore](this.modalita, errore);
        }
    }

}