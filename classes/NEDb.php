<?php
/**
 * Defines the base class for database operations
 * @package neolith
 * @version $Id: NEDb.php 2205 2007-03-26 19:18:22Z jreed $
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
 * Defines the base class for database operations
 * @package neolith
 */
class NEDb extends NEObject
{
  var $cnx;
  var $numRows = 0;
  var $isFirstFetch = true;
  var $autoFetch = true;
  var $dataSet = null;

  /**
   * the class constructor
   */
  function __construct(&$config = null)
  {
    //call the parent constructor
    parent::__construct();

    //handle the config object
    $this->setConfig($config);
  }

  function connect()
  {
    //connect using the specified port if necessary
    
    if ($this->getConfig()->get('dbPort'))
    {
      $server = $this->getConfig()->get('dbHost') . ':' . $this->getConfig()->get('dbPort');
    }
    else
    {
      $server = $this->getConfig()->get('dbHost');
    }
    
    $this->cnx = mysql_connect($server, $this->getConfig()->get('dbUsername'), $this->getConfig()->get('dbPassword'));
    if (!$this->cnx)
    {
      $this->addError("Could not connect to DB as {$this->getConfig()->get('dbUsername')}@{$this->getConfig()->get('dbHost')} : " . mysql_error());
      return false;
    }
    $dbSelected = mysql_select_db($this->getConfig()->get('dbDatabase'));
    if (!$dbSelected)
    {
      $this->addError("Could not select DB {$this->getConfig()->get('dbDatabase')} : " . mysql_error());
    }
    //set autoFetch
    if ($this->getConfig()->get('dbAutoFetch'))
    {
      $this->setAutoFetch($this->getConfig()->get('dbAutoFetch'));
    }
    
    return true;
  }
  
  function createTable($tableName)
  {
    $table = new NEDbTable($tableName);
    return $table;
  }

  function getData($tableName, $id = null, $key = 'id', $indexByName = true)
  {
    //sql to select registries
    $sql = "select * from $tableName";
    
    //add the key clause
    if ($id != null)
    {
      $sql .= " where $key = '$id'";
    }
    
    $data = null;    
    //get the data array
    $data = $this->getDataFromSql($sql, $indexByName);
  
    return $data;  
  }
    
  function getDataFromSql($sql, $indexByName = true)
  {
    //clear the internal dataset before assigning new data
    $this->clearDataSet();
    $row = null;

    //debug the sql
    $this->bug($sql,'sqlString');
  
    //run the query
    $result = mysql_query($sql);
    //test the result
    if (!$result)
    {
      $this->addError(mysql_error(), 'sqlError');
      return false;   
    }
    $this->numRows = mysql_num_rows($result);
    
    
    if ($this->numRows > 0)
    {
      //fetch records
      for ($i = 0 ; $fetch = mysql_fetch_array($result) ; $i++)
      {
        $j = 0;
        while ($name = @mysql_field_name($result, $j++))
        {
          if ($indexByName)
          {
            $row[$name] = $fetch[$name];
          }
          else
          {
            $row[$j] = $fetch[$name];
          }
        }
        //assign the data to the dataset
        $this->dataSet[$i] = $row;
      }
    }

    //reset the data set 
    $this->resetDataSet();
    
    //fetch if autoFetch is true
    if ($this->getAutoFetch())
    {
      $this->fetch();
    }
        
    //return the dataSet for backward compatibility
    return $this->dataSet;  
  }
  
  function fetch()
  {
    $dataRow = null;
    //get the next data row from the dataSet
    //...check if this is the first time thru
    if ($this->isFirstFetch)
    {
      //assign the varSet with the first row
      $dataRow = current($this->dataSet);
      
      //set isFirstFetch to false
      $this->isFirstFetch = false;
    }
    else 
    {
      $dataRow = next($this->dataSet);
    }
    
    if ($dataRow)
    {
      //assign the varSet
      $this->setVarSet($dataRow);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  
  function getDataWhere($tableName, $where = null, $indexByName = true)
  {
    //sql to select registries
    $sql = "select * from $tableName";
    
    //add the key clause
    if ($where != null)
    {
      $sql .= " where $where";
    }
    
    $data = null;    
    //get the data array
    $data = $this->getDataFromSql($sql, $indexByName);
  
    return $data;  
  }
  
  function runQuery($sql)
  {
    $result = mysql_query($sql);
    
    if (!$result)
    {
      //db query failed
      $this->addError(mysql_error(), 'sqlError');
      return false;
    }
    return true;
  }
  
  function getNumRows()
  {
    return $this->numRows;
  }
  
  function getNumRowsFromSql($sql, $indexByName = true)
  {
    $table = null;
    $row = null;
    //run the query
    $result = mysql_query($sql);
    if (!$result)
    {
      //db query failed
      $this->addError(mysql_error(), 'sqlError');
      return false;
    }
    $num = mysql_num_rows($result);
    return $num;
  }

  /**
   * @todo complete port of NESequence::getSequence, test
   */
  function getSequence($tableName)
  {
    $seq = 0;
    //TODO: add a function to test if table exists and create it if not exists
    /*
     *  CREATE TABLE `$tableName_sequence` (
          `id` int(11) NOT NULL auto_increment,
          `value` int(11) default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1
     */
    
    //this function emulates a sequence number using a provided table
    $sql = "insert into $tableName (value) values (0)";
    $result = mysql_query($sql);
  
    if (!$result)
    {
      //db failed insert
      $this->addError(mysql_error(), 'sqlError');
      return 0;
    }
    //return the sequence
    $seq = mysql_insert_id();
    
    //now delete any lesser values to only keep one value in the table
    $sqlDelete = "delete from $tableName where id < $seq";
    $result = mysql_query($sqlDelete);
    if (!$result)
    {
      //db failed insert
      $this->addError(mysql_error(), 'sqlError');
      return 0;
    }
    
    return $seq;
  }
  
  /*
   * Gets the list of tables in the database
   * returns table names
   * @param string $tablePrefix the name, or partial name of the tables
   */
  function getDbTables($tablePrefix = null)
  {
    if ($tablePrefix)
    {
    	$sql = "show tables like '%{$tablePrefix}%'";
    }
    else
    {
  	  $sql = "show tables";
    }
    $data = $this->getDataFromSql($sql);
    return $data;
  }
  
  /*
   * @param string $tableName name of table
   * returns table structure 
   */
  function getDbTableStructure($tableName)
  {
    $sql = "describe '$tableName'";
    $data = $this->getDataFromSql($sql);
    return $data;  	
  }
  
  function setDataSet(&$dataSet = null)
  {
    if ($dataSet)
    {
      $this->dataSet = $dataSet;
    }
    else
    {
      $this->dataSet = array();
    }
  }
  
  function getDataSet()
  {
    return $this->dataSet;
  }

  function resetDataSet()
  {
    reset($this->dataSet);
    $this->isFirstFetch = true;
  }

  function clearDataSet()
  {
    $this->dataSet = array();
    $this->isFirstFetch = true;
  }

  function getAutoFetch()
  {
    return $this->autoFetch;
  }
  
  function setAutoFetch($autoFetch)
  {
    $this->autoFetch = $autoFetch;
  }
}
?>
