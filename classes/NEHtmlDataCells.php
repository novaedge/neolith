<?php
/**
 * Defines the HtmlDataCells class to build div cells from data arrays
 * @package neolith
 * @version $Id: NEHtmlDataCells.php 129 2008-10-23 00:33:44Z jreed $
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
 * Defines the HtmlDataCells class to build div cells from data arrays
 * @package neolith
 */
class NEHtmlDataCells extends NEHtmlObject
{
  var $data = null;
  var $paging = false;
  var $pagingLimit = null;
  var $sqlObject = null;
  var $db = null;
  var $exampleCell = null;
  var $leftDelimiter = null;
  var $rightDelimiter = null;
  var $dbFieldsToMatch = null;
  var $showAll = false;
  var $hidePager = false;

  /**
   * the class constructor
   * @param string $name sets the name/id/class name for the main data cell
   */
  function __construct($name, $leftDelimiter, $rightDelimiter)
  {
    parent::__construct();

    // set the id for the data table using the given name
    if ($name)
    {
      $this->setId($name);
    }

    $this->setDelimiters($leftDelimiter, $rightDelimiter);
  }

  /**
   * sets the left & right delimiters for string replacing
   * 
   * @param string $leftDelimiter left delimiter
   * @param string $rightDelimiter right delimiter
   */
  function setDelimiters($leftDelimiter, $rightDelimiter)
  {
    if ($leftDelimiter == $rightDelimiter)
    {
      $this->addError("Delimiters must be different");
      return false;
    }

    $this->leftDelimiter = $leftDelimiter;
    $this->rightDelimiter = $rightDelimiter;
  }

  /**
   * - sets the example cell html used for all cells
   * - create dbFieldsToMatch set
   * 
   * @param string $exampleHtml html showing how cells are layed out. will 
   * contain db field names enclosed in delimiters to be replaced by their
   * respective values in each cell. db fields you specify in html will have 
   * to be fields returned by the sqlObject you feed this class.
   */
  function setExampleCell($exampleHtml)
  {
    $this->exampleCell = $exampleHtml;

    // escape special regex characters in delimiters
    $escapeChars = array(
      '/' => '\/', 
      '[' => '\[', 
      ']' => '\]', 
      '(' => '\(', 
      ')' => '\)'
    );
    $leftDelimiter = str_replace( array_keys($escapeChars), array_values($escapeChars), $this->leftDelimiter);
    $rightDelimiter = str_replace( array_keys($escapeChars), array_values($escapeChars), $this->rightDelimiter);

    // find db fields in exampleHtml
    $search = $leftDelimiter.'(.*?)'.$rightDelimiter;
    preg_match_all("/$search/", $exampleHtml, $matches);

    // set db field names
    foreach($matches[1] as $match)
    {
      if ( ! in_array($match, (array)$this->dbFieldsToMatch))
      {
        $this->dbFieldsToMatch[] = $match;
      }
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
   * turns paging on and sets the number of rows to display while paging
   * 
   * @param int $limit the number of cells to display while paging
   * 
   */
  function setPaging($limit, $showAll = false)
  {
    $this->paging = true;
    $this->pagingLimit = $limit;
    $this->showAll = $showAll;
  }

  /**
   * limit the number of cells shown. intended to be used if you aren't using
   * paging but you don't want all cells shown
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
    // TODO: There is strange bug sometimes where improper data is shown on pages
    // It is intermittent and seems to happen more when paging set low i.e., 1 or 2.
    // Problem doesn't seem to occur with higher paging.
    // I have to test more to define more. pancoast

    //assign the db
    $this->db = $db;
    
    //assign the internal sqlObject
    $this->sqlObject = $sqlObject;
    
    //check if we are showing all items
    if ($this->get('page') == 'all')
    {
      $this->hidePager = true;
    }
    
    // include paging clauses in the query
    if ($this->paging && !$this->hidePager)
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
   * Returns html string for cells 
   * 
   * @param boolean $pagingBelow Display paging below cells
   * @param boolean $pagingAbove Display paging above cells
   * 
   */
  function getCells($pagingBelow = false, $pagingAbove = true)
  { 
    // example data cell must be set until
    // some default sample cells are created
    if ( ! $this->exampleCell)
    {
      $this->addError("Must create example cell");
      return false;
    }

    $dataString = null;

    if (is_array($this->data) && count($this->data) > 0)
    {
      //get the paging menu if needed
      if ($this->paging && $pagingAbove)
      {
        $dataString .= $this->getPagingMenu();
      }
      
      // open main cell
      $dataString .= "<div class=\"{$this->getId()}\">\n";

      // create each data cell
      foreach ($this->data as $rowNum => $cellData)
      {
        $className = $this->getId() . 'Cell';

        // open cell
        $dataString .= "<div class=\"$className\">\n";

        // replace db field names with their real db value in cell markup
        foreach($this->dbFieldsToMatch as $key)
        {
          $replace = $this->leftDelimiter . $key . $this->rightDelimiter;
          $subArray[$replace] = $cellData[$key];
        }

        $dataString .= str_replace( array_keys($subArray), array_values($subArray), $this->exampleCell );
        
        $dataString .= "</div>\n\n";

        // TODO: Add row limit. Currently div's will float to next row based on page space.
        // TODO: Implimit limit. Will limit resulst shown. Only matters if no paging.
      }

      $dataString .= "<div class=\"clear\"></div>";
      $dataString .= "</div>\n";

      //get the paging menu if needed
      if ($this->paging && $pagingBelow)
      {
        $dataString .= $this->getPagingMenu();
      }
    } //-- end if (is_array($this->data) && count($this->data) > 0)

    else
    {
      return false;
    }

    return $dataString;
  }

  /**
   * sets the data to be included in the cells
   * 
   * @param array $data a multidimentional array of rows of data to include in the cells
   * 
   */
  function setData($data)
  {
    $this->data = $data;
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

    //initiate the open div
    $classStr = 'datamenu';
    if ( $this->getClass() )
    {
      $classStr .= " {$this->getClass()}";
    }

    //output containing class
    $dataString .= "<div class=\"$classStr\">";

    //check if we are hiding the paging menu and showing all items
    if ($this->get('page') == 'all' && $this->hidePager)
    {
      $dataString .= "<span class=\"$classStr\"><a href=\"?page=1$tableIdParam\">Show Paged List</a></span>";
      //output containing class and quit!
      $dataString .= "</div>";
      return $dataString;
    }
        
    //output the paging menu
    if ($previousPage)
    {
      $dataString .= "<span class=\"$classStr\"><a href=\"?page=$previousPage$tableIdParam$orderParam$dirParam\">Previous</a></span>";
    }

    $pageGap = false;
    $dataString .= "<span class=\"$classStr\">";
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
    $dataString .= "</span>";

    if ($nextPage)
    {
      $dataString .= "<span class=\"$classStr\"><a href=\"?page=$nextPage$tableIdParam$orderParam$dirParam\">Next</a></span>";
    }


    if ($this->showAll)
    {
      $dataString .= "<span class=\"$classStr\"><a href=\"?page=all$tableIdParam\">Show All</a></span>";
    }
    $dataString .= "<div class=\"clear\"></div>";

    //output containing class
    $dataString .= "</div>";
        
    return $dataString;
  }

}
?>
