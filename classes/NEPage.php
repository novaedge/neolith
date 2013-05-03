<?php
/**
 * Defines the Page class to contain the logic of an application page
 * @package neolith
 * @version $Id: NEPage.php 3869 2008-01-11 23:18:16Z jreed $
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

include_once('NEHtmlRequestObject.php');

define('NEOLITH_ADMIN_GROUP', 1);


/**
 * Defines the Page class to contain the logic of an application page
 * @package neolith
 */
class NEPage extends NEHtmlRequestObject
{
  var $pageContent = null;
  var $login = null;
  var $breadCrumb = null;
  var $db = null;
  var $templateContent = null;
  
  /**
   * the class constructor
   * @param object $config an instance of NEConfig or an object derived from NEConfig
   */
  function __construct(&$config = null)
  {
    //call the parent constructor
    parent::__construct();

    //handle the config object
    $this->setConfig($config);

		//neolith ignores all URL "path" information to prevent XSS
		if ($this->getConfig()->get('ignoreUrlPath') == true && isset($_SERVER['PATH_INFO'])) {
			$scriptAndQuery = $_SERVER['SCRIPT_NAME'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
			header("Location: $scriptAndQuery");
		  exit();
		}
  	
    //initialize the database
    if ($this->getConfig()->get('dbInUse') == true)
    {
      $this->db = new NEDb($this->getConfig());
      if (!$this->db)
      {
        $this->addError("The database is not initialized");
      }
      if ($this->db->connect() == false)
      {
        $this->setErrorSet($this->db->getErrorSet());
      }
      
      //load the login object, which is DB dependent
      $this->login = new NELogin($this->db);
    }
    
    //load the other page objects
    $this->loadPageRequest();
    
    //create the validator and load with the page request
    $this->pageValidator = new NEValidator();
    $this->pageValidator->setPageRequest($this->getPageRequest());
        
    $this->breadCrumb = new NEBreadCrumb();
    
    //create the template content object and set the default values
    $this->templateContent = new NEObject();
    $this->templateContent->set('loginStatus', false);
    $this->templateContent->set('adminStatus', false);
    $this->templateContent->set('userName', null);
  }
  
  function getDb()
  {
    if (!$this->db)
    {
      $this->addError("The database is not initialized");
    }
    return $this->db;
  }
  
  function getUserId()
  {
    return $this->login->getSessionUserID();
  }
  
  /**
   * check to see if the logged in user is an admin user
   * @return boolean true or false
   */
  function isAdminUser()
  {
    $return = false;
    //check to see if the user is an admin user
    if ($this->login->getUserData('group_id') == NEOLITH_ADMIN_GROUP)
    {
      $return = true;
    }
    return $return;
  }
  
  /**
   * check if the user id is the same as the logged in user
   * @param integer $userId the user id to check
   * @return boolean true or false
   */
  function isUserSelf($userId)
  {
    return ($userId == $this->getUserId() ? true : false);
  }
  
  function reloadUserData()
  {
    $this->login->reloadUserData();
  }

  function requireSSL()
  {
    if ( ($this->getConfig()->get('siteRequiresSSL') == true) && ($_SERVER["HTTPS"] != "on") )
    {
	  $newurl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	  header("Location: $newurl");
	  exit();
    }
  }
  
  function unrequireSSL()
  {
    if ( $_SERVER["HTTPS"] == "on" )
    {
	  $newurl = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	  header("Location: $newurl");
	  exit();
    }
  }
  
  function checkLogin()
  {
    //check the user login
    $isLoggedIn = $this->login->checkLogin();
    
    if ($isLoggedIn)
    {
      $this->templateContent->set('loginStatus', true);
      $this->templateContent->set('userName', $this->getSession('userName'));
    }

    return $isLoggedIn;
  }
  
  /**
   * check the status of the login in the session and redirect to login page if not logged in
   * @return boolean true, if the user is logged in; else redirect page and exit
   */
  function requireLogin()
  {
    if ($this->getConfig()->get('loginRequiresSSL') == true)
    {
      $this->requireSSL();
    }

    if ($this->checkLogin() == false)
    {
      $this->setSession('requestedPage', $_SERVER['REQUEST_URI']);
      $this->redirect($this->getConfig()->get('loginPage'));
    }
    $this->templateContent->set('adminStatus', $this->isAdminUser());
    return true;
  }
  
  /**
   * check the status of the login in the session and redirect to login page if not logged in or not an admin user
   * @return boolean true, if the user is logged in; else redirect page and exit
   */
  function requireAdminLogin()
  {
    $this->requireLogin();
    if ($this->isAdminUser() == false)
    {
      $this->redirect($this->getConfig()->get('loginNoAccessPage'));
    }
  }
  
  function logout()
  {
    $this->login->logout();
    if ($this->login->checkLogin() == false)
    {
      $this->redirect($this->getConfig()->get('logoutPage'));
    }
  }

  function getLogin()
  {
    return $this->login;
  }
  
  function getLoginStatus()
  {
    $loginStatus = false;
    if ($this->login)
    {
      $loginStatus = $this->login->getLoginStatus();
    }
    return $loginStatus;
  }
  
  function getSession($key)
  {
    $sessionValue = null;
    if ($this->login)
    {
      $sessionValue = $this->login->getSession($key);
    }
    return $sessionValue;
  }
  
  function setSession($key, $value)
  {
    $this->login->setSession($key, $value);
  }
  
  function getBreadCrumb()
  {
    $breadCrumb = false;
    if ($this->breadCrumb)
    {
      $breadCrumb = $this->breadCrumb->getBreadCrumb();
    }
    return $breadCrumb;
  }
  
  /**
   * add content to the page
   * @param string $string string content to add to the page
   */
  function addPageContent($string)
  {
    $this->pageContent .= $string;
  }

	/**
	 * add more content to the page's header (via template var)
	 * @param string $string string content to add to page in HEAD
	 */
	function addHeaderContent($string)
	{
		$old = $this->getTemplateVar('headerContents');
		$this->setTemplateVar('headerContents', $old.$string);
	}

	/**
	 * add/apply another style sheet to this
	 * @param string $string stylesheet name
	 */
	function addStyleSheet($string)
	{
		$this->addHeaderContent("<link rel=\"stylesheet\" href=\"{$string}\" type=\"text/css\" />");
	}

  /**
   * returns the page content string
   * @return string the content for the page
   */
  function getPageContent()
  {
    return $this->pageContent;
  }
  
  /**
   * loads the data for the page from request, post or get
   * @param array $requestData an optional array reference containing the page data, could be a superglobal defaults to $_REQUEST
   */
  function loadPageRequest(&$requestData = null)
  {
    //reset the page request
    $this->resetPageRequest();
    
    if ($requestData == null)
    {
      $requestData =& $_REQUEST;
    }
    
    if ($requestData)
    {
      foreach ($requestData as $key => $value)
      {
        $this->set($key, $value);
      }
    }
  }
  
  /**
   * redirect the request to a new page
   * @param string $page the new page to be redirected to
   */
  function redirect($page)
  {
    //redirect to a new page that is not this page, to prevent recursion
    if (!preg_match("@$page@", $_SERVER['PHP_SELF']))
    {
      //we are not logged in
      header("Location: $page");
      exit();
    }
  }

  /**
   * redirect to a pages controller
   * @param string $controllerAction the action for the controller
   * @param string $controllerName the name of the controller (default 'action')
   * @paran string $page the page to redirect to (default $_SERVER['SERVER_NAME'])
   * @param string $queryString query string to pass in addition (must be url_encode'd first)
   */
  function redirectCtrl($controllerAction, $controllerName = "action", $page = null, $queryString = null)
  {
    if ($page == null)
    {
      $page = $_SERVER['SCRIPT_NAME'];
    }

    $queryString = ($queryString && $queryString[0] != '&') ? '&'.$queryString : $queryString;
    $queryString = '?'.$controllerName.'='.$controllerAction.$queryString;

    $loc = $page.$queryString;
    $this->redirect($loc);
  }
  
  /**
   * get the template content object
   * @return NEObject the template content object
   */
  function getTemplateContent()
  {
    return $this->templateContent;
  }
  
  /**
   * get a variable from the template content object
   * @param string $key the variable key
   * @return mixed the value in the object matching the key
   */
  function getTemplateVar($key)
  {
    return $this->templateContent->get($key);
  }

  /**
   * set a variable in the template content object
   * @param string $key the variable key
   * @param mixed $value the value in the object matching the key
   */
  function setTemplateVar($key, $value)
  {
    $this->templateContent->set($key, $value);
  }

}
?>
