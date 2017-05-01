<?php
/**
 * Author: Andrea Semprebon
 * Date: 29/04/17
 */

/**
 * LETTURA FILE
 */

// Leggo il contenuto del file con gli indicatori
$filename = "./indicators/indicators.json";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);
$dimensions = json_decode($contents, true);

//Leggo i template
$filename = "./template/dimension_content.html";
$handle = fopen($filename, "r");
$dimension_content = fread($handle, filesize($filename));
fclose($handle);

$filename = "./template/component_content.html";
$handle = fopen($filename, "r");
$component_content = fread($handle, filesize($filename));
fclose($handle);

$filename = "./template/dyn_indicator_content.html";
$handle = fopen($filename, "r");
$dyn_indicator_content = fread($handle, filesize($filename));
fclose($handle);

$filename = "./template/static_indicator_content.html";
$handle = fopen($filename, "r");
$static_indicator_content = fread($handle, filesize($filename));
fclose($handle);

/**
 * In questa varibale è salvato tutto l'HTML del sistema,
 * prima che venga stampato a scherm
 */
$html = "";

/**
 * Contiene un array associativo che verrà caricato in javascript
 * per gestire i vari indicatori
 */
$js_indicators_array = "{ ";

$color_array = array(   "red"       => "#D95C5C",
                        "orange"    => "#E07B53",
                        "yellow"    => "#F2C61F",
                        "olive"     => "#B5CC18",
                        "green"     => "#5BBD72",
                        "teal"      => "#00B5AD",
                        "blue"      => "#3B83C0",
                        "purple"    => "#564F8A",
                        "pink"      => "#D9499A");
$color_array_names = array_keys($color_array);

$color_array_size = count($color_array);

foreach ($dimensions as $dim_id => $dim) {

    $dim_html = $dimension_content;

    $js_indicators_array .= $dim_id . ': { ';

    $color_array_index = 0;

    $com_html = '';

    foreach ($dim['components'] as $com_id => $com) {

        $js_indicators_array .= $com_id . ': { ind : { ';

        // Preparo l'ID stile HTML, con - al posto di .
        $com_id_for_html = str_replace(".", "-", $com['id']);

        // Preparo nome e codice dei colori
        $color_name = $color_array_names[ $color_array_index % $color_array_size ];
        $color_code = $color_array[ $color_name ];
        $color_array_index++;

        $com_html .= $component_content;

        $com_replace = array(   '{{__NAME__}}'          => $com['name'],
                                '{{__ID__}}'            => $com['id'],
                                '{{__ID_FOR_HTML__}}'   => $com_id_for_html,
                                '{{__IMPORTANCE__}}'    => $com['importance'],
                                '{{__DIM_ID__}}'        => $dim['id'],
                                '{{__COLOR__}}'         => $color_name,
                                '{{__COLOR_CODE__}}'    => $color_code  );

        $com_html = strtr($com_html, $com_replace);

        $ind_total = '';
        $total_i   = 0;

        foreach ($com['indicators'] as $ind_id => $ind) {
            $ind_id_for_html = str_replace(".", "-", $ind['id']);

            $ind_replace = array(   '{{__NAME__}}'          => $ind['name'],
                                    '{{__MEASURE__}}'       => $ind['measure'],
                                    '{{__ID__}}'            => $ind['id'],
                                    '{{__ID_FOR_HTML__}}'   => $ind_id_for_html,
                                    '{{__IMPORTANCE__}}'    => $ind['importance'],
                                    '{{__NAT__}}'           => $ind['nat'],
                                    '{{__DIM_ID__}}'        => $dim_id,
                                    '{{__COM_ID__}}'        => $com_id,
                                    '{{__IND_ID__}}'        => $ind_id);

            $ind_html = $dyn_indicator_content;

            if ( strtoupper($ind['nat']) == 'S' ) {
                $ind_html = $static_indicator_content;
            }

            $ind_html = strtr($ind_html, $ind_replace);

            $ind_total .= $ind_html;

            $nat     = (strtoupper($ind['nat']) == 'D') ? 'modalitaIndicator.dinamica' : 'modalitaIndicator.statica';
            $def_val = (strtoupper($ind['nat']) == 'D') ? 'null' : '1';

            $js_indicators_array .= $ind_id . ': { q0u: null, sv: null, q0: null, q1: ' . $def_val . ', qr: ' . $def_val . ', 
                                    tr: ' . $def_val . ', i : ' . intval($ind['importance']) . ', nat : ' . $nat . ' },';

            $total_i += intval($ind['importance']);
        }

        $com_html = str_replace("{{__INDICATORS_CONTENT__}}", $ind_total, $com_html);

        // Rimuovo l'ultima virgola
        $js_indicators_array = rtrim($js_indicators_array,", ");
        $js_indicators_array .= '}, i : ' . intval($com['importance']) . ', total_i : ' . $total_i . ', 
                                    name : "' . $com['name'] . '", color : "' . $color_code . '" },';
    }

    // Rimuovo l'ultima virgola
    $js_indicators_array = rtrim($js_indicators_array,", ");

    $dim_replace = array(   '{{__DIM_ID__}}'                => $dim_id,
                            '{{__COMPONENTS_CONTENT__}}'    => $com_html );

    $dim_html = strtr($dim_html, $dim_replace);

    $html .= $dim_html;

    // Chiudo l'array in javascript
    $js_indicators_array .= '},';
}

// Rimuovo l'ultima virgola
$js_indicators_array = rtrim($js_indicators_array,", ");
$js_indicators_array .= '}';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PEOPLES Framework</title>

    <!-- INCLUDO SEMANTIC-UI E JQUERY -->
    <link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="semantic/dist/semantic.min.js"></script>

    <link type="text/css" rel="stylesheet" href="katex/katex.min.css">
    <script type="text/javascript" src="katex/katex.min.js" charset="utf-8"></script>

    <script src="chartjs/dist/Chart.min.js" charset="UTF-8"></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">

    <script type="application/javascript" src="js/Indicators.js" charset="UTF-8"></script>
    <script type="application/javascript" src="js/Indicator.js" charset="UTF-8"></script>

    <script type="application/javascript">
        var lista = <?php echo $js_indicators_array; ?>;
        var ind_chart_list = null;
        var dimensione_selezionata = -1;
        var indicators = new Indicators();
        indicators.callbackSuccesso = 'azioneGestioneIndicatorsTerminataConSuccesso';
        indicators.caricaLista();

        function azioneGestioneIndicatorsTerminataConSuccesso(modalita, result) {
            if ( modalita == modalitaAPIIndicators.caricaLista ) {
                lista = result;
                aggiornaTabellaIndicatori();
            }
        }

        var timerSave = null;
        function avviaTimerSalvataggioEGraphRedraw() {
            // Salva la lista
            clearTimeout(timerSave);
            timerSave = setTimeout(azioniTimerSalvataggioGraphRedraw, 2000);
        }

        function azioniTimerSalvataggioGraphRedraw() {
            salvaListaIndicatori();
            aggiornaGrafici();
        }

        function salvaListaIndicatori() {
            console.log("Salvo");
            indicators.salvaLista( lista );
        }

        function aggiornaGrafici() {
            aggiornaGraficiPerDimensione(dimensione_selezionata);
        }

        function aggiornaTabellaIndicatori() {
            $( ".input.number input" ).each(function() {
                var dim     = $(this).data("dim");
                var com     = $(this).data("com");
                var ind     = $(this).data("ind");
                var name    = $(this).attr("name");

                var value   = lista[dim][com]['ind'][ind][name];


                if ( value !== null) {
                    $(this).val( value );
                } else {
                    $(this).val( "" );
                }
            });

            aggiornaListaGraficiIndicatori();
            aggiornaGrafici();
        }
        
        function aggiornaListaGraficiIndicatori() {
            ind_chart_list = {};
            for (var d = 1; d <= Object.keys(lista).length; d++) {
                ind_chart_list[d] = {};
                ind_chart_list[d]['graph_id'] = '#chart_' + d;
                ind_chart_list[d]['com'] = {};

                for (var c = 1; c <= Object.keys(lista[d]).length; c++) {
                    ind_chart_list[d]['com'][c] = {};
                    ind_chart_list[d]['com'][c]['graph_id'] = '#chart_' + d + '_' + c;
                    ind_chart_list[d]['com'][c]['ind'] = {};

                    for (var i = 1; i <= Object.keys(lista[d][c]['ind']).length; i++) {
                        ind_chart_list[d]['com'][c]['ind'][i] = new Indicator(d, c, i, lista[d][c]['ind'][i]['nat']);
                    }
                }
            }
        }

        function trapz(data, start, end) {
            var a = null;
            var b = null;

            for (var i = 1; i < data.length; i++) {
                if (data[i]['x'] >= start && a === null) {
                    a = i;
                }
                if (data[i]['x'] >= end && b === null) {
                    b = i;
                    break;
                }
            }

            var x0 = data[a-1]['x'];    var y0 = data[a-1]['y'];
            var x1 = data[a]['x'];      var y1 = data[a]['y'];
            var y_s = ((y0 - y1) / (x0 - x1)) * start + ((x0*y1 - y0*x1) / (x0 - x1));

            var x0 = data[b-1]['x'];    var y0 = data[b-1]['y'];
            var x1 = data[b]['x'];      var y1 = data[b]['y'];
            var y_f = ((y0 - y1) / (x0 - x1)) * start + ((x0*y1 - y0*x1) / (x0 - x1));

            var sum = (y_s + data[a]['y']) * (data[a]['x'] - start);

            for (var i = a+1; i < b; i++) {
                sum += ((data[i]['x'] - data[i-1]['x']) * (data[i]['y'] + data[i-1]['y']));
            }
            sum += (y_f + data[b-1]['y']) * (end - data[b-1]['x']);

            return sum / 2;
        }

        function linspance(a, b, n) {
            // Si definisce un massimo numero di punti che è 1000
            var step = (b - a) / n;
            var space = new Array();
            if ( step == 0 || a == b || a > b ) {
                return space
            }

            for (var s = a; s <= b && space.length <= 1000; s+=step) {
                space.push(s);
            }

            return space;
        }

        function aggiornaGraficiPerDimensione(dim) {
            var max_tr = 0;
            var comp_validi = new Array();
            var comp_errore = new Array();

            for (var c = 1; c <= Object.keys(lista[dim]).length; c++) {
                if ( sonoIndicatoriValidiPerComponente(dim, c) ) {
                    for (var i = 1; i <= Object.keys(lista[dim][c]['ind']).length; i++) {
                        if (parseFloat(lista[dim][c]['ind'][i]['tr']) > max_tr) {
                            max_tr = parseFloat(lista[dim][c]['ind'][i]['tr']);
                        }
                    }
                    comp_validi.push(c);
                } else {
                    comp_errore.push(c);
                }

            }

            mostraMessaggiDiErrorePerIlGrafico(dim, comp_errore, comp_validi);

            if (comp_validi.length == 0) {
                $(".dim-" + dim + " .plot .grafico").hide();
                return false;
            }
            $(".dim-" + dim + " .plot .grafico").show();

            var span = Math.floor(max_tr * 0.1);
            /**
             * Il grafico lo faccio iniziare 10% di Tr prima di 0 e prosegue
             * il 10% di Tr oltre Tr.
             */
            var space = linspance(-span, max_tr + span, Math.ceil( (max_tr + 2 * span) / 2) );

            var datasets = new Array();

            for (var c_idx = 0; c_idx < comp_validi.length; c_idx++) {
                var c = comp_validi[c_idx];

                var dataset = {
                    label: lista[dim][c]['name'],
                    id : c,
                    lineTension: 0.1,
                    borderColor: lista[dim][c]['color'],
                    fill: false,
                    pointRadius: 1,
                    pointHitRadius: 1,
                    pointHoverRadius: 1
                };

                var data = new Array();
                for (var t_idx = 0; t_idx < space.length; t_idx++) {
                    var t = space[t_idx];
                    var y_val = 0;
                    for (var i = 1; i <= Object.keys(lista[dim][c]['ind']).length; i++) {
                        y_val = y_val + ind_chart_list[dim]['com'][c]['ind'][i].getPoint(t, lista) * ind_chart_list[dim]['com'][c]['ind'][i].getWeight(lista);
                    }

                    data.push({x: t, y: y_val * 100});
                }

                dataset['data'] = data;
                var integral = 100 - (trapz(data, 0, max_tr)/max_tr);
                console.log(integral);
                mostraValoreIntegrale(dim, max_tr, integral);
                datasets.push(dataset);

            }

            if (datasets.length > 0) {

                /**
                 * Se ho più di un dataset che posso plottare, calcolo anche
                 * la media fra i vari dataset e la plotto
                 */
                if (datasets.length > 1) {
                    var dataset = {
                        label: 'All components',
                        lineTension: 0.5,
                        borderColor: 'black',
                        fill: false,
                        pointRadius: 1,
                        pointHitRadius: 1,
                        pointHoverRadius: 1
                    };


                    var data = new Array();
                    for (var t_idx = 0; t_idx < space.length; t_idx++) {
                        var t = space[t_idx];

                        var y_val = 0;
                        var i_sum = 0;
                        for (var i = 0; i < datasets.length; i++) {
                            var c = parseInt(datasets[i]['id']);
                            y_val = y_val + datasets[i]['data'][t_idx]['y'] * parseInt(lista[dim][c]['i']);
                            i_sum += parseInt(lista[dim][c]['i']);
                        }

                        data.push({x: t, y: y_val / i_sum});
                    }

                    dataset['data'] = data;
                    datasets.push(dataset);
                }


                createChart(ind_chart_list[dim]['graph_id'], datasets, -span, max_tr + span);
            }

        }

        function mostraMessaggiDiErrorePerIlGrafico(dim, comp_errore, comp_validi) {
            var msgs_id_class = ".dim-" + dim + " .plot .messaggi";
            $(msgs_id_class).html("");

            if (comp_errore.length == 0) {
                var good_msg = "<div class='ui green message'>All components are correctly plotted</div>";
                $(msgs_id_class).append(good_msg);
            }

            var err_msg_text = "<div class='ui red message'>{{__TESTO__}}</div>";

            if (comp_validi.length == 0) {
                err_msg_text = "<div class='ui red message full'>{{__TESTO__}}</div>";
            }

            for (var i = 0; i < comp_errore.length; i++) {
                var c = comp_errore[i];

                var name = lista[dim][c]['name'];

                var err_msg = err_msg_text.replace("{{__TESTO__}}", "Some indicators in component " + name + " are not correct. " +
                    name + " was not plotted");
                $(msgs_id_class).append(err_msg);
            }
        }

        function mostraValoreIntegrale(dim, max_tr, ris) {
            var msgs_id_class = ".dim-" + dim + " .plot .messaggi";
            var id = 'katex';

            var int = "<div class='integral''></div>";
            var math_text = "\\int_{0}^{{{__MAX_TR__}}} \\frac{100 - Q(x)}{{{__MAX_TR__}}}dx = {{__RIS__}}\\%";

            math_text = math_text.replace("{{__MAX_TR__}}", max_tr);
            math_text = math_text.replace("{{__MAX_TR__}}", max_tr);
            math_text = math_text.replace("{{__RIS__}}",    ris.toFixed(2));

            console.log(math_text);

            //$(msgs_id_class).append(int);

            $(msgs_id_class).append( "<div class='integral'>" + katex.renderToString(math_text, {displayMode: true}) + "</div>" );
        }

        function calcoliSuIndicatore(dim, com, ind) {
            // Calcolo q0 a partire da SV e qu0
            var sv  = lista[dim][com]['ind'][ind]['sv'];
            var qu0 = lista[dim][com]['ind'][ind]['q0u'];
            var q0  = lista[dim][com]['ind'][ind]['q0'];

            var q0_id = '#' + dim + '' + com + '' + ind + 'q0';

            if ( sv !== null && qu0 !== null ) {
                var new_q0 = Math.min(1, (qu0 / sv)).toFixed(3);

                if ( !isNaN(new_q0) && isFinite(new_q0) ) {

                    $(q0_id).attr('placeholder', '');
                    $(q0_id).val( new_q0 );

                    lista[dim][com]['ind'][ind]['q0'] = new_q0;

                } else {
                    $(q0_id).attr('placeholder', 'q0');
                    $(q0_id).val("");
                    lista[dim][com]['ind'][ind]['q0'] = null;
                }

            } else {
                $(q0_id).attr('placeholder', 'q0');
                $(q0_id).val("");
                lista[dim][com]['ind'][ind]['q0'] = null;
            }

        }
        
        function sonoIndicatoriValidiPerComponente(dim, com) {
            for (var i = 1; i <= Object.keys(lista[dim][com]['ind']).length; i++) {
                if (lista[dim][com]['ind'][i]['sv']   === null ||
                    lista[dim][com]['ind'][i]['q0u']  === null ||
                    lista[dim][com]['ind'][i]['q0']   === null ||
                    lista[dim][com]['ind'][i]['q1']   === null ||
                    lista[dim][com]['ind'][i]['qr']   === null ||
                    lista[dim][com]['ind'][i]['tr']   === null ||
                    lista[dim][com]['ind'][i]['sv'].length   == 0 ||
                    lista[dim][com]['ind'][i]['q0u'].length  == 0 ||
                    lista[dim][com]['ind'][i]['q0'].length   == 0 ||
                    lista[dim][com]['ind'][i]['q1'].length   == 0 ||
                    lista[dim][com]['ind'][i]['qr'].length   == 0 ||
                    lista[dim][com]['ind'][i]['tr'].length   == 0) {
                    return false;
                }

            }

            return true;
        }

        function createChart(component, datasets, start, end) {
            var ctx     = $(component);

            var scatterChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: datasets
                },
                options: {
                    maintainAspectRatio : false,
                    legend : {
                        display : true,
                        position : 'bottom',
                        labels : {
                            boxWidth : 1,
                            padding : 20
                        }
                    },
                    tooltips: {
                        enabled : false
                    },
                    scales: {
                        xAxes: [{
                            type: 'linear',
                            position: 'bottom',
                            min: Math.floor(parseInt(start)),
                            max: Math.ceil(parseInt(end))
                        }],
                        yAxes: [{
                            ticks: {
                                max: 100,
                                min: 0,
                                stepSize: 10
                            }
                        }]
                    }
                }
            });
        }

        $(document).ready(function() {
            /**
             * Rendo cliccabili gli elementi della sidebar
             */
            $(".mainmenu .item").click(function () {
                $(".maingrid").hide();
                $(".mainmenu .item").removeClass('active');
                $(".dim-" + $(this).data('dim_id')).show();
                $(this).addClass('active');
                $('html, body').scrollTop(0);

                dimensione_selezionata = parseInt(  $(this).data('dim_id') );
                aggiornaGrafici();
            });

            // Clicco sul primo elemento della sidebar dopo aver caricato tutto
            $(".mainmenu .item").first().click();

            /**
             * Per ogni elemento di input, ad ogni sua modifica salvo la lista
             * degli indicatori
             */
            $(".input.number input").on('input', function () {
                var dim     = $(this).data("dim");
                var com     = $(this).data("com");
                var ind     = $(this).data("ind");
                var name    = $(this).attr("name");

                lista[dim][com]['ind'][ind][name] = $(this).val();

                // Esegui calcoli dopo le modifiche
                calcoliSuIndicatore(dim, com, ind);

                avviaTimerSalvataggioEGraphRedraw();
            });

            aggiornaTabellaIndicatori();

        });

    </script>
</head>
<body>

<div class="ui visible very inverted vertical sidebar menu mainmenu">
    <a class="item" data-dim_id="1">
        <i class="users icon"></i>1. Population and demographics
    </a>
    <a class="item" data-dim_id="2">
        <i class="tree icon"></i>2. Ecosystem and environmental
    </a>
    <a class="item" data-dim_id="3">
        <i class="travel icon"></i>3. Organized governmental services
    </a>
    <a class="item" data-dim_id="4">
        <i class="building outline icon"></i>4. Physical infrastructure
    </a>
    <a class="item" data-dim_id="5">
        <i class="university icon"></i>5. Lifestyle and community competence
    </a>
    <a class="item" data-dim_id="6">
        <i class="money icon"></i>6. Economic development
    </a>
    <a class="item" data-dim_id="7">
        <i class="map outline icon"></i>7. Social-cultural capital
    </a>
</div>


<?php
    /**
     * Stampo a schermo tutti gli elementi del Framework PEOPLES
     */
    echo $html;
?>

</body>
</html>