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

$filename = "./template/indicator_content.html";
$handle = fopen($filename, "r");
$indicator_content = fread($handle, filesize($filename));
fclose($handle);

$html = "";

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

$create_chart_call_function = '';

foreach ($dimensions as $dim_id => $dim) {

    $html .= '<div id="dim_' . $dim['id'] . '">';

    $color_array_index = 0;
    foreach ($dim['components'] as $com_id => $com) {

        $com_id_for_html = str_replace(".", "-", $com['id']);

        $com_html = str_replace("{{__NAME__}}",         $com['name'],       $dimension_content);
        $com_html = str_replace("{{__ID__}}",           $com['id'],         $com_html);
        $com_html = str_replace("{{__ID_FOR_HTML__}}",  $com_id_for_html,   $com_html);
        $com_html = str_replace("{{__IMPORTANCE__}}",   $com['importance'], $com_html);
        $com_html = str_replace("{{__DIM_ID__}}",       $dim['id'],         $com_html);

        $color_name = $color_array_names[ $color_array_index % $color_array_size ];
        $color_code = $color_array[ $color_name ];
        $color_array_index++;
        $com_html = str_replace("{{__COLOR__}}",        $color_name, $com_html);
        $com_html = str_replace("{{__COLOR_CODE__}}",   $color_code, $com_html);

        $ind_total = '';

        foreach ($com['indicators'] as $ind_id => $ind) {
            $ind_id_for_html = str_replace(".", "-", $ind['id']);

            $ind_html = str_replace("{{__NAME__}}",         $ind['name'],       $indicator_content);
            $ind_html = str_replace("{{__MEASURE__}}",      $ind['measure'],    $ind_html);
            $ind_html = str_replace("{{__ID__}}",           $ind['id'],         $ind_html);
            $ind_html = str_replace("{{__ID_FOR_HTML__}}",  $ind_id_for_html,   $ind_html);
            $ind_html = str_replace("{{__IMPORTANCE__}}",   $ind['importance'], $ind_html);
            $ind_html = str_replace("{{__NAT__}}",          $ind['nat'],        $ind_html);

            $ind_total .= $ind_html;
        }

        $com_html = str_replace("{{__INDICATORS_CONTENT__}}", $ind_total, $com_html);

        // Aggiungo all'html finale
        $html .= $com_html;

        // Aggiungo la chiamata per inizializzare il grafico
        $create_chart_call_function .= 'createChart("#chart_' . $com_id_for_html . '");';
    }


    $html .= "</div>";
}

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

    <!--<link rel="stylesheet" href="mathscribe/jqmath-0.4.3.css">
    <script src="mathscribe/jqmath-etc-0.4.6.min.js" charset="utf-8"></script>-->

    <script src="chartjs/dist/Chart.min.js" charset="UTF-8"></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">

    <script type="application/javascript">
        $(document).ready(function() {

            $(".mainmenu .item").click(function () {
                $(".maingrid").hide();
                $(".mainmenu .item").removeClass('active');
                $(".dim-" + $(this).data('dim_id')).show();
                $(this).addClass('active');
                $('html, body').scrollTop(0);
            });

            $(".mainmenu .item").first().click();

            function createChart(component) {
                var ctx = $(component);
                var color = $(component).data("color");

                var scatterChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [{
                            lineTension: 0.5,
                            borderColor: color,
                            fill : false,
                            pointRadius: 1,
                            pointHitRadius: 1,
                            pointHoverRadius: 1,
                            data: [ {x: 0, y: 10},
                                {x: 10, y: 20},
                                {x: 20, y: 10},
                                {x: 30, y: 40}]
                        }]
                    },
                    options: {
                        maintainAspectRatio : false,
                        legend : {
                            display : false
                        },
                        tooltips: {
                            enabled : false
                        },
                        scales: {
                            xAxes: [{
                                type: 'linear',
                                position: 'bottom',
                                ticks: {
                                    min: 0
                                }
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

            <?php
                echo $create_chart_call_function;
            ?>


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
