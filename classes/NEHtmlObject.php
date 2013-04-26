<?php
/**
 * Defines the HtmlObject class to build HTML objects
 * @package neolith
 * @version $Id: NEHtmlObject.php 2205 2007-03-26 19:18:22Z jreed $
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
 * Defines the HtmlObject class to build HTML objects
 * @package neolith
 */
class NEHtmlObject extends NEObject
{
  var $htmlClass = null;
  var $htmlId = null;
  
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
   
  /**
   * set the HTML class
   * 
   * @param string $htmlClass description  
   */
  function setClass($htmlClass)
  {
    $this->htmlClass = $htmlClass;
  }

  /**
   * get the HTML class
   * 
   * @return string the HTML class
   */
  function getClass()
  {
    return $this->htmlClass;
  }

  function setId($htmlId)
  {
    $this->htmlId = $htmlId;
  }

  /**
   * get the HTML ID
   * 
   * @return string the HTML ID
   */
  function getId()
  {
    return $this->htmlId;
  }
  
  /**
   * returns a camelCase string that appends $name to the existing class
   * 
   * @param string $name the lower case sub name that will be appended to the class name
   * @return $string the camelCase sub class name
   */
  function getSubClass($name)
  {
    if ($this->getClass())
    {
      $subClass = $this->getClass().strtoupper($name{0}).substr($name, 1);
    }
    else 
    {
      $subClass = $name;
    }
    return $subClass;
  }

}
?>