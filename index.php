<?php
/**
 * Created by Andrea
 * Date: 02/05/17
 * Time: 10:18
 */

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
                $("#form_filename").val( $(this).data("filename") );
                $("#form_name").val( $(this).data("name") );
                $("#form_action").val( "1" );
                $("#form_scenario").submit();
            });

            $(".btn_new_scenario").click(function () {
                $("#form_name").val( $("#new_name").val() );
                $("#form_action").val( "2" );
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
                    <span class="text black">PEOPLES Framework <a href="./indicators_edit.php"><i class="settings icon"></i></a></span>
                </div>
            </h2>
        </div>
        <div class="four column centered row">
            <div class="column">
                <table class="ui celled striped table">
                    <thead>
                    <tr><th colspan="1">
                            Saved Scenario
                        </th>
                    </tr></thead>
                    <tbody>

                    <?php

                    $directory 	= getcwd() . '/scenario/';
                    $files 		= array_diff( scandir($directory), array('..', '.', '.DS_Store'));

                    foreach ($files as $file) {
                        $json           = file_get_contents($directory . $file);
                        $filecontent    = json_decode($json, true);
                        $filename = $filecontent['filename'];
                        $name     = $filecontent['name'];
                        echo '<tr>';
                        echo '<td class="collapsing scenario-row" data-filename="' . $filename . '" data-name="' . $name . '">';
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
    <input type="text" id="form_filename"   name="filename" hidden>
    <input type="text" id="form_name"       name="name"     hidden>
    <input type="text" id="form_action"     name="action"   hidden>
</form>
</body>
</html>
