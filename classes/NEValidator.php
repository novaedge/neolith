<?php
/**
 * Defines the Validator class used to validate data
 * @package neolith
 * @version $Id: NEValidator.php 3970 2008-02-07 03:39:54Z pancoast $
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

define('VALIDATOR_EMAIL', 'This value must be a valid email address');
define('VALIDATOR_NUMBER', 'This value must be a valid number');
define('VALIDATOR_DATE', 'This value must be a valid date (YYYY-MM-DD)');
define('VALIDATOR_REQUIRED', 'This value is required');
define('VALIDATOR_CURRENCY_AMOUNT', 'This value must be a valid dollar amount');
define('VALIDATOR_LENGTH', 'This value exceeds the max length allowed: ');
define('VALIDATOR_LATIN', 'This value cannot have non-ASCII character');
define('VALIDATOR_PHONE', 'This value must be a valid phone');

// tbd: add a generic "invalid value" and a REGEXP type?  -- or allow validator caller to specify the text

/**
 * Defines the Validator class used to validate data
 * @package neolith
 */
class NEValidator extends NEHtmlRequestObject
{
  var $validSet = null;
  var $isValid = true;
    
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
   
  /**
   * check the value of the field to see if it is an email address
   * @param string $name the name of the field to check  
   */
  function isEmail($name)
  {
    $this->validSet[] = array('name' => $name, 'type' => 'email');
  }

  /**
   * check the value of the field to see if it has non-ASCII character
   * @param string $name the name of the field to check
  */
  function isLatin($name)
  {
    $this->validSet[] = array(
                              'name' => $name,
                              'type' => 'latin');
  }

  /**
   * check the value of the field to see if it exceeds the length limit
   * @param string $name the name of the field to check
  */
  function isString($name,$len)
  {
    $this->validSet[] = array(
                              'name' => $name,
                              'type' => 'length',
                              'max_len' => $len);
  }
 
  /**
   * check the concatenated value of the fields to see if it exceeds the length limit
   * @param string $name1, $name2 the names of the fields to check
  */
  function isConString($name1,$name2,$con_fields,$len)
  {
    $this->validSet[] = array(
                              'name1' => $name1,
                              'name2' => $name2,
                  			      'con_fields' => $con_fields,
                              'type' => 'con_length',
                              'max_len' => $len);
  }

  /**
   * check the value of the field to see if it is a phone number, only allow digits, '-', '/','(',')','*',' ',',','.'
   * @param string $name the name of the field to check
   */
  function isPhone($name)
  {
    $this->validSet[] = array('name' => $name, 'type' => 'phone');
  }

 
  /**
   * check the value of the field to see if it is a number
   * @param string $name the name of the field to check  
   */
  function isNumber($name)
  {
    $this->validSet[] = array('name' => $name, 'type' => 'number');
  }

  function isCurrencyAmount($name)
  {
  	$this->validSet[] = array('name' => $name, 'type' => 'currencyAmount');
  }

  /**
   * check the value of the field to see if it is a date
   * @param string $name the name of the field to check  
   */
  function isDate($name)
  {
    $this->validSet[] = array('name' => $name, 'type' => 'date');
  }
  
  /**
   * check the value of the field to see if it is required
   * @param string $name the name of the field to check  
   */
  function isRequired($name)
  {
    $this->validSet[] = array('name' => $name, 'type' => 'required');
  }
   
  /**
   * check the value of the field to see if if passes password criteria
   * @param string $name the name of the field to check  
   * @param string $minChars minimum allowed characters
   * @param string $minAlphas minimum allowed alpha characters  
   * @param string $minDigits minimum allowed digit characters  
   * @param string $minCaps minimum allowed capital 
   * TODO: add sequence checker to disallow sequence passwords (i.e., abcd1234)
   */
  function isPassword($name, $minChars, $minAlphas, $minDigits, $minCaps)
  {
    $this->validSet[] = array(
                              'name' => $name, 
                              'type' => 'password', 
                              'minChars' => $minChars, 
                              'minAlphas' => $minAlphas, 
                              'minDigits' => $minDigits, 
                              'minCaps' => $minCaps );
  }
  
  /**
   * return the current validation state
   * @return boolean returns false if any element misses validation, otherwise true  
   */
  function isValid()
  {    
    return $this->isValid;    
  }
  
  /**
   * run the validation for the set
   * @return boolean returns false if any element misses validation, otherwise true  
   */
  function validate()
  {
    //examine the validSet
    if ( $this->validSet )
    {
      foreach($this->validSet as $data)
      {
        switch($data['type'])
        {
          case 'email':
            $this->checkIsEmail($data);
            break;
          case 'number':
            $this->checkIsNumber($data);
            break;
          case 'date':
            $this->checkIsDate($data);
            break;
          case 'required':          
            $this->checkIsRequired($data);
            break;
          case 'selected':
            $this->checkIsSelected($data);
            break;
          case 'futureDate':
            $this->checkIsFutureDate($data);
            break;
          case 'currencyAmount':
            $this->checkIsCurrencyAmount($data);
            break;
          case 'password':
            $this->checkIsPassword($data);
            break;
        	case 'length':
        	  $this->checkLength($data);
        	  break;
        	case 'con_length';
        	  $this->checkConLength($data);
        	  break;
        	case 'latin';
        	  $this->checkLatin($data);
        	  break;
        	case 'phone';
        	  $this->checkIsPhone($data);
            break;
          default:
            // needed for extensibility and to add new validation types in subclasses 
            $this->checkOther($data);
            break;
        }
      }
    }
    return $this->isValid();
  }

  function annotateField( $fieldName, $annotation )
  {
    // does NOT fail validation (i.e., set isValid = false)
    $this->addError($annotation, $fieldName);
  }

  function failField( $fieldName, $error )
  {
    $this->isValid = false;
    $this->annotateField( $fieldName, $error );
  }

  /**
   * check if the value is a valid email address
   * 
   * using the regex from http://us2.php.net/manual/en/function.eregi.php#52458
   * @param array $data a validSet array element
   */
  function checkIsEmail($data)
  {
    //from http://us2.php.net/manual/en/function.eregi.php#52458
    // GSK: added 0-9 as allowable first chars in domain
    $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';    // allowed characters for part before "at" character
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // allowed characters for part after "at" character

    $regex = '^' . $atom . '+' .        // One or more atom characters.
    '(\.' . $atom . '+)*'.              // Followed by zero or more dot separated sets of one or more atom characters.
    '@'.                                // Followed by an "at" character.
    '(' . $domain . '{1,63}\.)+'.        // Followed by one or max 63 domain characters (dot separated).
    $domain . '{2,63}'.                  // Must be followed by one set consisting a period of two
    '$'; 
    
    if ($email = $this->get($data['name']))
    {
      if (!eregi($regex, $email))
      {
        $this->isValid = false;
        $this->addError(VALIDATOR_EMAIL, $data['name']);
      }
    }
  }
  
  function checkIsPhone($data)
  {
    if ($phone = $this->get($data['name'])) 
    {
      $replace = array(' ','-','/','(',')',',','.','*','+','x','X');
      $clean_phone = str_replace($replace, '', $phone);    

      if (!is_numeric($clean_phone))
      {
        $this->isValid = false;
        $this->addError(VALIDATOR_PHONE, $data['name']);
      }
    }
  }

  /**
   * check if the value is a valid number
   * @param array $data a validSet array element
   */
  function checkIsNumber($data)
  {    
    if ($number = $this->getNumber($data['name']))
    {
      if (!is_numeric($number))
      {        
        $this->isValid = false;
        $this->addError(VALIDATOR_NUMBER, $data['name']);
      }
    }
  }

  function checkIsCurrencyAmount($data)
  {
	$number = $this->getCurrencyAmount( $data['name'] );
	if ( !is_numeric( $number ) )
	{
	  $this->isValid = false;
	  $this->addError(VALIDATOR_CURRENCY_AMOUNT, $data['name']);
	}
  }
  
  /**
   * check if the value is a valid date
   * checkIfDate code came from http://www.php.net/manual/en/function.checkdate.php#54611
   * @param array $data a validSet array element
   */
  function checkIsDate($data)
  {    
    if (isset($data['name']))
    {
      if ($eDate = $this->get($data['name']))
      {
        // see comments below
        if (strtotime($eDate) === false)
        {
          $this->isValid = false;
          $this->addError(VALIDATOR_DATE, $data['name']);
        }
    
        //this is a safe transformation for MOST valid date formats.
        ///TODO: need to figure out how to support the INVALID formats.
        $date = strftime('%Y-%m-%d', strtotime($eDate));
        // tbd: support more date formats, esp for non-US sites, or end-user input (e.g., "apr 21, 2005", "5/1/07", "31.1.07")
        if (eregi("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $date)) 
        {
          $date_arr = explode('-', $date);
          if ($date_arr[0]>=1000 && $date_arr[0]<=9999) 
          {
            if (checkdate($date_arr[1], $date_arr[2], $date_arr[0])) 
            {
              unset($date_arr);
              return true;
            }
            else 
            {
              unset($date_arr);
              $this->isValid = false;
              $this->addError(VALIDATOR_DATE, $data['name']);
            }
          }
          else 
          {
            unset($date_arr);
            $this->isValid = false;
            $this->addError(VALIDATOR_DATE, $data['name']);
          }
        }
        else
        {
          $this->isValid = false;
          $this->addError(VALIDATOR_DATE, $data['name']);
        }
      }
    }
  }
  
  function checkLatin($data)
  {
    if (!(mb_detect_encoding($this->get($data['name']), 'ASCII', true)))
    {
      $this->isValid = false;      
      $this->addError(VALIDATOR_LATIN,$data['name']);
    }
  }

  function checkLength($data)
  {
    if (strlen($this->get($data['name'])) > $data['max_len'])
    {
      $this->isValid = false;
      $this->addError(VALIDATOR_LENGTH . $data['max_len'] . " characters",$data['name']);
    }
  }
 
  function checkConLength($data)
  {
    if ((strlen($this->get($data['name1'])) + strlen($this->get($data['name2'])) + 1) > $data['max_len'])
    {
      $this->isValid = false;
      $this->addError($data['con_fields'] . " exceeds the max length allowed: " . $data['max_len'] . " characters",$data['name1']);
      $this->addError($data['con_fields'] . " exceeds the max length allowed: " . $data['max_len'] . " characters",$data['name2']);
    }
  }
 
  function checkIsRequired($data)
  {
    // check strlen of field for requirement check
    if (strlen($this->get($data['name'])) == 0)
    {
      $this->isValid = false;
      $this->addError(VALIDATOR_REQUIRED, $data['name']);
    } 
  }
  
  function checkIsFutureData($data)
  {
  	
  }

  function checkIsPassword($data)
  {
    if ($data)
    {
      $name = $data['name'];
      $minChars = $data['minChars'];
      $minAlphas = $data['minAlphas'];
      $minDigits = $data['minDigits'];
      $minCaps = $data['minCaps'];
      
      $password = $this->get($name);

      if (strlen($password) < $minChars)
      {
        $this->isValid = false;
        $this->addError("Password must be at least $minChars characters long", $data['name']);
      }

      $digitCount = 0;
      $alphaCount = 0;
      $capsCount = 0;
      $chars = str_split($password);
      foreach($chars as $char)
      {
        // number of digits and non digits
        if (preg_match("/\d/", $char))
        {
          $digitCount ++;
        }
        elseif(preg_match("/\D/", $char))
        {
          $alphaCount ++;
        }
        
        // number of capitals
        if (preg_match("/[A-Z]/", $char))
        {
          $capsCount ++;
        }
      }

      if ($alphaCount + $capsCount < $minAlphas)  // caps count as alpha
      {
        $this->isValid = false;
        $this->addError("Password must contain at least $minAlphas letters", $data['name']);
      }
      if ($digitCount < $minDigits)
      {
        $this->isValid = false;
        $this->addError("Password must contain at least $minDigits numbers", $data['name']);
      }
      if ($capsCount < $minCaps)
      {
        $this->isValid = false;
        $this->addError("Password must contain at least $minCaps capital letters", $data['name']);
      }
    }
    else
    {
      $this->isValid = false;
    }
  }
  
  function checkOther()
  {
  	// probably best left unimplemented
  	// (smalltalk: self shouldNotImplement)
  }
  
  function resetValidSet()
  {
    $this->validSet = null;
  }
  
}
?>
