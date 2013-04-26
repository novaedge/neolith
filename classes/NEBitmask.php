<?php
/**
 * Defines the class to handle bitmasking
 * @package neolith
 * @version $Id: NEBitmask.php 3524 2007-09-20 22:54:11Z gkrupa $
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
 * Defines the class to handle bitmasking
 * @package neolith
 */
class NEBitmask extends NEObject
{
  /**
   * the class constructor
   */
 	function __construct()
  {
    parent::__construct();
  }
   
  /**
   * generates a bitmask based off an array of values
   * @param array $values data to be masked
   * @return int bitmask
   */ 
  function mask($values)
  {               
    if ($values == null)
    {
      return false;
    }

    foreach ($values as $row)
    {
      
      $row = pow(2, $row);
      $mask += $row;
    }
  
    return $mask;  	
  }
  
  /**
   * returns an array of values from the mask
   * @param int $count total number of possible values in dataset
   * @param int $mask bitmask
   * @return array values that are in bitmask
   */ 
  function unmask($count, $mask)
  {
    $selectedValues = array();

    if ($count == null)
    {
    	  return false;
    }

    for($i = 0; $i < $count; $i++)
    {
	    //checks to see which values are included in the bitmask
  	  if (($mask & pow(2, $i)) != 0)
      {
        $selectedValues[] = $i;          
      }
    }

    return $selectedValues;     	
  }    
}
?>