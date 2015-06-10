<?php

class VIAdminCenter
{
	private static $nav = Array();
	public static $max_art_per_page = 4;
	private static $error;
	private static $success;
	private static $database;

	public function __construct()
	{
	}

	public static function setDatabase($db) {
		self::$database = $db;
	}

	public static function addNavEntry($key, $value=NULL) {
		if(isset(self::$nav[$key]) && self::$nav[$key] != NULL)
			VILogger::log("Navigation entry for " .$key. " with value " .$value. " already exists!", VI_LOG_LEVEL_WARNING);
		if($value == NULL)
			trigger_error('You need to provide a value for the admin navigation', E_USER_ERROR);
		self::$nav[$key] = $value;
	}

	public static function getNav() {
		return self::$nav;
	}

	public static function getError() {
		if(!empty(self::$success))
			$s = '<div class="success">' . self::$success . '</div>';
		else
			$s = '';
		if(!empty(self::$error))
			$e = '<div class="error">' . self::$error . '</div>';
		else
			$e = '';
		return $s . $e;
	}

	public static function setupAdmin() {
		if(isset($_POST['newadmin']) && self::$database->getuser('*') == false)
		{
			self::addUser(true);
		}
	}

	public static function login() {
		if(isset($_POST['login']))
		{
			if(self::$database->check_password($_POST['username'], $_POST['password']))
			{
				$_SESSION['user'] = $_POST['username'];
				header('Location: ' . VIPagemap::$baseurl . '/admin');
			}
			else
			{
				self::$error = '<div class="error">Username and password do not match</div>';
			}
		}
	}

	public static function logout() {
		if(isset($_GET['nav']) && $_GET['nav'] == 'logout')
		{
			session_unset();
			header('Location: ' . VIPagemap::$baseurl);
		}
	}

	public static function isLoggedIn() {
		if(isset($_SESSION['user']))
			return true;
		return false;
	}

	public static function parseForm() {
		// Someone submitted a form. What could it be?
		if(isset($_POST['editpost']))
		{ // Someone edited a post
			self::editPost();
		}

		if(isset($_POST['addpost']))
		{
			self::addPost();
		}

		if(isset($_POST['adduser']))
		{
			self::addUser();
		}

		if(isset($_POST['profile']))
		{
			// Someone wants to edit their profile
			self::editProfile();
		}

		if(isset($_POST['media']))
		{
			self::addMedia();
		}
	}

	private static function editPost() {
		if(!empty($_POST['title']) && !empty($_POST['content']) && !empty($_GET['cat']) && !empty($_SESSION['editid']))
		{
			// All entries have been provided, let's edit the post!
			$t = $database->edit_content($_GET['cat'], $_GET['action'], $_POST['title'], $_POST['content']);

			self::$success = 'Successfully edited the post';
		}
		else
		{
			// Whoops something's been left empty...
			$list = '<ul class="errorlist">';
			$list .= makelist(getempty($_POST['title'], 'Title'));
			$list .= makelist(getempty($_POST['content'], 'Content'));
			$list .= makelist(getempty($_GET['cat'], 'Category'));
			$list .= makelist(getempty($_SESSION['editid'], 'Editid (How did you get there?)'));
			$list .= '</ul>';

			self::$error = 'Some of the required fields have not been provided';
			self::$error .= $list;
		}

		if(isset($_SESSION['editid']))
			unset($_SESSION['editid']);
	}

	private static function addPost() {
		// Someone wants to add a post
		if(!empty($_POST['title']) && !empty($_POST['content']) && !empty($_GET['cat']) && !empty($_SESSION['user']))
		{
			self::$database->set_content($_GET['cat'], $_SESSION['user'], $_POST['title'], $_POST['content']);

			self::$success = 'Successfully added the post';
		}
		else
		{
			// Whoops something's been left empty...
			$list = '<ul class="errorlist">';
			$list .= makelist(getempty($_POST['title'], 'Title'));
			$list .= makelist(getempty($_POST['content'], 'Content'));
			$list .= makelist(getempty($_GET['cat'], 'Category'));
			$list .= makelist(getempty($_SESSION['user'], 'User (How did you get there?)'));
			$list .= '</ul>';

			self::$error = 'Some of the required fields have not been provided';
			self::$error .= $list;
		}
	}

	private static function addUser($new=false) {
		if(!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['password_r']) && !empty($_POST['email']) && (!empty($_SESSION['user']) || $new))
		{
			if(strlen($_POST['password']) < 8)
			{
				self::$error = 'Password has to be at least 8 Characters long';
			}
			else
			{
				if($_POST['password'] == $_POST['password_r'])
				{
					self::$database->add_admin( $_POST['username'], $_POST['email'], $_POST['password']);
					self::$success = 'Successfully added the administrator';
				}
				else
				{
					self::$error = 'Passwords do not match';
				}
			}
		}
		else
		{
			// Whoops something's been left empty...
			$list = '<ul class="errorlist">';
			$list .= makelist(getempty($_POST['username'], 'Username'));
			$list .= makelist(getempty($_POST['password'], 'Password'));
			$list .= makelist(getempty($_POST['password_r'], 'Repeat Password'));
			$list .= makelist(getempty($_POST['email'], 'Email'));
			if(isset($_SESSION['user']))
				$list .= makelist(getempty($_SESSION['user'], 'User (How did you get there?)'));
			$list .= '</ul>';

			self::$error = 'Some of the required fields have not been provided';
			self::$error .= $list;
		}
	}

	private static function editProfile() {
		if($database->check_password($_SESSION['user'], $_POST['password_o']))
		{
			if(!empty($_POST['username']) && !empty($_POST['email']) && !empty($_SESSION['user']))
			{
				if(!empty($_POST['password']) && !empty($_POST['password_r']))
				{ // Also update password
					if($_POST['password'] == $_POST['password_r'])
					{
						if(strlen($_POST['password']) >= 8)
						{
							$result = $database->updateuser($_SESSION['user'], $_POST['username'], $_POST['email'], $_POST['password_o'], $_POST['password']);
							if($result == 1)
							{
								$_SESSION['user'] = $_POST['username'];
							}
						}
						else
							self::$error = 'The passwords has to be at least 8 characters long';
					}
					else
					{
						self::$error = 'The passwords did not match';
					}
				}
				else
				{ // Dont update password
					$result = $database->updateuser($_SESSION['user'], $_POST['username'], $_POST['email'], $_POST['password_o']);
					if($result == 1)
					{
						$_SESSION['user'] = $_POST['username'];
					}
				}
			}
			else
			{
				// Whoops something's been left empty...
				$list = '<ul class="errorlist">';
				$list .= makelist(getempty($_POST['username'], 'Username'));
				$list .= makelist(getempty($_POST['password'], 'Password'));
				$list .= makelist(getempty($_POST['password_r'], 'Repeat Password'));
				$list .= makelist(getempty($_POST['email'], 'Email'));
				$list .= makelist(getempty($_SESSION['user'], 'Session User (How did you get there?)'));
				$list .= '</ul>';

				self::$error = 'Some of the required fields have not been provided';
				self::$error .= $list;
			}
		}
		else
		{
			// Password doesnt match
			self::$error = 'You either didn\'t provide your old password, or it is wrong.';
		}
	}

	private static function addMedia() {
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["file"]["name"]);
		$extension = end($temp);

		if ((($_FILES["file"]["type"] == "image/gif")
		|| ($_FILES["file"]["type"] == "image/jpeg")
		|| ($_FILES["file"]["type"] == "image/jpg")
		|| ($_FILES["file"]["type"] == "image/pjpeg")
		|| ($_FILES["file"]["type"] == "image/x-png")
		|| ($_FILES["file"]["type"] == "image/png"))
		&& ($_FILES["file"]["size"] < 2500000)
		&& in_array($extension, $allowedExts)) {
		  if ($_FILES["file"]["error"] > 0) {
			  self::$error = "Return Code: " . $_FILES["file"]["error"];
		  } else {
			if (file_exists(VIPagemap::$basedir . "/media/" . $_FILES["file"]["name"])) {
				self::$error = $_FILES["file"]["name"] . " already exists.";
			} else {
			  move_uploaded_file($_FILES["file"]["tmp_name"],
			  VIPagemap::$basedir . "/media/" . $_FILES["file"]["name"]);
			  self::$success = "Stored in: " . VIPagemap::$basedir . "/media/" . $_FILES["file"]["name"];
			}
		  }
		} else {
			self::$error = "Invalid file";
		}
	}
}

?>
