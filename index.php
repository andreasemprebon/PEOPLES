<?php
/**
 * Created by Andrea
 * Date: 02/05/17
 * Time: 10:18
 */

session_start();

if ( !isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0 ) {
    header('Location: ./login.php');
    die();
}
$user_is_admin = true;
$show_error = false;

require_once( dirname(__FILE__) . '/database/DatabasePeople.php');

$databasePeople = new DatabasePeople();

$result = $databasePeople->scenariPerUtente( $_SESSION['user_id'] );

$show_error     = $result['error'];
$error_message  = $result['desc'];

$scenari = array();

if ( $result['result'] == NULL ) {
    $show_error = true;
    $error_message = 'Si è verificato un errore sconosciuto';
} else {
    $scenari = $result['result'];
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


    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">

    <script type="application/javascript">
        $(document).ready(function() {
            $(".scenario-row").click(function () {
                $("#form_name").val( $(this).data("name") );
                $("#form_id").val( $(this).data("scenario-id") );
                $("#form_scenario").submit();
            });

            $(".btn_new_scenario").click(function () {
                $("#form_name").val( $("#new_name").val() );
                $("#form_id").val( "-1" );
                $("#form_scenario").submit();
            });
        });

    </script>
</head>
<body>
<div class="ui middle aligned centered grid maingrid">
    <div class="ui two column middle aligned centered grid">
        <div class="column">
            <h2 class="ui header component">
                <div class="content ">
                    <span class="text black">PEOPLES Framework
                        <?php if ($user_is_admin): ?>
                            <a href="./indicators_edit.php"><i class="settings icon"></i></a>
                        <?php endif; ?>
                    </span>
                    <div class="login-info">

                        Logged as <?php echo $_SESSION['username']; ?>
                        <a class="ui labeled button red btn_logout" href="./logout.php">
                            Logout
                        </a>

                    </div>
                </div>
            </h2>
        </div>
        <div class="four column centered row">
            <div class="column">
                <?php if ($show_error): ?>
                    <div class="ui red message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <table class="ui celled striped table">
                    <thead>
                    <tr><th colspan="1">
                            Saved Scenario
                        </th>
                    </tr></thead>
                    <tbody>

                    <?php

                    foreach ($scenari as $scenario) {
                        $scenario_id    = $scenario['id'];
                        $name           = $scenario['name'];
                        echo '<tr>';
                        echo '<td class="collapsing scenario-row" data-scenario-id="' . $scenario_id . '" data-name="' . $name . '">';
                        echo '<i class="folder icon"></i>';
                        echo $name;
                        echo '</td>';
                        echo '</tr>';
                    }

                    ?>


                    </tbody>
                </table>
            </div>
            <div class="ui vertical divider">
                or
            </div>
            <div class="center aligned column">
               <div class="ui fluid input">
                   <input type="text" name="name" id="new_name" placeholder="Name">
               </div>
                <button class="ui labeled icon button btn_new_scenario" type="submit">
                    <i class="plus icon"></i>
                    Create New Scenario
                </button>

            </div>
        </div>
    </div>
</div>

<form action="./people.php" method="POST" id="form_scenario">
    <input type="text" id="form_name"     name="name"   hidden>
    <input type="text" id="form_id"       name="id"     hidden>
</form>
</body>
</html>
