<?php
/**
 * Defines a class to handle CSV files
 * @package neolith
 * @version $Id: NECsvFile.php 2205 2007-03-26 19:18:22Z jreed $
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
 
include_once('NEFile.php');
 
/**
 * Defines a class to handle CSV files
 * @package neolith
 */
class NECsvFile extends NEFile
{
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }

  /**
   * @todo complete port of NECsvFile::makeCsvLine, test
   */
  function makeCsvLine($values) {
    // If a value contains a comma, a quote, a space, a 
    // tab (\t), a newline (\n), or a linefeed (\r),
    // then surround it with quotes and replace any quotes inside
    // it with two quotes
    foreach($values as $i => $value) 
    {
      if ((strpos($value, ',')  !== false) ||
          (strpos($value, '"')  !== false) ||
          (strpos($value, ' ')  !== false) ||
          (strpos($value, "\t") !== false) ||
          (strpos($value, "\n") !== false) ||
          (strpos($value, "\r") !== false)) 
      {
        $values[$i] = '"' . str_replace('"', '""', $value) . '"';
      }
    }
    // Join together each value with a comma and tack on a newline
    return implode(',', $values) . "\n";
  }
  
  /**
   * @todo complete port of NECsvFile::readCsvFileData, test
   */
  function readCsvFileData($filename, $delimiter = ',')
  {
    //test that the file exists
    if (file_exists($filename))
    {
      //create the data set array
      $dataSet = array();
      
      //get the file into an an array
      $csvFile = fopen($filename, 'rb');  
        
      $rowsImported = 0;  
      //loop through the lines, and insert into table
      for ($data = fgetcsv($csvFile, 1024, $delimiter) ; ! feof($csvFile) ; $data = fgetcsv($csvFile, 1024, $delimiter))
      { 
        $dataSet[] = $data;
      }
      
      //close the file
      fclose($csvFile);
      
      return $dataSet;
    }
    else
    {
      $this->addError("The file $filename does not exist!");
      return false;
    }
  }
    
}
?>