<?php
/**
 * Created by PhpStorm.
 * User: Andrea
 * Date: 30/04/17
 * Time: 11:03
 */

$arrayReturn = array(	'status' 	=> 1,
                        'desc'		=> 'Errore sconosciuto',
                        'result' 	=> NULL );

$method = strtoupper( $_SERVER['REQUEST_METHOD'] );

$methodCallback = array( 'POST' 		=> 'save' );

/**
 *	Se il metodo non è noto, NON procedo oltre
 *	e restituisco un errore
 */
if ( !isset($methodCallback[$method]) ) {
    die( json_encode($arrayReturn) );
}

/**
 *	Se il methodo è PUT oppure DELETE,
 *	leggo le variabili direttamente dallo stream
 */
$reqVars = NULL;
$methodPutDelete = ( $method == 'PUT' || $method == 'DELETE' );

if ( $methodPutDelete ) {
    parse_str(file_get_contents("php://input"), $reqVars);
}
else if ( $method == 'POST') {
    $reqVars = $_POST;
}
else if ( $method == 'GET' ) {
    $reqVars = $_GET;
}

/**
 *	Se non sono presenti variabili mi fermo
 */
if ( !isset($reqVars) ) {
    die( json_encode($arrayReturn) );
}


if ($method == 'POST'){

    if ( isset($reqVars['lista']) ) {
        $lista = json_decode($reqVars['lista'], true);

        $err = file_put_contents("../indicators/indicators_auto.json", json_encode($lista));

        if ($err === FALSE) {
            $arrayReturn['desc'] = 'Errore durante il salvataggio del file (cod. 1)';
            die( json_encode($arrayReturn) );
        }

        $arrayReturn['desc']    = 'Salvataggio avvenuto correttamente';
        $arrayReturn['status']  = 0;
    } else {
        $arrayReturn['desc'] = 'Errore durante la gestione del file del file (cod. 2)';
    }

}

die( json_encode($arrayReturn) );

?>