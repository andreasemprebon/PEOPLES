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

?>
<html>
<head>
    <title>PEOPLES Indicators Editor</title>

    <!-- INCLUDO SEMANTIC-UI E JQUERY -->
    <link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="semantic/dist/semantic.min.js"></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/indicators_edit.css">

    <script type="application/javascript">
        var indicators = $.parseJSON('<?php echo $contents; ?>');

        var timerSave   = null;
        function avviaTimerSalvataggio() {
            $(".save-modal").html("Saving...");
            $(".save-modal").fadeIn(500);
            clearTimeout(timerSave);
            timerSave = setTimeout(salvaIndicators, 500);
        }

        function salvaIndicators() {
            var that = this;
            $.ajax({
                type: "POST",
                url: "./ajax/save_peoples.php",
                data: { lista : JSON.stringify(indicators) },
                cache: false,
                success: function(html) {
                    //console.log(html);
                    var json = false;
                    try {
                        json = $.parseJSON(html);
                    }
                    catch(err) {
                        //that.lanciaCallbackErrore("Il formato dei dati restituito dall'elaborazione sembra essere non valido. <br/>" + err.message + "<br />" + html);
                        return;
                    }

                    if ( json.status !== 0 ) {
                        //that.lanciaCallbackErrore(json.desc);
                        return;
                    }

                    $(".save-modal").html("Saved");
                    setTimeout(function() {
                        $(".save-modal").fadeOut(500);
                    }, 1000);
                    //that.lanciaCallbackSuccesso(json.result);
                } ,
                error: function(html) {
                    //that.lanciaCallbackErrore("Impossibile raggiungere la pagina di gestione degli indicatori.");
                }
            });
        }

        function popolaTabella(id, array, new_button_text = "", dim = null, com = null) {

            var html = '';

            for (var i = 0; i < array.length; i++) {
                var elem = array[i];
                console.log(elem);
                html += '<tr><td data-dim="' + elem['dim'] + '" data-com="' + elem['com'] + '" data-ind="' + elem['ind'] + '">';

                if ( new_button_text.length > 0 ) {
                    html += '<i class="trash outline icon red elimina"></i>';
                }

                html += elem['dim'];
                if ( elem['com'] != null ) {
                    html += "." + elem['com'];
                }
                if ( elem['ind'] != null ) {
                    html += "." + elem['ind'];
                }
                html += " " + elem['name'];
                html += '</td></tr>';
            }

            if ( new_button_text.length > 0 ) {
                html += '<tr><td>';
                html += '<button class="ui button" id="cancel-button" data-dim="' + dim + '" data-com="' + com + '">' + new_button_text + '</button>';
                html += '</td></tr>';
            }

            $(id + " tbody").html(html);

        }

        function popolaDimensions() {
            var arr = new Array();
            for (var d = 1; d <= Object.keys(indicators).length; d++) {
                arr.push({
                    name: indicators[d]['name'],
                    dim: d,
                    com: null,
                    ind: null
                });

            }
            popolaTabella("#dimensions-table", arr, "Add Dimension");
        }

        function popolaIndicatorForm(d, c, i) {

            if ( indicators[d]['components'][c] == 'undefined' ) {
                return;
            }

            var elem = indicators[d]['components'][c]['indicators'][i];

            $("#indicator-form input[name='dim']").val(d);
            $("#indicator-form input[name='com']").val(c);
            $("#indicator-form input[name='ind']").val(i);

            $("#indicator-form input[name='name']").val( elem['name'] );
            $("#indicator-form input[name='measure']").val( elem['measure'] );

            if ( !elem.hasOwnProperty('info') ) {
                indicators[d]['components'][c]['indicators'][i]['info'] = "";
                $("#indicator-form input[name='info']").val("");
            } else {
                $("#indicator-form input[name='info']").val( elem['info'] );
            }

            $("#indicator-form .nature-dropdown").dropdown('set selected', elem['nat']);
            $("#indicator-form .importance-dropdown").dropdown('set selected', elem['importance']);

            $("#indicator-form .field").removeClass("disabled");
        }

        $(document).ready(function () {

            $("#dimensions-table").on('click', 'tr td button', function () {
                var d = $(this).data('dim');
                $("#new-dimensions-form input[name='dim']").val( d );

                $("#dimension-modal").modal({
                    onDeny    : function(){

                    },
                    onApprove : function() {
                        console.log("Approve");
                        var l = Object.keys(indicators).length;
                        indicators[l+1] = {     'name'       : $("#new-dimension-form input[name='name']").val(),
                                                'importance' : $("#new-dimension-form input[name='importance']").val(),
                                                'components' : {},
                                                'id'         : (l+1)};
                        popolaDimensions();
                        avviaTimerSalvataggio();
                    }
                }).modal("show");
            });

            $("#dimensions-table").on('click', 'tr td', function () {
                var arr = new Array();
                var d = $(this).data("dim");

                for (var c = 1; c <= Object.keys(indicators[d]['components']).length; c++) {
                    arr.push({
                        name: indicators[d]['components'][c]['name'],
                        dim: d,
                        com: c,
                        ind: null
                    });

                }

                $("#indicators-table tbody").html("");
                $("#indicator-form .field").addClass("disabled");

                popolaTabella("#components-table", arr, "Add Component", d);

            });

            $("#components-table").on('click', 'tr td', function () {
                var arr = new Array();
                var d = $(this).data("dim");
                var c = $(this).data("com");

                for (var i = 1; i <= Object.keys(indicators[d]['components'][c]['indicators']).length; i++) {
                    arr.push({
                        name: indicators[d]['components'][c]['indicators'][i]['name'],
                        dim: d,
                        com: c,
                        ind: i
                    });

                }
                popolaTabella("#indicators-table", arr, "Add Indicator", d, c);

            });

            $("#components-table").on('click', 'tr td button', function () {
                var d = $(this).data('dim');
                $("#new-component-form input[name='dim']").val( d );

                $("#component-modal").modal({
                    onDeny    : function(){

                    },
                    onApprove : function() {
                        console.log("Approve");
                        var l = Object.keys(indicators[d]['components']).length;
                        indicators[d]['components'][l+1] = {    'name'       : $("#new-component-form input[name='name']").val(),
                                                                'importance' : $("#new-component-form input[name='importance']").val(),
                                                                'indicators' : {},
                                                                'id'         : d + "." + (l+1)};
                        $("#dimensions-table tr td")[d-1].click();
                        avviaTimerSalvataggio();
                    }
                }).modal("show");
            });

            $("#indicators-table").on('click', 'tr td', function () {
                var arr = new Array();
                var d = $(this).data("dim");
                var c = $(this).data("com");
                var i = $(this).data("ind");

                popolaIndicatorForm(d, c, i);

            });

            $("#indicator-form #cancel-button").click(function (e) {
                var d = $("#indicator-form input[name='dim']").val();
                var c = $("#indicator-form input[name='com']").val();
                var i = $("#indicator-form input[name='ind']").val();

                popolaIndicatorForm(d, c, i);

                e.preventDefault();
            });

            $("#indicator-form #save-button").click(function (e) {
                var d = $("#indicator-form input[name='dim']").val();
                var c = $("#indicator-form input[name='com']").val();
                var i = $("#indicator-form input[name='ind']").val();

                indicators[d]['components'][c]['indicators'][i]['name']         = $("#indicator-form input[name='name']").val();
                indicators[d]['components'][c]['indicators'][i]['measure']      = $("#indicator-form input[name='measure']").val();
                indicators[d]['components'][c]['indicators'][i]['info']         = $("#indicator-form input[name='info']").val();
                indicators[d]['components'][c]['indicators'][i]['nat']          = $("#indicator-form input[name='nature']").val();
                indicators[d]['components'][c]['indicators'][i]['importance']   = $("#indicator-form input[name='importance']").val();

                e.preventDefault();

                avviaTimerSalvataggio();
            });

            $("#indicators-table").on('click', 'tr td button', function () {
                var d = $(this).data('dim');
                var c = $(this).data('com');
                $("#new-indicator-form input[name='dim']").val( d );
                $("#new-indicator-form input[name='com']").val( c );

                $("#indicator-modal").modal({
                    onDeny    : function(){

                    },
                    onApprove : function() {
                        var l = Object.keys(indicators[d]['components'][c]['indicators']).length;
                        indicators[d]['components'][c]['indicators'][l+1] = {
                            'name'       : $("#new-indicator-form input[name='name']").val(),
                            'importance' : $("#new-indicator-form input[name='importance']").val(),
                            'measure'    : $("#new-indicator-form input[name='measure']").val(),
                            'info'       : $("#new-indicator-form input[name='info']").val(),
                            'nat'        : $("#new-indicator-form input[name='nature']").val(),
                            'id'         : d + "." + c + "." +(l+1)
                        };
                        $("#components-table tr td")[c-1].click();
                        avviaTimerSalvataggio();
                    }
                }).modal("show");
            });

            $(".ui.grid").on('click', '.trash.icon.elimina', function () {
                var d = $(this).parent().data('dim');
                var c = $(this).parent().data('com');
                var i = $(this).parent().data('ind');
                console.log(d + "." + c + "." +i);

                $("#elimina-modal").modal({
                    onDeny    : function(){

                    },
                    onApprove : function() {
                        if ( d != null ) {

                            if ( c != null ) {
                                if (i == null) {
                                    delete indicators[d]['components'][c];
                                    $("#dimensions-table tr td")[d - 1].click();
                                } else {
                                    delete indicators[d]['components'][c]['indicators'][i];
                                    $("#components-table tr td")[c - 1].click();
                                }
                            } else {
                                delete indicators[d];
                                popolaDimensions();
                                $("#dimensions-table tr td")[0].click();
                            }

                            avviaTimerSalvataggio();
                        }
                    }
                }).modal("show");
            });

            popolaDimensions();

            $(".ui.dropdown").dropdown();

        });

    </script>
</head>
<body>

<div class="ui grid center centered">

    <div class="three wide column">
        <table class="ui very basic collapsing celled table" id="dimensions-table">
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="three wide column">
        <table class="ui very basic collapsing celled table" id="components-table">
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="three wide column">
        <table class="ui very basic collapsing celled table" id="indicators-table">
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="five wide column">
        <form class="ui form fluid" id="indicator-form">
            <input type="hidden" name="dim">
            <input type="hidden" name="com">
            <input type="hidden" name="ind">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" placeholder="">
            </div>
            <div class="field">
                <label>Measure</label>
                <input type="text" name="measure" placeholder="">
            </div>
            <div class="field">
                <label>Info</label>
                <input type="text" name="info" placeholder="">
            </div>
            <div class="field">
                <div class="ui selection dropdown nature-dropdown">
                    <input type="hidden" name="nature">
                    <i class="dropdown icon"></i>
                    <div class="default text">Nature</div>
                    <div class="menu">
                        <div class="item" data-value="1">S</div>
                        <div class="item" data-value="2">D</div>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="ui selection dropdown importance-dropdown">
                    <input type="hidden" name="importance">
                    <i class="dropdown icon"></i>
                    <div class="default text">Importance</div>
                    <div class="menu">
                        <div class="item" data-value="1">1</div>
                        <div class="item" data-value="2">2</div>
                        <div class="item" data-value="3">3</div>
                    </div>
                </div>
            </div>
            <div class="ui buttons">
                <button class="ui button" id="cancel-button">Cancel</button>
                <div class="or"></div>
                <button class="ui positive button" id="save-button">Save</button>
            </div>
        </form>
    </div>

</div>


<div class="ui modal" id="dimension-modal">
    <i class="close icon"></i>
    <div class="header">
        Add Dimension
    </div>
    <div class="content">
        <form class="ui form fluid" id="new-dimension-form">
            <input type="hidden" name="dim">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" placeholder="">
            </div>
            <div class="field">
                <div class="ui selection dropdown importance-dropdown">
                    <input type="hidden" name="importance">
                    <i class="dropdown icon"></i>
                    <div class="default text">Importance</div>
                    <div class="menu">
                        <div class="item" data-value="1">1</div>
                        <div class="item" data-value="2">2</div>
                        <div class="item" data-value="3">3</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            Cancel
        </div>
        <div class="ui positive right labeled icon button">
            Add Dimension
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div class="ui modal" id="component-modal">
    <i class="close icon"></i>
    <div class="header">
        Add Component
    </div>
    <div class="content">
        <form class="ui form fluid" id="new-component-form">
            <input type="hidden" name="dim">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" placeholder="">
            </div>
            <div class="field">
                <div class="ui selection dropdown importance-dropdown">
                    <input type="hidden" name="importance">
                    <i class="dropdown icon"></i>
                    <div class="default text">Importance</div>
                    <div class="menu">
                        <div class="item" data-value="1">1</div>
                        <div class="item" data-value="2">2</div>
                        <div class="item" data-value="3">3</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            Cancel
        </div>
        <div class="ui positive right labeled icon button">
            Add Component
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div class="ui modal" id="indicator-modal">
    <i class="close icon"></i>
    <div class="header">
        Add Indicator
    </div>
    <div class="content">
        <form class="ui form fluid" id="new-indicator-form">
            <input type="hidden" name="dim">
            <input type="hidden" name="com">
            <input type="hidden" name="ind">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" placeholder="">
            </div>
            <div class="field">
                <label>Measure</label>
                <input type="text" name="measure" placeholder="">
            </div>
            <div class="field">
                <label>Info</label>
                <input type="text" name="info" placeholder="">
            </div>
            <div class="field">
                <div class="ui selection dropdown nature-dropdown">
                    <input type="hidden" name="nature">
                    <i class="dropdown icon"></i>
                    <div class="default text">Nature</div>
                    <div class="menu">
                        <div class="item" data-value="1">S</div>
                        <div class="item" data-value="2">D</div>
                    </div>
                </div>
            </div>
            <div class="field">
                <div class="ui selection dropdown importance-dropdown">
                    <input type="hidden" name="importance">
                    <i class="dropdown icon"></i>
                    <div class="default text">Importance</div>
                    <div class="menu">
                        <div class="item" data-value="1">1</div>
                        <div class="item" data-value="2">2</div>
                        <div class="item" data-value="3">3</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            Cancel
        </div>
        <div class="ui positive right labeled icon button">
            Add Indicator
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div class="ui modal" id="elimina-modal">
    <i class="close icon"></i>
    <div class="header">
        Sei sicuro di volerlo eliminare?
    </div>
    <div class="actions">
        <div class="ui black deny button">
            Cancel
        </div>
        <div class="ui positive right labeled icon button">
            Elimina
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div class="save-modal" style="display: none;">
    Saving...
</div>

</body>
</html>
