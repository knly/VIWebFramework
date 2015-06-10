<?php

class VIDatabase {
	private $host;
	private $user;
	private $password;
	private $database;

	public $mysqli;

	function __construct($host, $user, $password, $database) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;

		$this->mysqli = new mysqli($host, $user, $password, $database);

		if ($this->mysqli->connect_error) {
			__destruct();
	    	return false;
		}
	}

	function __destruct() {
		unset($mysqli);
	}

	function bcrypt_encode ( $email, $password, $rounds='08' )
	{
	    $string = hash_hmac ( "whirlpool", str_pad ( $password, strlen ( $password ) * 4, sha1 ( $email ), STR_PAD_BOTH ), 'NEVERGONNAGIVEYOUUPNEVERGONNALETYOUDOWN', true );
	    $salt = substr ( str_shuffle ( './0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' ) , 0, 22 );
	    return crypt ( $string, '$2a$' . $rounds . '$' . $salt );
	}

	function bcrypt_check ( $email, $password, $stored )
	{
	    $string = hash_hmac ( "whirlpool", str_pad ( $password, strlen ( $password ) * 4, sha1 ( $email ), STR_PAD_BOTH ), 'NEVERGONNAGIVEYOUUPNEVERGONNALETYOUDOWN', true );
	    return crypt ( $string, substr ( $stored, 0, 30 ) ) == $stored;
	}

	function create_content_table()
	{
		$query = 'CREATE TABLE IF NOT EXISTS content (
				id INT AUTO_INCREMENT PRIMARY KEY,
				category VARCHAR(50) NOT NULL,
				date DATE NOT NULL,
				author VARCHAR(100) NOT NULL,
				title VARCHAR(200) NOT NULL,
				content text NOT NULL
			) CHARACTER SET utf8 COLLATE utf8_general_ci;';

		$result = $this->mysqli->query($query);
	}

	function create_admin_table()
	{
		$query = 'CREATE TABLE IF NOT EXISTS admin (
				id INT AUTO_INCREMENT PRIMARY KEY,
				user VARCHAR(50) NOT NULL,
				email VARCHAR(100) NOT NULL,
				regdate DATE NOT NULL,
				password VARCHAR(400) NOT NULL
			) CHARACTER SET utf8 COLLATE utf8_general_ci;';

		$result = $this->mysqli->query($query);
	}

	function get_content( $cat ) {
		$query = 'SELECT id, title, content, author, date FROM content WHERE category = "' . $cat . '" ORDER BY date DESC';

		$result = $this->mysqli->query($query);

		return $result->fetch_all();
	}

	function set_content( $cat, $author, $title, $content ) {
		$query = "INSERT INTO content(category, date, author, title, content)
					VALUES('" . $cat . "', '" . date('Y-m-d') . "', '" . $author . "', '" . $title . "', '" . $content . "')";

		$result = $this->mysqli->query($query);
		return($result);
	}

	function delete_content( $cat, $id ) {
		$query = "DELETE FROM content WHERE category='" . $cat . "' AND id='" . $id . "'";

		$result = $this->mysqli->query($query);
		return $result;
	}

	function edit_content ( $cat, $id, $title, $content ) {
		$content = $this->mysqli->real_escape_string($content);
		$query = 'UPDATE content SET title="' . $title . '", content="' . $content . '" WHERE category="' . $cat . '" AND id="' . $id . '"';

		$result = $this->mysqli->query($query);
		return($result);
	}

	function add_admin( $user, $email, $password )
	{
		$hash = $this->bcrypt_encode($email, $password, 10);

		$query = 'INSERT INTO admin(user, email, regdate, password) VALUES("' . $user . '", "' . $email . '", "' . date("Y-m-d") . '", "' . $hash . '")';
		$result = $this->mysqli->query($query);
		return($result);
	}

	function check_password( $user, $password )
	{
		$query = 'SELECT email, password FROM admin WHERE user="' . $user . '"';
		$result = $this->mysqli->query($query)->fetch_all();

		if(empty($result))
			return false;
		return $this->bcrypt_check( $result[0][0], $password, $result[0][1]);

	}

	function getuser( $user )
	{
		if($user == '*')
			$query = 'SELECT user, email, regdate FROM admin';
		else
			$query = 'SELECT user, email, regdate FROM admin WHERE user="' . $user . '"';

		$result = $this->mysqli->query($query);
		$fetch = $result->fetch_all();
		if(empty($fetch))
		{
			return false;
		}
		return $fetch[0];
	}

	function updateuser( $olduser, $newuser, $email, $oldpassword, $password = '' )
	{

		if(empty($password))
		{
			$password = $oldpassword;
		}

		$hash = $this->bcrypt_encode($email, $password, 10);
		$query = 'UPDATE admin SET user="' . $newuser . '", email="' . $email . '", password="' . $hash . '" WHERE user="' . $olduser . '"';

		$result = $this->mysqli->query($query);
		echo $query;
		return($result);
	}
}

?>
