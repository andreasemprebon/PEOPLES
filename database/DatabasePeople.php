<?php

/**
 *
 *	@author Andrea Semprebon <boris.pio@hotmail.it>
 *
 */

require_once( dirname(__FILE__) . '/Database.php' );

class DatabasePeople extends Database {

    public function loginUtente($username, $password) {

        $sql = 'SELECT id, password_hash FROM users WHERE username = ? LIMIT 1';

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'     => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('s', $username);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();
        $stmt->bind_result($ID, $password_hash);

        while ($stmt->fetch()) {

            $result = password_verify($password, $password_hash);
            $stmt->close();

            if ($result) {
                return array(
                    'error'	    => false,
                    'desc'		=> '',
                    'result'	=> $ID );
            } else {
                return array(
                    'error'	    => false,
                    'desc'		=> 'La password immessa non è valida',
                    'result'	=> NULL );
            }

        }

        $stmt->close();

        return array(
            'error'	    => false,
            'desc'		=> 'Non esiste alcun utente con questo username',
            'result'	=> NULL );

    }

    public function registerUtente( $username, $password ) {

        /**
         * Se è presente già un cliente con lo stesso nominativo,
         * allora mi fermo qui e restituisco un errore
         */
        $result = $this->numeroUtentiConUsername( $username );

        if ( $result['error'] ) {
            return $result;
        }

        if ( $result['result'] > 0 ) {
            return array(
                'error'	    => false,
                'desc'		=> $result['desc'],
                'result'	=> NULL );
        }


        $sql = "INSERT INTO users (id, username, password_hash) VALUES (DEFAULT, ?, ?)";

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'	    => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $pwd_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param('ss', $username, $pwd_hash);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $insert_id = $stmt->insert_id;

        if ( $insert_id <= 0 ) {
            return array(
                'error' 	=> true,
                'desc'		=> 'Username NON è stato aggiunto al database',
                'result'	=> NULL );
        }

        return array(
            'error'	    => false,
            'desc'		=> "User correctly registered",
            'result'	=> $insert_id );
    }

    public function numeroUtentiConUsername( $username ) {

        $sql = 'SELECT id FROM users WHERE username = ?';

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'	    => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error . ' ' . $sql,
                'result'	=> NULL );
        }

        $stmt->bind_param('s', $username);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error' 	=> true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();
        $numRows = $stmt->num_rows;

        if ( $numRows > 0 ) {
            return array(
                'error'	    => false,
                'desc'		=> 'Un utente con lo stesso username è già presente nel database.',
                'result'	=> $numRows );
        }

        return array(
            'error'	    => false,
            'desc'		=> '',
            'result'	=> 0 );
    }

    public function scenariPerUtente($user_id) {

        $sql = 'SELECT id, name FROM scenario WHERE user_id = ?';

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'     => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('i', $user_id);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();
        $stmt->bind_result($ID, $name);

        $scenari = array();
        while ($stmt->fetch()) {

            $scenario = array(
                'id'    => $ID,
                'name'  => $name
            );
            array_push($scenari, $scenario);
        }

        $stmt->close();

        return array(
            'error'	    => false,
            'desc'		=> '',
            'result'	=> $scenari );

    }

    public function aggiungiScenario( $name, $user_id ) {

        $sql = "INSERT INTO scenario (id, name, user_id, value) VALUES (DEFAULT, ?, ?, DEFAULT)";

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'	    => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('si', $name, $user_id);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $insert_id = $stmt->insert_id;

        if ( $insert_id <= 0 ) {
            return array(
                'error' 	=> true,
                'desc'		=> 'Scenario NON aggiunto al database',
                'result'	=> NULL );
        }

        return array(
            'error'	    => false,
            'desc'		=> "Scenario correctly added",
            'result'	=> $insert_id );
    }

    public function ottieniScenario($scenario_id, $user_id) {

        $sql = 'SELECT value FROM scenario WHERE id = ? AND user_id = ?';

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'     => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('ii', $scenario_id, $user_id);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();
        $stmt->bind_result($value);

        while ($stmt->fetch()) {

            $stmt->close();
            return array(
                'error'	    => false,
                'desc'		=> '',
                'result'	=> $value );
        }

        $stmt->close();

        return array(
            'error'	    => false,
            'desc'		=> 'Nessuno scenario associato',
            'result'	=> NULL );

    }


    public function modificaScenario($value, $scenario_id, $user_id) {

        $sql =	"UPDATE scenario SET";
        $sql .=	" value = ?";
        $sql .=	" WHERE id = ? AND user_id = ? LIMIT 1";

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'	    => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('sii',	$value, $scenario_id, $user_id);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();

        $num_rows = $stmt->affected_rows;

        return array(
            'error' 	=> false,
            'desc'		=> '',
            'result'	=> $num_rows );
    }

    public function eliminaScenario( $scenario_id, $user_id ) {
        $sql = "DELETE FROM scenario WHERE id = ? AND user_id = ? LIMIT 1";

        $stmt = $this->db_conn->prepare($sql);
        if ($stmt === false) {
            return array(
                'error'	    => true,
                'desc'		=> 'Errore: ' . $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->bind_param('ii',	$scenario_id, $user_id);

        $status = $stmt->execute();
        if ($status === false) {
            return array(
                'error'	    => true,
                'desc'		=> $this->db_conn->error,
                'result'	=> NULL );
        }

        $stmt->store_result();

        $num_rows = $stmt->affected_rows;

        return array(
            'error'	    => false,
            'desc'		=> '',
            'result'	=> $num_rows
        );

    }
}

?>