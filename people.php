<?php
/**
 * Author: Andrea Semprebon
 * Date: 29/04/17
 */

/**
 * LETTURA FILE
 */

// Leggo il contenuto del file con gli indicatori
$filename = "./indicators/indicators_auto.json";
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

$filename = "./template/indicator_content.html";
$handle = fopen($filename, "r");
$indicator_content = fread($handle, filesize($filename));
fclose($handle);


$filename   = $_POST['filename'];
$name       = $_POST['name'];
$action     = intval( $_POST['action'] );

// Se sto creando un nuovo file, gli assegno un nome casuale
if ($action == 2) {
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    $filename = generateRandomString(15) . "_" . time() . ".json";
}

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

            $info_text = "";
            if ( isset($ind['info']) && strlen($ind['info']) > 0 ) {
                $info_text = "<i class='info circle icon popup' data-content='" . $ind['info'] . "'></i>";
            }

            $ind_replace = array(   '{{__NAME__}}'          => $ind['name'],
                                    '{{__MEASURE__}}'       => $ind['measure'],
                                    '{{__ID__}}'            => $ind['id'],
                                    '{{__ID_FOR_HTML__}}'   => $ind_id_for_html,
                                    '{{__IMPORTANCE__}}'    => $ind['importance'],
                                    '{{__NAT__}}'           => $ind['nat'],
                                    '{{__DIM_ID__}}'        => $dim_id,
                                    '{{__COM_ID__}}'        => $com_id,
                                    '{{__IND_ID__}}'        => $ind_id,
                                    '{{__INFO__}}'          => $info_text);

            $ind_html = $indicator_content;

            $ind_html = strtr($ind_html, $ind_replace);

            $ind_total .= $ind_html;

            $nat        = (strtoupper($ind['nat']) == 'D') ? 'modalitaIndicator.dinamica' : 'modalitaIndicator.statica';
            $def_val    = 'null';
            $importance = ($ind['importance'] == '-') ? 0 : intval($ind['importance']);

            $js_indicators_array .= $ind_id . ': { q0u: null, sv: null, q0: null, q1: ' . $def_val . ', qr: ' . $def_val . ', 
                                    tr: ' . $def_val . ', i : ' . $importance . ', nat : ' . $nat . ' },';

            $total_i += $importance;
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
                            '{{__COMPONENTS_CONTENT__}}'    => $com_html,
                            '{{__CHART_NAME__}}'            => 'Chart');

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
    <link rel="stylesheet" type="text/css" href="css/people.css">

    <script type="application/javascript" src="js/Indicators.js" charset="UTF-8"></script>
    <script type="application/javascript" src="js/Indicator.js" charset="UTF-8"></script>

    <script type="application/javascript">
        var lista = <?php echo $js_indicators_array; ?>;
        var ind_chart_list = null;
        var dimensione_selezionata = -1;
        var name = '<?php echo $name; ?>';
        var common_resilience_curve_datasets = {};
        var common_resilience_curve_datasets_max_tr = 0;

        var indicators = new Indicators();
        indicators.callbackSuccesso = 'azioneGestioneIndicatorsTerminataConSuccesso';
        indicators.callbackErrore   = 'azioneGestioneIndicatorsTerminataConErrore';
        indicators.caricaLista(name, '<?php echo $filename; ?>' );

        function azioneGestioneIndicatorsTerminataConSuccesso(modalita, result) {
            $(".save-modal").html(result);
            setTimeout(function() {
                $(".save-modal").fadeOut(500);
            }, 10000);
        }

        function azioneGestioneIndicatorsTerminataConSuccesso(modalita, result) {
            if ( modalita == modalitaAPIIndicators.caricaLista ) {
                if (result !== null) {
                    name                    = result.name;

                    for (var d = 1; d <= Object.keys(result['lista']).length; d++) {
                        for (var c = 1; c <= Object.keys(result['lista'][d]).length; c++) {
                            for (var i = 1; i <= Object.keys(result['lista'][d][c]['ind']).length; i++) {
                                lista[d][c]['ind'][i] = result['lista'][d][c]['ind'][i];
                            }
                        }
                    }

                    for (var d = 1; d <= Object.keys(lista).length; d++) {
                        for (var c = 1; c <= Object.keys(lista[d]).length; c++) {

                            if ( !result['lista'][d].hasOwnProperty(c) ) {
                                delete lista[d][c];
                                c = 1;
                            }

                            for (var i = 1; i <= Object.keys(lista[d][c]['ind']).length; i++) {
                                if ( !result['lista'][d][c]['ind'].hasOwnProperty(i) ) {
                                    delete lista[d][c]['ind'][i];
                                    i = 1;
                                }
                            }
                        }
                    }

                    $("#sel-dim" + result.dim_selected).click();
                }
                aggiornaTabellaIndicatori(true);
            } else if ( modalita == modalitaAPIIndicators.salvaLista ) {
                $(".save-modal").html("Saved");
                setTimeout(function() {
                    $(".save-modal").fadeOut(500);
                }, 1000);
            }
        }

        var timerSave   = null;
        var timerRedraw = null;
        function avviaTimerSalvataggioEGraphRedraw() {
            avviaTimerSalvataggio();
            avviaTimerRedraw();
        }

        function avviaTimerSalvataggio() {
            clearTimeout(timerSave);
            timerSave = setTimeout(salvaListaIndicatori, 2000);
        }

        function avviaTimerRedraw() {
            clearTimeout(timerRedraw);
            timerRedraw = setTimeout(aggiornaGrafici, 2000);
        }

        function salvaListaIndicatori() {
            $(".save-modal").html("Saving...");
            $(".save-modal").fadeIn(500);
            indicators.salvaLista(name, '<?php echo $filename; ?>', lista, dimensione_selezionata );
        }

        function aggiornaGrafici() {
            aggiornaGraficiPerDimensione(dimensione_selezionata);
        }

        function aggiornaTabellaIndicatori(aggiornamentoDaLoading = false) {
            // Aggiorno i valori di ogni input
            $( ".input.number input" ).each(function() {
                var dim     = $(this).data("dim");
                var com     = $(this).data("com");
                var ind     = $(this).data("ind");
                var name    = $(this).attr("name");

                var value   = lista[dim][com]['ind'][ind][name];

                if ( value !== null) {
                    $(this).val( value );
                    $(this).attr("placeholder", "");
                } else {
                    $(this).val( "" );
                    $(this).attr("placeholder", name);
                }

                if ( name == 'i' && value == 0) {
                    $(this).parent().parent().hide();
                    $(this).parent().parent().parent().find('.show-on-indifference-of-importance').show();
                }

                if (aggiornamentoDaLoading) {
                    if (name == 'nat' || name == 'i') {
                        var val_da_mostrare = value;
                        if (name == 'nat') {
                            val_da_mostrare = (value == modalitaIndicator.statica) ? 'S' : 'D';
                        }
                        $(this).parent().find('.text').html(val_da_mostrare);

                        $(this).parent().find('.menu .item').each(function () {
                            if ($(this).data('value') == value) {
                                $(this).addClass('active selected');
                            } else {
                                $(this).removeClass('active selected');
                            }
                        });
                    }

                }

                if (name == 'q1' || name == 'qr' || name == 'tr') {
                    // Imposto i campi visibili per i campi statici e dinamici
                    var nat = lista[dim][com]['ind'][ind]['nat'];

                    if (nat == modalitaIndicator.statica) {
                        $(this).attr('type', 'hidden');
                        $(this).parent().parent().find('.show-on-static').show();
                    } else {
                        $(this).attr('type', 'text');
                        $(this).parent().parent().find('.show-on-static').hide();
                    }
                }
            });

            aggiornaListaGraficiIndicatori();
            avviaTimerRedraw();
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

            if ( a - 1 < 0 ) {
                a = min(2, data.length);
            }

            if ( b - 1 < 0 ) {
                b = min(2, data.length);
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

        function hexToRGB(hex, alpha) {
            var r = parseInt(hex.slice(1, 3), 16),
                g = parseInt(hex.slice(3, 5), 16),
                b = parseInt(hex.slice(5, 7), 16);

            if (alpha) {
                return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
            } else {
                return "rgb(" + r + ", " + g + ", " + b + ")";
            }
        }


        function aggiornaGraficiPerDimensione(dim) {

            if (dim == 8) {
                aggiornaGraficoCommonResilienceCurve();
                return;
            }

            if (lista == null || lista == 'undefined' ||
                lista[dim] == null || lista[dim] == 'undefined') {
                return false;
            }

            var max_tr      = 0;
            var max_tr_dim  = 0;
            var comp_validi = new Array();
            var comp_errore = new Array();

            /**
             * Il max_tr lo cerco ovunque, così è più semplice plottare la common
             * resilience curve, mentre i componenti validi li considero solo per la
             * dimensione corrente
             */

            for (var d = 1; d <= 7; d++) {

                for (var c = 1; c <= Object.keys(lista[d]).length; c++) {
                    if (sonoIndicatoriValidiPerComponente(d, c)) {
                        for (var i = 1; i <= Object.keys(lista[d][c]['ind']).length; i++) {
                            var t = parseFloat(lista[d][c]['ind'][i]['tr']);

                            if (lista[d][c]['ind'][i]['nat'] == modalitaIndicator.dinamica) {
                                max_tr = Math.max(max_tr, t);

                                if ( d == dim ) { max_tr_dim = Math.max(max_tr_dim, t); }
                            }
                        }

                        if ( d == dim ) { comp_validi.push(c); }

                    } else {

                        if ( d == dim ) { comp_errore.push(c); }

                    }
                }
            }

            max_tr = Math.max(max_tr, 10);

            max_tr_dim = Math.max(max_tr_dim, 10);

            common_resilience_curve_datasets_max_tr = Math.max(max_tr, common_resilience_curve_datasets_max_tr);

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
                    borderColor: hexToRGB(lista[dim][c]['color'], 0.5),
                    fill: false,
                    pointRadius: 0,
                    pointHitRadius: 0,
                    pointHoverRadius: 0,
                    borderWidth: 1
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
                mostraValoreIntegrale(dim, lista[dim][c]['name'], lista[dim][c]['color'], max_tr, integral);
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
                        pointRadius: 0,
                        pointHitRadius: 0,
                        pointHoverRadius: 0,
                        borderWidth: 3
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
                    var integral = 100 - (trapz(data, 0, max_tr)/max_tr);
                    mostraValoreIntegrale(dim, 'All components', '#000000', max_tr, integral);
                    datasets.push(dataset);

                    common_resilience_curve_datasets[dim] = data;

                }

                var span_dim = Math.floor(max_tr_dim * 0.1);
                createChart(ind_chart_list[dim]['graph_id'], datasets, -span_dim, max_tr_dim + span_dim);
            }

        }

        function dimDataFromId(id) {
            var dim_id_name = { 1 : {   name    : 'Population and demographics',
                                        color   : '#D95C5C',
                                        i       : 2 },
                                2 : {   name    : 'Ecosystem and environmental',
                                        color   : '#E07B53',
                                        i       : 2 },
                                3 : {   name    : 'Organized governmental services',
                                        color   : '#F2C61F',
                                        i       : 3 },
                                4 : {   name    : 'Physical infrastructure',
                                        color   : '#B5CC18',
                                        i       : 3 },
                                5 : {   name    : 'Lifestyle and community competence',
                                        color   : '#5BBD72',
                                        i       : 1 },
                                6 : {   name    : 'Economic development',
                                        color   : '#00B5AD',
                                        i       : 3 },
                                7 : {   name    : 'Social-cultural capital',
                                        color   : '#3B83C0',
                                        i       : 2 } };
            return dim_id_name[id];
        }
        
        function aggiornaGraficoCommonResilienceCurve() {
            var msgs_id_class = ".dim-8 .plot .messaggi";
            $(msgs_id_class).html("");

            common_resilience_curve_datasets_max_tr = 0;
            common_resilience_curve_datasets        = {};

            var datasets = new Array();

            var max_length = 0;
            var valid_dim_count = 0;

            /**
             * Aggiungo i 7 grafici delle varie dimensioni
             */
            for (var dim = 1; dim < 8; dim++) {
                aggiornaGraficiPerDimensione(dim);

                var dataset = {
                    label: dimDataFromId(dim)['name'],
                    lineTension: 0.5,
                    borderColor: dimDataFromId(dim)['color'],
                    fill: false,
                    pointRadius: 0,
                    pointHitRadius: 0,
                    pointHoverRadius: 0,
                    borderWidth: 1
                };

                if ( common_resilience_curve_datasets.hasOwnProperty(dim) ) {
                    dataset['data'] = common_resilience_curve_datasets[dim];
                    datasets.push(dataset);

                    max_length = common_resilience_curve_datasets[dim].length;
                    valid_dim_count++;

                    var integral = 100 - (trapz(common_resilience_curve_datasets[dim], 0, common_resilience_curve_datasets_max_tr) / common_resilience_curve_datasets_max_tr);
                    mostraValoreIntegrale(8, dimDataFromId(dim)['name'], dimDataFromId(dim)['color'], common_resilience_curve_datasets_max_tr, integral);

                }

            }

            /**
             * Aggiungo il grafico finale
             */

            if ( valid_dim_count > 1 ) {
                var dataset = {
                    label: 'Common Resilience Curve',
                    lineTension: 0.5,
                    borderColor: 'black',
                    fill: false,
                    pointRadius: 0,
                    pointHitRadius: 0,
                    pointHoverRadius: 0,
                    borderWidth: 2
                };

                var datasetArea = {
                    label: '',
                    lineTension: 0.5,
                    borderColor: 'black',
                    fill: true,
                    pointRadius: 0,
                    pointHitRadius: 0,
                    pointHoverRadius: 0,
                    borderWidth: 0.1,
                    showLine: true
                };

                var data        = new Array();
                var dataArea    = new Array();

                for (var i = 0; i < max_length; i++) {

                    var x_t     = 0;
                    var y_val   = 0;
                    var total_i = 0;

                    for (var dim = 1; dim < 8; dim++) {

                        if (common_resilience_curve_datasets.hasOwnProperty(dim)) {
                            var elem = common_resilience_curve_datasets[dim][i];
                            x_t = elem['x'];
                            y_val += elem['y'] * dimDataFromId(dim)['i'];
                            total_i += dimDataFromId(dim)['i'];
                        }
                    }

                    data.push({x: x_t, y: y_val / total_i});

                    if ( x_t >= 0 && x_t <= common_resilience_curve_datasets_max_tr) {
                        dataArea.push({x: x_t, y: y_val / total_i});
                    }

                }

                dataset['data'] = data;
                datasetArea['data'] = dataArea;
                datasets.push(dataset);
                datasets.push(datasetArea);

                var integral = 100 - (trapz(data, 0, common_resilience_curve_datasets_max_tr) / common_resilience_curve_datasets_max_tr);
                mostraValoreIntegrale(8, 'Common Resilience Curve', '#000000', common_resilience_curve_datasets_max_tr, integral);


            }

            var span = Math.floor( common_resilience_curve_datasets_max_tr * 0.1 );
            createChart('#chart_8', datasets, -span, common_resilience_curve_datasets_max_tr + span);
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

        function mostraValoreIntegrale(dim, name, color, max_tr, ris) {
            var msgs_id_class = ".dim-" + dim + " .plot .messaggi";

            var int = "<div class='integral''></div>";
            var math_text = "\\int_{0}^{{{__MAX_TR__}}} \\frac{100 - Q(x)}{{{__MAX_TR__}}}dx = {{__RIS__}}\\%";

            math_text = math_text.replace("{{__MAX_TR__}}", max_tr);
            math_text = math_text.replace("{{__MAX_TR__}}", max_tr);
            math_text = math_text.replace("{{__RIS__}}",    ris.toFixed(2));

            //$(msgs_id_class).append(int);

            $(msgs_id_class).append( "<div class='integral'><span class='nome' style='color: " + color + "'>LOR " + name + ":</span><br/>" + katex.renderToString(math_text, {displayMode: true}) + "</div>" );
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
                // Valido per qualunque indicatore. SV, Q0u e Q0 devono essere sempre presenti
                if (lista[dim][com]['ind'][i]['sv']   === null ||
                    lista[dim][com]['ind'][i]['q0u']  === null ||
                    lista[dim][com]['ind'][i]['q0']   === null ||
                    lista[dim][com]['ind'][i]['sv'].length   == 0 ||
                    lista[dim][com]['ind'][i]['q0u'].length  == 0 ||
                    lista[dim][com]['ind'][i]['q0'].length   == 0
                   ) {
                    return false;
                }

                // Altri campi fondamentali solo per indicatori dinamici
                if ( lista[dim][com]['ind'][i]['nat'] == modalitaIndicator.dinamica && (
                        lista[dim][com]['ind'][i]['q1']   === null ||
                        lista[dim][com]['ind'][i]['qr']   === null ||
                        lista[dim][com]['ind'][i]['tr']   === null ||
                        lista[dim][com]['ind'][i]['q1'].length   == 0 ||
                        lista[dim][com]['ind'][i]['qr'].length   == 0 ||
                        lista[dim][com]['ind'][i]['tr'].length   == 0
                    )) {
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
                            padding : 20,
                            filter : function (item, char_data) {
                                return (item.text.length > 0);
                            }
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
            $(".mainmenu a.item").click(function () {
                var new_dim = parseInt(  $(this).data('dim_id') );
                $(".maingrid").hide();
                $(".mainmenu .item").removeClass('active');
                $(".dim-" + new_dim).show();
                $(this).addClass('active');
                $('html, body').scrollTop(0);

                if (dimensione_selezionata != -1 && new_dim != 8) {
                    avviaTimerSalvataggio();
                }

                dimensione_selezionata = new_dim;
                aggiornaGrafici();

            });

            $(".mainmenu div.item.nome").click(function () {
                window.open("./index.php","_self");
            });

            // Clicco sul primo elemento della sidebar dopo aver caricato tutto
            $(".mainmenu a.item").first().click();

            /**
             * Per ogni elemento di input, ad ogni sua modifica salvo la lista
             * degli indicatori
             */
            $(".input.number input").on('input', function () {
                var dim     = $(this).data("dim");
                var com     = $(this).data("com");
                var ind     = $(this).data("ind");
                var name    = $(this).attr("name");
                var val     = $(this).val();

                var regex = /[a-zA-Z]+/;
                if ( val.match(regex) ) {
                    $(this).val( lista[dim][com]['ind'][ind][name] );
                    return false;
                }

                lista[dim][com]['ind'][ind][name] = $(this).val();

                // Esegui calcoli dopo le modifiche
                calcoliSuIndicatore(dim, com, ind);

                avviaTimerSalvataggioEGraphRedraw();
            });

            $("th").each(function () {
                $(this).popup();
            });

            $(".info.icon.popup").each(function () {
                $(this).popup();
            });

            $(".ui.selection.dropdown").dropdown({
                onChange: function (value, text, selectedItem) {
                    var input = selectedItem.parent().parent().find('input');

                    var dim     = input.data("dim");
                    var com     = input.data("com");
                    var ind     = input.data("ind");
                    var name    = input.attr("name");

                    lista[dim][com]['ind'][ind][name] = parseInt( input.val() );
                    aggiornaTabellaIndicatori();

                    avviaTimerSalvataggioEGraphRedraw();

                }
            });

            aggiornaTabellaIndicatori();

        });

    </script>
</head>
<body>

<div class="ui visible very inverted vertical sidebar menu mainmenu">
    <div class="item nome">
        <i class="caret left icon"></i>
        <div class="text">
            <?php echo $name; ?>
        </div>
    </div>

    <a class="item" id="sel-dim1" data-dim_id="1">
        <i class="users icon"></i>1. Population and demographics
    </a>
    <a class="item" id="sel-dim2" data-dim_id="2">
        <i class="tree icon"></i>2. Ecosystem and environmental
    </a>
    <a class="item" id="sel-dim3" data-dim_id="3">
        <i class="travel icon"></i>3. Organized governmental services
    </a>
    <a class="item" id="sel-dim4" data-dim_id="4">
        <i class="building outline icon"></i>4. Physical infrastructure
    </a>
    <a class="item" id="sel-dim5" data-dim_id="5">
        <i class="university icon"></i>5. Lifestyle and community competence
    </a>
    <a class="item" id="sel-dim6" data-dim_id="6">
        <i class="money icon"></i>6. Economic development
    </a>
    <a class="item" id="sel-dim7" data-dim_id="7">
        <i class="map outline icon"></i>7. Social-cultural capital
    </a>

    <a class="item" id="sel-dim8" data-dim_id="8">
        <i class="area chart icon"></i>The community resilience curve
    </a>
</div>

<?php
/**
 * Stampo a schermo tutti gli elementi del Framework PEOPLES
 */
echo $html;
?>

<?php

$com_res_curve = $dimension_content;
$com_res_curve = str_replace("{{__COMPONENTS_CONTENT__}}", "", $com_res_curve);
$com_res_curve = str_replace("{{__DIM_ID__}}", "8", $com_res_curve);
$com_res_curve = str_replace("{{__CHART_NAME__}}", "The Community Resilience Curve", $com_res_curve);
$com_res_curve = str_replace("<div class=\"ui divider\"></div>", "", $com_res_curve);

echo $com_res_curve;
?>

<div class="save-modal" style="display: none;">
    Saving...
</div>

</body>
</html>