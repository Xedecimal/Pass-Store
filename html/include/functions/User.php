<?php

function user_hash($Password, $Username) {
	return hash('sha256', user_key($Password, $Username) . $Password);
}

function user_key($Password, $Username) {
	return hash('sha256', $Password . s($Username));
}

function user_exists($User) {
	global $pdo;

	$stmt = $pdo->prepare('
		SELECT count(*)
		FROM `users`
		WHERE `username` = :username');
	$stmt->bindParam(':username', s($User));
	$stmt->execute();
	
	return ($stmt->fetchColumn() > 0);
}

function user_create($Username, $Password) {
	global $pdo;

	if (user_exists($Username)) {
		return false;
	}

	$stmt = $pdo->prepare('
		INSERT INTO `users`
		(
			`uuid`
			, `username`
			, `password`
		) VALUES (
			uuid()
			, :username
			, :password
		)');
	$stmt->bindValue(':username', s($Username));
	$stmt->bindValue(':password', user_hash($Password, $Username));
	$stmt->execute();
	$stmt->closeCursor();
	return true;
}

function user_authenticate($Username, $Password) {
	global $pdo;

	$stmt = $pdo->prepare('
		SELECT count(*)
		FROM `users`
		WHERE `username` = :username AND
		`password` = :password
	');
	$stmt->bindValue(':username', $Username);
	$stmt->bindValue(':password', user_hash($Password, $Username));
	$stmt->execute();

	if ($stmt->fetchColumn() > 0) {
		// Some website told me it's a good idea to regenerate session ID's when a user logs in
//		session_obliterate();
//		session_start();
		$user = new User($Username, user_key($Password, $Username));
		$_SESSION['user'] = &$user;
		return true;
	} else {
		return false;
	}
}

function user_logout() {
	unset($_SESSION['user']);
}

class User {
	public $id;
	public $username;

	private $decryptionKey;
	
	function __construct($Username, $key) {
		if (!user_exists($Username)) {
			return false;
		}
		$this->username = $Username;
		$this->rehash();

		$_SESSION['user'] = &$this;

		$this->decryptionKey = $key;
	}

	function rehash() {
		global $pdo;
		
		$stmt = $pdo->prepare('
			SELECT `id`, `username`
			FROM `users`
			WHERE 
				`username` = :username
		');
		$stmt->bindParam(':username', $this->username); 
		$stmt->setFetchMode(PDO::FETCH_INTO, $this);
		$stmt->execute();
		$stmt->fetch();
	}

	function decrypt($Encrypted) {
		// Check that the private key we're using for decryption exists
		if ((is_readable(PATH . '/keys/' . $this->username . '.pem')) && (!empty($this->decryptionKey))) {
			$PrivKey = openssl_get_privatekey(file_get_contents(PATH . '/keys/' . $this->username . '.pem'), $this->decryptionKey);
			openssl_private_decrypt($Encrypted, $Decrypted, $PrivKey);
			return $Decrypted;
		}
		return false;
	}

	function encrypt($PlainText) {
		if (is_readable(PATH . 'keys/' . $this->username . '.pub')) {
			openssl_public_encrypt($PlainText, $Encrypted, file_get_contents(PATH . '/keys/' . $this->username . '.pub'));
			return $Encrypted;
		}
		return false;
	}
}

?>
