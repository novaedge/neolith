<?php
/**
 * Defines the NEFile class to handle file uploads and downloads
 * @package neolith
 * @version $Id: NECrypto.php 3656 2007-10-20 19:40:59Z jreed $
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
 * Defines the NECrypto class to string and file encryption
 * @package neolith
 */
class NECrypto extends NEObject
{
  var $key = null;
  
  /**
   * the class constructor
   */
  function __construct($key)
  {
    parent::__construct();
    $this->key = $key;    
  }
   
  function encryptString($value)
  { 
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_ECB, $iv);
    return  mysql_real_escape_string($crypttext);         
  }
  
  function decryptString($value)
  {
    $value = stripslashes($value);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);    
    $value = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_ECB, $iv);
    return $value;   
  }
}
 
?>
