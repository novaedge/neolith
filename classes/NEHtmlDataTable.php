<?php
/**
 * Defines the HtmlDataTable class to build HTML tables from data arrays
 * @package neolith
 * @version $Id: NEHtmlDataTable.php 4102 2008-02-21 21:17:27Z jreed $
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

define('HTMLDATATABLE_FORMAT_ID', 'HTMLDATATABLE_FORMAT_ID');
define('HTMLDATATABLE_FORMAT_INT', 'HTMLDATATABLE_FORMAT_INT');
define('HTMLDATATABLE_FORMAT_FLOAT', 'HTMLDATATABLE_FORMAT_FLOAT');
define('HTMLDATATABLE_FORMAT_STRING', 'HTMLDATATABLE_FORMAT_STRING');
define('HTMLDATATABLE_FORMAT_STRING_50', 'HTMLDATATABLE_FORMAT_STRING_50');
define('HTMLDATATABLE_SORT_ASCENDING', '&nbsp;&#x25b2;');
define('HTMLDATATABLE_SORT_DESCENDING', '&nbsp;&#x25bc;');

/**
 * Defines the HtmlDataTable class to build HTML tables from data arrays
 * @package neolith
 */
class NEHtmlDataTable extends NEHtmlObject
{
  var $data = null;
  var $formatSet = null;
  var $columnSet = null;
  var $linkSet = null;
  var $imgSet = null;
  var $sortSet = null;
  var $headingSet = null;
  var $paging = false;
  var $pagingLimit = null;
  var $sqlObject = null;
  var $db = null;
  var $limit = null;

  /**
   * the class constructor
   * @param string $name sets the name/id for the data table
   */
  function __construct($name = null)
  {
    parent::__construct();

    // set the id for the data table using the given name
    if ($name)
    {
      $this->setId($name);
    }
  }
   
  /**
   * sets the page request variable set in this class
   * 
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
   * sets a link using query string on table column cells
   * 
   * @param string $id the column name that gets the link
   * @param string $key the data element that provides the value to the url param
   * @param string $param the name of the url param to use
   * @param string $url the url for the query string
   */
  function setLink($id, $key = null, $param = null, $url = null, $newWindow = false)
  {
    if ($key == null)
    {
      $key = $id;
    }

    if ($param == null)
    {
      $param = $id;
    }

    if ($url == null)
    {
      $url = $_SERVER['PHP_SELF'];
    }
    $this->linkSet[$id] = array('id' => $id, 'key' => $key, 'param' => $param, 'url' => $url, 'new_window' => $newWindow);
  }
  
  function setImage($id, $key = null, $alt = null)
  {
    if ($key == null)
    {
      $key = $id;
    }

    if ($alt == null)
    {
      $alt = '';
    }

    $this->imgSet[$id] = array('id' => $id, 'key' => $key, 'alt' => $alt);
  }
  
  /**
   * indicates which column to include in the table with the column order determined by calling order
   * 
   * @param string $id the column name to be included in the table
   * @param string $headingText heading text for the column
   */
  function setColumn($id, $headingText = null)
  {
    $this->columnSet[$id] = $id;

    if ($headingText != null)
    {
      $this->setHeading($id, $headingText);
    }
  }
  
  /**
   * sets the column heading text
   * 
   * @param string $id the column name 
   * @param string $text the column heading text 
   */
  function setHeading($id, $text)
  {
    $this->headingSet[$id] = array('id' => $id, 'text' => $text);
  }
  
  /**
   * sets the column data formatting
   * 
   * @param string $id the column name 
   * @param string $format a defined value that indicates how the data should be formatted 
   */
  function setFormat($id, $format)
  {
    $this->formatSet[$id] = array('id' => $id, 'format' => $format);
  }
  
  function setSort($id, $param = null, $url = null)
  {
    if ($param == null)
    {
      $param = $id;
    }

    if ($url == null)
    {
      $url = $_SERVER['PHP_SELF'];
    }
    $this->sortSet[$id] = array('id' => $id, 'param' => $param, 'url' => $url);
  }
  
  /**
   * turns paging on and sets the number of rows to display while paging
   * 
   * @param int $limit the number of rows to display while paging
   * 
   */
  function setPaging($limit)
  {
    $this->paging = true;
    $this->pagingLimit = $limit;
  }

  /**
   * limit the number of rows shown. intended to be used if you aren't using
   * paging but you don't want all results shown in  datatable.
   * @param int $limit the number of rows to display
   */
  function setLimit($limit)
  {
    $this->limit = $limit;
  }
  
  /**
   * sets the data to be included in the table
   * 
   * @param Sql $sqlObject a Sql class object which defines the query
   * 
   */
  function setDataFromSqlObject(&$sqlObject, &$db)
  {
    //assign the db
    $this->db = $db;
    
    //assign the internal sqlObject
    $this->sqlObject = $sqlObject;
    
    // include paging clauses in the query
    if ($this->paging)
    {
      if ( ($this->get('table_id') == $this->getId() || !$this->get('table_id')) && $this->get('page'))
      {
        $begin = ($this->get('page') - 1) * $this->pagingLimit;
      }
      else
      {
        $begin = 0;
      }
      $sqlObject->limit($begin, $this->pagingLimit);
    }
    
    //include order clauses
    if ( ($this->get('table_id') == $this->getId() || !$this->get('table_id')) && $this->get('order'))
    {
      $sqlObject->order($this->get('order').' '.$this->get('dir'), true);
    }
    
    $sqlString = $sqlObject->getSql();
    $data = $db->getDataFromSql($sqlString);
    $this->setData($data);
  }

  /**
   * sets the data to be included in the table
   * 
   * @param array $data a multidimentional array of rows of data to include in the table
   * 
   */
  function setData($data)
  {
    $this->data = $data;
  }

  /**
   * returns the HTML string for the table
   * 
   * @return string the HTML string for the table
   * 
   */
  function getTable( $pagingBelow = false )
  {
    $dataString = null;

    if (count($this->data) > 0)
    {
      //get the paging menu if needed
      if ($this->paging && !$pagingBelow)
      {
        $dataString .= $this->getPagingMenu();
      }
      
      //set the table class
      if ($this->getClass())
      {
        $dataString .= "<table class=\"".$this->getClass()."\">\n";
      }
      else
      {
        $dataString .= "<table>\n";
      }
      
      //get the table heading
      $dataString .= $this->getHeading();

      //get the columns
      $columns = $this->getColumns();
      
      //check if there is data for the table
      if ($this->data)
      {
        $rowCount = 0;
        foreach ($this->data as $rowNum => $rowData)
        {
          // stop looping rows if we reached limit
          if ($this->limit && $this->limit == $rowCount)
          {
            break;
          }

          $dataString .= "<tr class=\"".$this->evenOdd($rowNum)."\"$onClick>"; //TODO: make row clickable
          foreach ($columns as $key => $id)
          {
            //open cell
            if (isset($this->formatSet[$id]))
            {
              $dataString .= "<td ".$this->getFormatClass($this->formatSet[$id]['format']).">";
            }
            else
            {
              $dataString .= "<td>";
            }
            
            //open link
            if (isset($this->linkSet[$id]))
            {
              
              if ($this->linkSet[$id]['new_window'])
              {
                $dataString .= "<a href=\"".$this->getUrl($this->linkSet[$id]['url'], $this->linkSet[$id]['param'], $rowData[$this->linkSet[$id]['key']])."\" target=\"_blank\">";
              }
              else
              {
                $dataString .= "<a href=\"".$this->getUrl($this->linkSet[$id]['url'], $this->linkSet[$id]['param'], $rowData[$this->linkSet[$id]['key']])."\">";
              }
            }
            
            //fill cell
            if (isset($this->formatSet[$id]))
            {
              $dataString .= $this->getFormatData($rowData[$id], $this->formatSet[$id]['format']);
            }
            else if ( isset($this->imgSet[$id]))
            {
            	$imgSrc = $rowData[ $this->imgSet[$id]['key'] ];
            	$imgAlt = htmlentities( $this->imgSet[$id]['alt'] );  // no need for ENT_QUOTES b/c in double-quotes in HTML/XML below
            	if ( $imgSrc )  // do not display if no image, since IE shows broken link placeholder
            	{
	            	$dataString .= "<img src=\"{$imgSrc}\" alt=\"{$imgAlt}\">";
            	}
            }
            else
            {
              $dataString .= $this->fillIfNull($rowData[$id]);
            }

            //close cell
            if (isset($this->linkSet[$id]))
            {
              $dataString .= "</a></td>";
            }
            else
            {
              $dataString .= "</td>";
            }
  
          }
          $dataString .= "</tr>\n";

          $rowCount ++;
        }
      }
      $dataString .= "</table>\n";

      //get the paging menu if needed
      if ($this->paging && $pagingBelow)
      {
        $dataString .= $this->getPagingMenu();
      }
      
    }
    return $dataString;
  }
  
  function getHeading()
  {
    $dataString = null;

    //open the heading row
    $dataString .=  "<tr class=\"rowhead\">";

    //get the columns
    $columns = $this->getColumns();
    
    //loop through the column list
    foreach ($columns as $key => $id)
    {
      //open cell
      if (isset($this->sortSet[$id]))
      {
        //apply format class if set
        if (isset($this->formatSet[$id]))
        {
          $dataString .= "<td ".$this->getFormatClass($this->formatSet[$id]['format']).">";
        }
        else
        {
          $dataString .= "<td>";
        }
        $dataString .= "<a href=\"".$this->getSortUrl($this->sortSet[$id]['url'], $id)."\">";
      }
      else
      {
        $dataString .=  "<td>";
      }
      
      //fill cell
      if (isset($this->headingSet[$id]))
      {
        $dataString .= $this->headingSet[$id]['text'];
      }
      else
      {
        $dataString .= $id;
      }

      if (isset($this->sortSet[$id]) && $this->get('order') == $id)
      {
        if ($this->get('dir') == 'asc')
        {
          $dataString .=  HTMLDATATABLE_SORT_ASCENDING;
        }
        else if ($this->get('dir') == 'desc')
        {
          $dataString .=  HTMLDATATABLE_SORT_DESCENDING;
        }
      }
      
      //close cell
      if (isset($this->sortSet[$id]))
      {
        $dataString .= "</a></td>";
      }
      else
      {
        $dataString .= "</td>";
      }
    }

    //close the heading row
    $dataString .=  "</tr>\n";
    
    return $dataString;
  }
  
  function getColumns()
  {
    //get an array that represents the output column list
    $columns = array();
    if (isset($this->columnSet))
    {
      $columns = $this->columnSet;
    }
    else
    {
      $columns = array_keys($this->data[0]);
    }
    return $columns;
  }
  
  function getFormatClass($format)
  {
    $dataString = null;
    switch ($format)
    {
      case HTMLDATATABLE_FORMAT_ID:
          $dataString = "class=\"cellcenter\"";
        break;
      case HTMLDATATABLE_FORMAT_INT:
          $dataString = "class=\"cellright\"";
        break;
      case HTMLDATATABLE_FORMAT_FLOAT:
          $dataString = "class=\"cellright\"";
        break;
      case HTMLDATATABLE_FORMAT_STRING:
          $dataString = "class=\"cellleft\"";
        break;
      case HTMLDATATABLE_FORMAT_STRING_50:
          $dataString = "class=\"cellleft\"";
        break;
      default:
          $dataString = "class=\"$format\"";
        break;
    }
    return $dataString;
  }

  function getFormatData($value, $format)
  {
    $dataString = null;
    switch ($format)
    {
      case HTMLDATATABLE_FORMAT_ID:
          $dataString = $this->fillIfNull($value);
        break;
      case HTMLDATATABLE_FORMAT_INT:
          $dataString = number_format($value);
        break;
      case HTMLDATATABLE_FORMAT_FLOAT:
          $dataString = number_format($value, 2);
        break;
      case HTMLDATATABLE_FORMAT_STRING:
          $dataString = $this->fillIfNull($value);
        break;
      case HTMLDATATABLE_FORMAT_STRING_50:
          $dataString = substr($value, 0, 50);
        break;
      default:
          $dataString = $this->fillIfNull($value);
        break;
    }
    return $dataString;
  }

  function getUrl($url, $name, $key)
  {
    //test that the $url doesn't already contain parameters and handle accordlingly
    if (preg_match('/\?/', $url))
    {
      $returnUrl = "$url&$name=$key";
    }
    else
    {
      $returnUrl = "$url?$name=$key";
    }
    return $returnUrl;
  }
  
  function getOnClick($url, $name, $key)
  {
    $urlString = $this->getUrl($url, $name, $key);
    return "onclick=\"location.href='$urlString';\"";
  }
  
  function getSortUrl($url, $key)
  {
    if (!$this->get('dir') || ( ($this->get('table_id') == $this->getId() || !$this->get('table_id')) && $this->get('dir') == 'desc') )
    {
      $dir = 'asc';
    }
    else
    {
      $dir = 'desc';
    }

    $tableIdStr = ($this->getId()) ? 'table_id='.$this->getId().'&' : '';
    return "$url?{$tableIdStr}order=$key&dir=$dir";
  }

  function getSortOnClick($url, $key)
  {
    $urlString = $this->getSortUrl($url, $key);
    return "onclick=\"location.href='$urlString';\"";
  }
  
  function evenOdd($rownum)
  {
    //set the row class for even or odd
    if ( (($rownum)%2) != 0)
    {
      $class = "roweven";
    }
    else 
    {
      $class = "rowodd";
    }
    return $class;
  }

  function fillIfNull($value)
  {
    return ($value == null ? '<em>null</em>' : $value);
  }
  
  function getPagingMenu()
  {
    $dataString = null;

    //get the total page count
    $dataCount = $this->db->getDataFromSql($this->sqlObject->getCountSql());
    $totalDataCount = $dataCount[0]['count'];
    $totalPages = ($totalDataCount % $this->pagingLimit == 0 ? $totalPages = $totalDataCount / $this->pagingLimit : $totalPages = floor(($totalDataCount / $this->pagingLimit) + 1));
    
    //don't show a paging menu if there is only one page
    if ($totalPages <= 1)
    {
      return $dataString;
    }
    
    $pageWindow = 5;
    // account for multiple tables on one page
    if ( ($this->get('table_id') == $this->getId() || !$this->get('table_id')) && $this->get('page'))
    {
      $thisPage = $this->get('page');
    }
    else
    {
      $thisPage = 1;
    }
#    $thisPage = ($this->get('page') ? $this->get('page') : 1);
    $previousPage = ($thisPage - 1 > 0 ? $thisPage - 1 : null);
    $nextPage = ($thisPage + 1 <= $totalPages ? $thisPage + 1 : null);
    
    //order param for SQL
    $orderParam = null;
    $dirParam = null;
    $tableIdParam = null;

    // if this table has an id assign the query string param
    if ($this->getId())
    {
      $tableIdParam = "&table_id=".$this->getId();
    }

    if ( ($this->get('table_id') == $this->getId() || !$this->get('table_id')) && $this->get('order'))
    {
      $order = $this->get('order');
      $orderParam = "&order=$order";

      //dir param for SQL
      if ( ( $this->get('table_id') == $this->getId() || !$this->get('table_id') ) && $this->get('dir'))
      {
        $dir = $this->get('dir');
        $dirParam = "&dir=$dir";
      }
    }

		$classStr = 'datamenu';
    
    if ( $this->getClass() )
    {
    	$classStr .= " {$this->getClass()}";
    }

    
    //output the paging menu
    if ($previousPage)
    {
      $dataString .= "<div class=\"$classStr\"><a href=\"?page=$previousPage$tableIdParam$orderParam$dirParam\">Previous</a></div>";
    }

    if ($nextPage)
    {
      $dataString .= "<div class=\"$classStr\"><a href=\"?page=$nextPage$tableIdParam$orderParam$dirParam\">Next</a></div>";
    }

    $pageGap = false;
    $dataString .= "<div class=\"$classStr\">";
    //output the numbered page list    
    for ($pageNo = 1 ; $pageNo <= $totalPages ; $pageNo++)
    {
      //bold the page if its this page
      if ($pageNo == $thisPage)
      {
        $pageString = "<strong>$pageNo</strong>";
      }
      else
      {
        $pageString = $pageNo;
      }
      
      //set the page link
      $pageLink = "<a href=\"?page=$pageNo$tableIdParam$orderParam$dirParam\">$pageString</a>";
      
      //output the page link...
      //...always if its the first or last page
      if ($pageNo == 1 || $pageNo == $totalPages)
      {
        $dataString .= $pageLink;
        $pageGap = false;
      }
      //...or it fits in the page window
      else if (($pageNo > $thisPage - ($pageWindow/2)) && ($pageNo < $thisPage + ($pageWindow/2)))
      {
        $dataString .= $pageLink;
        $pageGap = false;
      }
      //... otherwise output an elipsis as a gap
      else
      {
        //... but only print the gap once
        if ($pageGap == false)
        {
          if ($pageNo < $thisPage)
          {
            $gapPageNo = (($thisPage - $pageWindow) > 0 ? $thisPage - $pageWindow : 1);
          }
          else
          {
            $gapPageNo = (($thisPage + $pageWindow) <= $totalPages) ? $thisPage + $pageWindow : $totalPages;
          }
          $dataString .= "<a href=\"?page=$gapPageNo$tableIdParam$orderParam$dirParam\">...</a>";
          $pageGap = true;
        }
      }
    }
    $dataString .= "</div><div class=\"clear\"></div>";
    
    return $dataString;
  }

}
?>
