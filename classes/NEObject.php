<?php
/**
 * Defines the NEObject base class that is the parent of all neolith classes
 * @package neolith
 * @version $Id: NEObject.php 3054 2007-07-25 23:22:59Z gkrupa $
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

define('OBJECT_VARIABLE_HTML', 'OBJECT_VARIABLE_HTML');
define('OBJECT_VARIABLE_SQL', 'OBJECT_VARIABLE_SQL');
define('OBJECT_VARIABLE_NONE', 'OBJECT_VARIABLE_NONE');
define('OBJECT_VARIABLE_NUMBER', 'OBJECT_VARIABLE_NUMBER');
define('OBJECT_VARIABLE_DATE', 'OBJECT_VARIABLE_DATE');
define('OBJECT_VARIABLE_CURRENCY_AMOUNT', 'OBJECT_VARIABLE_CURRENCY_AMOUNT');

/**
 * Defines the NEObject base class that is the parent of all neolith classes
 * @package neolith
 */
class NEObject
{
  var $errorSet = null;
  var $debugSet = null;
  var $varSet = null;
  var $config = null;
  
  /**
   * the class constructor
   */
  function __construct()
  {
  }
  
  /**
   * set the configuration object which must be an instance of NEConfig or inherited from NEConfig
   * @param object $config the configuration object
   */
  function setConfig(&$config = null)
  {
    //TODO: check if the config object is an instance of NEConfig
    if (is_object($config) == false)
    {
      //assign the config as NEConfig by default
      $this->config = new NEConfig();
    }
    else
    {
      $this->config = $config;
    }  
  }
  
  /**
   * get the configuration object
   * @param object the configuration object
   */
  function getConfig()
  {
    return $this->config;
  }
    
  function getError($key = null)
  {
    if ($key)
    {
      return $this->errorSet[$key];
    }
    else
    {
      return $this->errorSet;
    }
  }
  
  function addError($string, $key = null)
  {
    if ($key)
    {
      $this->errorSet[$key] = $string;
    }
    else
    {
      $this->errorSet[] = $string;
    }
  }
  
  function setErrorSet(&$errorSet)
  {
    $this->errorSet = $errorSet;
  }
  
  function getErrorSet()
  {
    return $this->errorSet;
  }
  
  function bug($string, $key = null)
  {
    if ($key)
    {
      $this->debugSet[$key] = $string;
    }
    else
    {
      $this->debugSet[] = $string;
    }
  }

  function getDebugBasic()
  {
    return $this->debugSet;
  }

  function getDebug()
  {
    if ( $this->getConfig()->get('debug') == true )
    {
      return $this->getDebugBasic();
    }
    else
    {
      return null;
    }
  }
  
  /**
    * Outputs the given string to the httpd error_log if TraceEnabled has been set.
    * @param string $string the string to output
    * @param addDate boolean added this since error_log already outputs date to log.
    * defaults to true for backward compatibility
    */
  function trace($string, $addDate = true)
  {
    if ($this->getConfig()->get('TraceEnabled') == 'TRUE')
    {
      if ($addDate == true)
      {
        error_log(date('m-d H-i-s') . ": " .$string);
      }
      else
      {
        error_log($string);
      }
    }
  }
 
  function traceString($file = null, $line = null, $method = null, $info = null)
  {
    $file = ($file != null) ? $file : 'unknown file';
    $method = ($method != null) ? $method : 'unknown method';
    $line = ($line != null) ? $line : 'unknown line';

    $file = preg_replace("/^.*\/(.*?$)/", "$1", $file);

    $str = "[$file $line $method] SESSIONID: ".session_id()." CONTROLLER: ".$this->get('action')."  INFO: $info";
    
    $this->trace($str, false);
  }
  
  /**
   * @todo keep from clobbering key names that are the same
   */
  function addDebug($debugSet)
  {
    //TODO: keep from clobbering key names that are the same
    if (is_array($debugSet)) {
	    $this->debugSet = array_merge($debugSet);
    }
  }
  
  function setVarSet(&$varSet = null)
  {
    if ($varSet)
    {
      $this->varSet = $varSet;
    }
    else
    {
      $this->varSet = array();
    }
  }

  function getVarSet()
  {
    return $this->varSet;
  }

  function set($key, $value)
  {
    $this->varSet[$key] = $value;
  }

  /**
   * returns just the "number" portion of a string representing a monetary amount
   * currently only handles dollars ("$xxx")
   */
  function currencyAmountFromStr($str)
  {
	// strip all sorts of chars -- a weak algorithm but should work for most cases
	// purposely leaves '.' in the string, in case we're using cents
	$parts = preg_split('/[,\s\$]/', $str );
	$whole = join("", $parts);
	
	return $whole;
  }
   
  function get($key, $encoding = null)
  {
    $value = null;
    if (isset($this->varSet[$key]))
    {
      //handle the variable encoding
      switch ($encoding)
      {
        case OBJECT_VARIABLE_DATE:
          $value = str_replace('/', '-', $this->varSet[$key]); 
          break;
        case OBJECT_VARIABLE_NUMBER:
          $value = str_replace(',', null, $this->varSet[$key]); 
          break;
        case OBJECT_VARIABLE_HTML:
          $value = htmlentities($this->varSet[$key]); 
          break;
        case OBJECT_VARIABLE_SQL:
          $value = mysql_real_escape_string($this->varSet[$key]); 
          break;
        case OBJECT_VARIABLE_CURRENCY_AMOUNT:
          $value = NEObject::currencyAmountFromStr($this->varSet[$key]);
          break;
        case OBJECT_VARIABLE_NONE:
        default:
          $value = $this->varSet[$key]; 
          break;
      }
    }
    return $value;
  }

  function getHtmlEncoded($key)
  {
    return $this->get($key, OBJECT_VARIABLE_HTML);
  }
  
  function getSqlEncoded($key)
  {
    return $this->get($key, OBJECT_VARIABLE_SQL);
  }
  
  function getNumber($key)
  {
    return $this->get($key, OBJECT_VARIABLE_NUMBER);
  }
  
  function getCurrencyAmount($key)
  {
  	return $this->get($key, OBJECT_VARIABLE_CURRENCY_AMOUNT);
  }
  
  function getDate($key)
  {
    return $this->get($key, OBJECT_VARIABLE_DATE);
  }
  
  /**
   * generates global unique identification
   */
  function guid()
  {
    return sprintf('%c%d%c%d%c-%d%c%d%c%d',
       mt_rand(65, 90), mt_rand(0, 9), mt_rand(65, 90), mt_rand(0, 9), mt_rand(65, 90), 
       mt_rand(0, 9), mt_rand(65, 90),  mt_rand(0, 9), mt_rand(65, 90), mt_rand(0, 9));  	
  }

  /**
   * truncates a string to the last space in a desired length and adds an elipsis
   * @param string $string the input string to truncate
   * @param int $stringLength the desired length of the string
   * @param string $trailingString the trailing string to append to the resulting truncated string
   * @return string the truncated string or the full string if its length is <= $stringLength  
   */
  function truncateString($string, $stringLength, $trailingString = '...')
  {
    //return the full string if it is <= desired length
    if (strlen($string) <= $stringLength)
    {
      return $string;
    }
    
    //pad the length by three to handle a trailing elipsis (...)
    $padLength = $stringLength-strlen($trailingString);
    
    //truncate the string to the desired length
    $subStr = substr($string, 0, $padLength);

    //find the last space
    $lastSpacePos = strrpos($subStr, ' ');
    
    //tuncate to the last space
    $subStr = substr($subStr, 0, $lastSpacePos);
    
    //add the elipsis to the string
    $subStr .= $trailingString;
    
    return $subStr;
  }

	/**
	 * returns a string with any non-numeric [0-9] digits removed
	 * useful for credit card numbers, phone numbers, etc.
	 * be careful with extended ZIP codes (e.g., 90048-0312 => 900480312)
	 */
	function stripNonDigits($str)
	{
		return preg_replace( '/([^0-9]+)/', '', $str );
	}

}
?>
