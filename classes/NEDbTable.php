<?php
/**
 * Defines the class to manage access to rows of a database table
 * @package neolith
 * @version $Id: NEDbTable.php 2205 2007-03-26 19:18:22Z jreed $
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

include_once('NEDb.php');

/**
 * Defines the class to manage access to rows of a database table
 * @package neolith
 */
class NEDbTable extends NEDb 
{
  var $table = null;
  var $columns = null;
  var $pk = null;
  var $autoIncrement = true;
  
  /**
   * the class constructor
   */
  function __construct($table)
  {
    parent::__construct();
    //set the table
    $this->table = $table;
    
    //reset the column list
  		$this->columns = array();
    
    //get the column list from the db
    $result = @mysql_query("show columns from $this->table");
    if (!$result) {
       $this->addError("The query encountered an error: " . mysql_error());
       exit();
    }
    
    //check if we have results
    if (@mysql_num_rows($result) > 0)
    {
      //load the column list if there
      while ($row = @mysql_fetch_assoc($result))
      {
        $this->columns[] = $row['Field'];
        $this->set($row['Field'], null);
        //test for PK
        if ($row['Key'] == 'PRI')
        {
          $this->pk = $row['Field'];
          
          //check if the primary key auto increments
          if ($row['Extra'] != 'auto_increment')
          {
            $this->autoIncrement = false;
          }
        }
      }
    }    
  }
  
  function getColumnList()
  {
  		$string = @implode(',', $this->columns);
  		return $string;
  }
  
  function getValuesList($row) 
  {
		$string = null;
		foreach ($this->columns as $key) 
		{
			$value = $row[$key];
			if ($value)
			{
				if (get_magic_quotes_gpc())
				{
					$value = stripslashes($value);
				}
				$string .= "'".mysql_real_escape_string($value)."',";
			}
			else
			{
				$string .= "null,";
			}
		}
		return rtrim($string, ',');
  }
  
  function getSetList($row)
  {
		$string = null;
		foreach ($row as $key => $value)
		{
      if ($value)
      {
        //test if magic quotes are on, which requires to stripslashes()
        if (get_magic_quotes_gpc())
        {
          $value = stripslashes($value);
        }
        $string .= "`$key` = '".mysql_real_escape_string($value)."',";
      }
      else
      {
        $string .= "`$key` = null,";
      }
		}
		return rtrim($string, ',');
  }
  
  function insert($withColumnList = false)
  {
  		//build the select row
  		$valueList = null;
    $sql = "insert into $this->table "; 
    
    if ($withColumnList == false)
    {
       $sql .= "(".$this->getColumnList().") ";
    }         
        
    $sql .= "values " .
      "(".$this->getValuesList($this->getVarSet()).")";  
    $this->bug($sql);
    $result = mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError("The query encountered an error: " . mysql_error());
      return false;
    }
    
    
    //set the PK if it auto increments
    if ($this->autoIncrement)
    {
      $insertId = @mysql_insert_id();
      $this->set($this->pk, $insertId);
    } 
    return true;    
  }
  
  /**
   * update table
   * @param int $id primary key to update
   * @param bool $updateAllRows update all rows in table (no 'where' specified)
   */
  function update($id = null, $updateAllRows = false)
  {
    if ($id == null)
    {
      $id = $this->get($this->pk);
    }

    //update
    if ($updateAllRows)
    {
      $sql = "update $this->table set ".$this->getSetList($this->getVarSet());
    }
    else
    {
      $sql = "update $this->table set ".$this->getSetList($this->getVarSet())." where $this->pk = '$id'";
    }
    $this->bug($sql);
    $result = @mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError("The query encountered an error: " . mysql_error());
      return false;
    }
    return true;    
  }
  
  function updateWhere($whereClause)
  {
  	  //update
    $sql = "update $this->table set ".$this->getSetList($this->getVarSet())." where $whereClause";
    $this->bug($sql);
    $result = @mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError("The query encountered an error: " . mysql_error());
      return false;
    }
    return true;
  }
  
  function delete($id)
  {
    if ($this->getDataForKey($id) == false)
    {
      $this->addError("The query encountered an error: There is no record for ID = $id");
      return false;
    }
    
    $sql = "delete from $this->table where $this->pk = '$id'";
    $result = @mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError("The query encountered an error: " . mysql_error());
      return false;
    }
    return true;    
  }
  
  /**
   * performs a delete based off a user defined where clause
   * @param string $table name of database table to perform query on  
   * @param string $whereClause the where statement
   * @return boolean returns true if a row is sucessfully deleted
   */
  function deleteWhere($table, $whereClause)
  {    
    $sql = "delete from $table where $whereClause";
    $result = @mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError("The query encountered an error: " . mysql_error());
      return false;
    }
    return true;
  }
  
  /**
   * retrieves a database table row for a unique key
   * @param mixed $id the value of the key that identifies a table row
   * @param string $column the name of the column containing the unique identifier; defaults to primary key
   * @return boolean returns true if a row is sucessfully returned
   */
  function getDataForKey($id, $column = null)
  {
    if ($column)
    {
      $keyColumn = $column;
    }
    else
    {
      $keyColumn = $this->pk;
    }
    
    //return of $data is now deprecated...
    $data = $this->getData($this->table, $id, $keyColumn);
    
    if (!$data)
    {
      return false;
    }
    
    return true;
  }
  
  
  /**
   * retrieves a database table row by where clause
   * @param string $where the where clause to return a unique row
   * @return boolean returns true if a row is sucessfully returned
   */
  function getDataWhere($where = null)
  {
    $data = parent::getDataWhere($this->table, $where);
    if (!$data)
    {
      return false;
    }

    return true;
  }
  
  function setData($data)
  {
    foreach ($data as $key => $value)
    {
	    $this->set($key, $value);
    }
  }

  function reset()
  {
    $this->setVarSet();
  }
  
  function now()
  {
    return date('Y-m-d H:i:s');
  }
  
}
?>
