<?php
/**
 * Defines a class to store configuration values for neolith projects
 * @package neolith
 * @version $Id: NEConfig.php 3869 2008-01-11 23:18:16Z jreed $
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

require_once('NEObject.php');

/**
 * Defines a class to store configuration values for neolith projects
 * @package neolith
 */
class NEConfig extends NEObject
{
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
    
    //database settings
    $this->set('dbUsername','');
    $this->set('dbPassword','');
    $this->set('dbDatabase','');
    $this->set('dbHost','localhost');
    $this->set('dbInUse', true);
    
    //login settings
    $this->set('loginPage','login.php');
    $this->set('logoutPage','index.php');
    $this->set('loginNoAccessPage','home.php');
    //login auth table specs
    $this->set('loginAuthTable','user');    
    $this->set('loginAuthUserField','username');    
    $this->set('loginAuthPasswordField','password');    
    $this->set('loginAuthKeyField','user_id');    
    $this->set('loginAuthFirstNameField','firstname');    
    $this->set('loginAuthLastNameField','lastname');    
    
    $this->set('selectOptionText', 'Please pick a value');
    
    //password validation keys
    $this->set('validatorPasswordMinChars', 8);
    $this->set('validatorPasswordMinAlphas', 1);
    $this->set('validatorPasswordMinDigits', 1);
    $this->set('validatorPasswordMinCaps', 1);
    
    //SSL settings
    $this->set('loginRequiresSSL', false);
    // assume that if SSL is present that it is required
    $this->set('siteRequiresSSL', true);

		//ignore URL path to prevent URI XSS/hijack
		$this->set('ignoreUrlPath', true);
  }
  
}
?>