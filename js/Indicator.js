/**
 * Created by Andrea on 30/04/17.
 */

var modalitaIndicator = {
    dinamica    : 1,
    statica     : 2
};

function Indicator(dim, com, ind, mod) {
    // Setta la modalità come dinamica
    this.modalita = mod;

    // Identificatore univoco per ogni indicatore
    this.dim = dim;
    this.com = com;
    this.ind = ind;
}

Indicator.prototype = {
    constructor: Indicator,

    getPoint: 	function( t, lista ) {
        var indicator = lista[this.dim][this.com]['ind'][this.ind];
        var q0 = parseFloat(indicator['q0']);

        if (this.modalita == modalitaIndicator.statica || t < 0) {
            return q0;
        }

        var q1 = parseFloat(indicator['q1']);
        var qr = parseFloat(indicator['qr']);
        var tr = parseFloat(indicator['tr']);

        if ( t >= tr ) {
            return qr;
        } else {
            return (((qr - q1) / tr) * t) + q1;
        }
    },
    
    getWeight: function (lista) {
        var indicator = lista[this.dim][this.com]['ind'][this.ind];
        if ( parseFloat(indicator['i']) == 0 ) {
            return 1;
        }
        return parseFloat(indicator['i']) / parseFloat(lista[this.dim][this.com]['total_i']);
    }

}
