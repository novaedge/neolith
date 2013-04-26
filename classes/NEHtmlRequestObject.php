<?php
/**
 * Defines the HtmlRequestObject class for objects that need to handle HTML requests
 * @package neolith
 * @version $Id: NEHtmlRequestObject.php 2205 2007-03-26 19:18:22Z jreed $
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

include_once('NEHtmlObject.php');

/**
 * Defines the HtmlRequestObject class for objects that need to handle HTML requests
 * @package neolith
 */
class NEHtmlRequestObject extends NEHtmlObject
{
  var $pageValidator = null;

  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
   
  /**
   * sets the page request variable set in this class
   * @param array &$pageRequest an array containing the page request variables
   */
  function setPageRequest(&$pageRequest = null)
  {
    if ($pageRequest)
    {
      $this->setVarSet($pageRequest);
    }
  }

  /**
   * returns the internal page request array
   * @return array an array containing the variables of the page request
   */
  function getPageRequest()
  {
    return $this->getVarSet();
  }
  
  /**
   * resets the internal page request array
   */
  function resetPageRequest()
  {
    $this->setVarSet();
  }

  /**
   * sets the internal page validator object
   * @param Validator &$object a reference to a Validator object
   */
  function setPageValidator(&$object)
  {
    $this->pageValidator = $object;
  }
  
  /**
   * returns the internal page validator object
   */
  function getPageValidator()
  {
    return $this->pageValidator;
  }

}
?>