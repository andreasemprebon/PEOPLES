<?php
/**
 * Created by Andrea
 * Date: 02/05/17
 * Time: 10:18
 */

session_start();

if ( isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 ) {
    header('Location: ./index.php');
    die();
}

$show_error = false;

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

    if ( !isset($_POST['username']) || !isset($_POST['password']) ) {
        $show_error     = true;
        $error_message  = "Entrambi i campi sono obbligatori";

    } else {
        require_once( dirname(__FILE__) . '/database/DatabasePeople.php');

        $databasePeople = new DatabasePeople();

        $result = $databasePeople->registerUtente($_POST['username'], $_POST['password']);
        $show_error     = $result['error'];
        $error_message  = $result['desc'];

        if ( $result['result'] == NULL ) {
            $show_error = true;
        } else {
            $_SESSION['user_id']    = $result['result'];
            $_SESSION['username']   = $_POST['username'];
            header('Location: ./index.php');
            die();
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PEOPLES Framework - Register</title>

    <!-- INCLUDO SEMANTIC-UI E JQUERY -->
    <link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="semantic/dist/semantic.min.js"></script>


    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/register.css">

</head>
<body>
<div class="ui middle aligned centered grid maingrid">
    <div class="ui two column middle aligned centered grid">
        <div class="column">
            <h2 class="ui header component">
                <div class="content ">
                    <span class="text black">PEOPLES Framework register</span>
                </div>
            </h2>
        </div>
        <div class="four column centered row">
           <div class="center aligned column">
               <form action="./register.php" method="POST" id="form_login">
                   <?php if ($show_error): ?>
                       <div class="ui red message"><?php echo $error_message; ?></div>
                   <?php endif; ?>
                   <div class="ui fluid input">
                        <input type="text" name="username" id="username" placeholder="Username">
                    </div>
                    <div class="ui fluid input">
                        <input type="password" name="password" id="password" placeholder="Password">
                    </div>
                    <button class="ui labeled button blue btn_register" type="submit">
                        Register
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

</body>
</html>
