<?php
/**
 * Defines the breadcrumb class to populate in the page
 * @package neolith
 * @version $Id: NEBreadCrumb.php 2205 2007-03-26 19:18:22Z jreed $
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
 * Defines the breadcrumb class to populate in the page
 * @package neolith
 */
class NEBreadCrumb extends NEObject
{
  var $crumbSet = null;
  
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
    $this->crumbSet[] = array('text' => 'Home', 'url' => './');
  }
  
  function add($text, $url = null, $highlight = false, $id = null, $cssClass = null)
  {
    if ($url == null)
    {
      $url = $_SERVER['PHP_SELF'];
    }

    if ($id)
    {
      $this->crumbSet[$id] = array('text' => $text, 'url' => $url, 'highlight' => $highlight, 'class' => $cssClass);
    }
    else
    {
      $this->crumbSet[] = array('text' => $text, 'url' => $url, 'highlight' => $highlight, 'class' => $cssClass);
    }
  }

  function remove($id)
  { 
    unset($this->crumbSet[$id]);
  }

  function setHighlight($id)
  {
    $this->crumbSet[$id]['highlight'] = true;
  }
  
  function getBreadCrumb()
  {
    $dataString = null;    
    foreach($this->crumbSet as $key => $crumb)
    {
      // changed since breadcrumbs can have array keys now
      if ($count != 0)
      {
        $dataString .= ' &gt; ';
      }
      $dataString .= $this->getLink($crumb);
      $count ++;
    }
    return $dataString;
  }
  
  function getLink($crumb)
  {
    if ($crumb['highlight'] == true)
    {
      $class = "class=\"breadCrumbHighlight\"";
    }
    elseif ($crumb['class'] != null)
    {
      $class = "class=\"".$crumb['class']."\"";
    }
    $dataString = "<a href=\"".$crumb['url']."\" $class>".$crumb['text']."</a>";    
    return $dataString;
  }
}
?>
