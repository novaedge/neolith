<?php
/**
 * Defines the class to manage user logins
 * @package neolith
 * @version $Id: NELogin.php 2488 2007-05-15 00:00:39Z gkrupa $
 * @author NovaEdge Technologies LLC
 * @copyright Copyright &copy; 2006-2007, NovaEdge Technologies LLC
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 */

include_once('NEObject.php');

/**
 * Defines the class to manage user logins
 * @package neolith
 */
class NELogin extends NEObject
{
  var $loginStatus = false;
  var $db = null;
    
  function __construct(&$db)
  {
    parent::__construct();
    //start the session
    session_start();
    //assign the db
    $this->db = $db;
    $this->setConfig($this->db->getConfig());
  }
  
  /**
   * returns the current login status
   * @return boolean true or false
   */
  function getLoginStatus()
  {
    return $this->loginStatus;
  }
  
  /**
   * validate the login with username and password
   * @param string $username the username to validate
   * @param string $password the password to validate
   * @return boolean true or false
   */
  function validateLogin($username, $password)
  {
    //get the user table
    $user = $this->db->createTable($this->getConfig()->get('loginAuthTable'));
    
    //get the user data by username
    if ($user->getDataForKey($username, $this->getConfig()->get('loginAuthUserField')) == false)
    {
      $this->addError($user->getError(), 'loginError');
      return false;
    }
    
    //check if user is active
    if ($user->get('status') != 'A')
    {
      $this->addError('The user is no longer active', 'loginError');
      return false;
    }
    
    //execute the session login
    if (!$this->sessionLogin($username, $password, $user->get($this->getConfig()->get('loginAuthPasswordField')), $user->get($this->getConfig()->get('loginAuthKeyField'))))
    {
      $this->addError($this->getError(), 'loginError');
      return false;
    }  
    
    //reload the user data into the session
    if ($this->reloadUserData() == false)
    {
      return false;
    }

    //everything is good... return true!
    $this->loginStatus = true;
    return true;
  }
  
  /**
   * reload the user data into the session
   * @return boolean true or false representing success or failure
   */
  function reloadUserData()
  {
    //check if the session user id is set
    if ($_SESSION['login_id'] == null)
    {
      $this->addError('Invalid user ID', 'reloadUserError');
      return false;
    }
    
    //get the user id from the session
    $id = $_SESSION['login_id'];
    
    //get the user table
    $user = $this->db->createTable($this->getConfig()->get('loginAuthTable'));
    
    //get the user data by session id
    if ($user->getDataForKey($id) == false)
    {
      $this->addError($user->getError(), 'reloadUserError');
      return false;
    }
        
    $this->setSession('userName', $user->get($this->getConfig()->get('loginAuthFirstNameField')).' '.$user->get($this->getConfig()->get('loginAuthLastNameField')));
    $this->setSession('userData', $user->getVarSet());

    return true;
  }
  
  /**
   * get an attribute of the user data from the session
   * @param string $key the columnn key from the user db table whose data you want
   * @return mixed the value matching the key
   */
  function getUserData($key)
  {
    $userData = $this->getSession('userData');
    return $userData[$key];
  }
  
  /**
   * check the status of the login in the session
   * @return boolean true or false; whether the user is logged in or not
   */
  function checkLogin()
  {    
    $this->loginStatus = $this->checkSessionLogin();
    return $this->loginStatus;
  }
 
  /**
   * log the user out
   */
  function logout()
  {
    $this->sessionLogout();
    $this->loginStatus = $this->checkSessionLogin();
  }

  /**
   * perform a session logout
   */
	function sessionLogout()
	{
    session_destroy();
    session_start();
	}
  
  /**
   * check the session login
   * @return boolean true or false; whether the user is logged in or not
   */
	function checkSessionLogin()
	{
	  $isLoggedIn = false;
		if (isset($_SESSION['login_username']) && isset($_SESSION['login_password']))
		{         
			if ($this->getSessionPassword($_SESSION['login_username']) == $_SESSION['login_password'])
			{
				$isLoggedIn = true;
			}
		}
	  return $isLoggedIn;
	}
	
	function setSessionLogin($username, $id)
	{
  	$_SESSION['login_username'] = $username;
		$_SESSION['login_password'] = $this->getSessionPassword($username);
    $_SESSION['login_id'] = $id;         
	}
	
	function sessionLogin($username, $password, $hash, $id)
	{
		if ($this->getPassword($password) == $hash)
		{
			$this->setSessionLogin($username, $id);     
			return true;
		}
		else
		{
      	  $this->addError("The password for '$username' is invalid.");
		  return false;
		}
	}
  
  function getSessionUsername()
  {
    return $_SESSION['login_username'];
  }

  function getSessionUserID()
  {    
    return $_SESSION['login_id'];
  }
	
	function getSessionPassword($password)
	{
		$string = md5($_SERVER["SERVER_NAME"].$password.session_id());
		return $string;
	}
	
	function getPassword($password)
	{
		$string = md5($password);
		return $string;
	}
  
  function setSession($key, $value)
  {
    $_SESSION[$key] = $value;
  }
  
  function getSession($key)
  {
    return $_SESSION[$key];
  }

}
?>