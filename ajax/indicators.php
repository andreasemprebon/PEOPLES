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

$methodCallback = array(	'GET'		=> 'listaClientiCompleta',
                            'POST' 		=> 'aggiungiNuovoCliente',
                            'DELETE'	=> 'eliminaCliente' );

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

if ( !isset($reqVars['filename']) ) {
    die( json_encode($arrayReturn) );
}

$nome           = isset($reqVars['name']) ? ucwords($reqVars['name']) : "nome_file";
$filename       = '../scenario/' . $reqVars['filename'];
$dim_selected   = isset($reqVars['dim_selected']) ? intval( $reqVars['dim_selected'] ) : 1;
$dim_selected   = max($dim_selected, 1);
$dim_selected   = min($dim_selected, 7);

$file_content = array(  "name"          => $nome,
                        "lista"         => null,
                        "filename"      => $filename,
                        "dim_selected"  => $dim_selected );

if ($method == 'GET') {

    $json = file_get_contents($filename);

    if ($json === FALSE) {
        $arrayReturn['result'] = $file_content;
    } else {
        $arrayReturn['result'] = json_decode($json, true);
    }

    $arrayReturn['status'] = 0;

} else if ($method == 'POST'){

    if ( isset($reqVars['lista']) ) {
        $lista = json_decode($reqVars['lista'], true);

        $file_content["lista"] = $lista;

        $err = file_put_contents($filename, json_encode($file_content));

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