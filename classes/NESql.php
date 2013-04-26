<?php
/**
 * Defines the Sql class to build and manage SQL queries
 * @package neolith
 * @version $Id: NESql.php 3818 2007-12-14 04:57:58Z pancoast $
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
 * Defines the Sql class to build and manage SQL queries
 * @package neolith
 */
class NESql extends NEObject
{
  var $selectSet = null;
  var $tableSet = null;
  var $whereSet = null;
  var $whereOrSet = null;
  var $orderSet = null;
  var $groupSet = null;
  var $limitSet = null;
  var $distinct = false;
  var $having = null;
  
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
   
  function select($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->selectSet = array();
    }
    $this->selectSet[] = $string;
  }
  
  function table($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->tableSet = array();
    }
    $this->tableSet[] = $string;
  } 

  function where($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->whereSet = array();
    }
    $this->whereSet[] = $string;
  }
  
  function group($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->groupSet = array();
    }
    $this->groupSet[] = $string;
  }
  
  function whereOr($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->whereOrSet = array();
    }
    $this->whereOrSet[] = $string;
  }

  function having($string)
  {
    $this->having = $string;
  }

  function order($string, $reset = false)
  {
    if ($reset == true)
    {
      $this->orderSet = array();
    }
    $this->orderSet[] = $string;
  }
  
  function limit($begin, $end = null, $reset = false)
  {
    if ($reset == true)
    {
      $this->limitSet = array();
    }
    $this->limitSet[] = $begin;
    if ($end)
    {
      $this->limitSet[] = $end;
    }
  }
  
  function getSql($includeLimit = true)
  {
    $sql = null;
    $whereSet = false;
    if ($this->distinct)
    {
    	$sql = 'select distinct ';
    }
    else
    {
      $sql = 'select ';	
    }
    $sql .= ($this->selectSet ? implode(', ', $this->selectSet) : '*');
    $sql .= ' from ';
    $sql .= ($this->tableSet ? implode(', ', $this->tableSet) : null);
    if ($this->whereOrSet)
    {
			if (!$whereSet)
      {
        $sql .= ' where ';
        $whereSet = true;
      }
      else
      {
         $sql .= ' and ';
      }
      $sql .= implode( ' or ', $this->whereOrSet);
    }
    if ($this->whereSet)
    {
      if (!$whereSet)
      {
       $sql .= ' where ';
       $whereSet = true;
      }
      else
      {
      	 $sql .= ' and ';
      }
      $sql .= implode(' and ', $this->whereSet);
    }
    if ($this->groupSet)
    {
      $sql .= ' group by ';
      $sql .= implode(', ', $this->groupSet);
    }
    if ($this->having)
    {
      $sql .= ' having ' . $this->having;
    }
    if ($this->orderSet)
    {
      $sql .= ' order by ';
      $sql .= implode(', ', $this->orderSet);
    }
    if ($includeLimit && $this->limitSet)
    {
      $sql .= ' limit ';
      $sql .= implode(', ', $this->limitSet);
    }
    return $sql;
  }

  function getCountSql()
  {
    $sql = null;    
    $sql = "select count(*) as 'count' ";
    
    $sql .= ' from ';
    $sql .= ' ( ';
    $sql .= $this->getSql(false);
    $sql .= ' ) NE_COUNT_TABLE ';

    return $sql;
  }
  
  function setDistinct($distinctStatus)
  {
  	$this->distinct = $distinctStatus;
  }
}
?>
