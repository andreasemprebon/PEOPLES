<?php

define('kDBName', 		'peoples');
define('kDBUser', 		'root');
define('kDBPassword', 	'root');
define('kDBHost', 		'localhost');

class Database
{
    private $db_name;
    private $db_user;
    private $db_password;
    private $db_host;

    /**
     *	@var Mysqli
     */
    public $db_conn;

    public function __construct($db_name = kDBName, $db_user = kDBUser, $db_password = kDBPassword, $db_host = kDBHost)
    {
        $this->db_name 		= $db_name;
        $this->db_user 		= $db_user;
        $this->db_password 	= $db_password;
        $this->db_host 		= $db_host;
        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     *	Prova a connettersi al db
     *	@return bool
     */
    public function connect()
    {
        $this->db_conn = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);
        if ($this->db_conn->connect_error) {
            return false;
        }
        return true;

    }

    /**
     *	Si disconnette dal Database
     */
    public function disconnect()
    {
        if ($this->db_conn) {
            $this->db_conn->close();
        }
    }



}

?>