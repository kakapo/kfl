<?php
class Database {
	
	private $cfg;
	private $conn;
	public $error;
	
	public function connect($cfg, $charset = 'utf8') {
		$this->cfg = parse_url ( $cfg );
		$this->cfg ['charset'] = $charset;
		error_log ( var_export ( $this->cfg, true ) );
		
		//$this->conn = mysql_connect($this->cfg['host'], $this->cfg['user'], $this->cfg['pass']);
		if (false == ($this->conn = mysql_connect ( $this->cfg ['host'] . ":" . $this->cfg ['port'], $this->cfg ['user'], $this->cfg ['pass'] ))) {
			$this->error = "Can't connect to mysql server";
			return false;
		}
		
		if (isset ( $this->cfg ['path'] )) {
			mysql_select_db ( trim ( $this->cfg ['path'], '/' ), $this->conn );
		}
		
		/*
        if (!empty($this->cfg['charset'])) {
            mysql_query("SET NAMES '".($this->cfg['charset'])."'", $this->conn);
        }
*/
		return true;
	}
	
	public function query($sql) {
		$res = mysql_query ( $sql, $this->conn );
		$errno = mysql_errno ( $this->conn );
		if ($errno) {
			$this->error = mysql_error ( $this->conn );
			return false;
		}
		return is_resource ( $res ) ? mysql_num_rows ( $res ) : mysql_affected_rows ( $this->conn );
	}
	
	public function find($sql) {
		$res = mysql_query ( $sql, $this->conn );
		$errno = mysql_errno ( $this->conn );
		if ($errno) {
			$this->error = mysql_error ( $this->conn );
			return false;
		}
		$rows = array ();
		while ( ($row = mysql_fetch_assoc ( $res )) != false ) {
			$rows [] = $row;
		}
		return $rows;
	}
}
?>
