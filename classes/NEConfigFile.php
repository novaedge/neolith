<?php
/*
 * $Id: NEConfigFile.php 3809 2007-12-13 00:27:12Z pancoast $ 
 */

class NEConfigFile extends NEConfig
{

  function __construct($configFile = null)
  {
    parent::__construct();
    
    //set the global path of all config files
    $this->configPath = preg_replace(':/htdocs:','', "{$_SERVER['DOCUMENT_ROOT']}/");
    
    //assume the hostname if there is no specific file passed
    if (is_null($configFile))
    {
      $hostname = strtolower( $_SERVER['SERVER_NAME'] ); // always use lower case hostnames, for consistency
      $configFile = "{$hostname}.cfg";
    }
    
    if ( ! file_exists( $this->configPath.$configFile ) )
    {
      $configFile = "default.cfg";
    }
    
    $this->readFileContents($configFile);
  }

  function readFileContents($filename)
  {
    return $this->parseArray( file($this->configPath.$filename) );
  }

  function parseArray($anArr)
  {
    foreach ($anArr as $xstr)
    {
      $str = trim($xstr);
      // comments only allowed at start of line
      if ( '#' == $str[0] || ';' == $str[0] || '/' == $str[0] || '-' == $str[0] )
      {
          // skip comment lines
          // shell style (# comment)
          // C++ style (// comment)
          // SQL style (-- comment)
          // other/misc style (; comment)
      }
      else
      {
        $kv = $this->getKeyValue($str);
        $key = $kv['key'];
        $value = $kv['value']; 
        
        if ( '%include' == $key )
        {
          // tbd: do not read a file we've alredy read -- avoid infinite loops
					$this->readFileContents($value);
        }
        else
        {
          $this->set($key, $value);
        }
      }
    }
  }

  function getKeyValue($str)
  {
    // tbd: allow notation of value's type (hungarian prefix, qualifier, etc.)
    $ar = array( '=', ':', ' ');
    foreach ( $ar as $ch )
    {
      $pos = strpos($str, $ch);
      if ($pos !== false)
      {
        return $this->split_at($pos, $str);
      }
    }

    return array( 'key' => $str, 'value' => null);
  }

	// replace special strings with special values --
	//   also a good place to variable substitution or special vars
	//   like $_SERVER[blah]
	function replaceSpecialValues( $v )
	{
		$result = null;
		
		if ( 'false' == strtolower($v) )
		{
			$result = false;
		}
		else if ( 'true' == strtolower($v) )
		{
			$result = true;
		}
		else if ( '0' == strtolower($v) )
		{
			$result = 0;  // may not matter -- may be same as false
		}
		else
		{
			$result = $v;
		}
		
		return $result;
	}

  function split_at($pos, $str)
  {
    $key = trim( substr($str, 0, $pos) );
    $value = trim( substr($str, $pos+1) );
    $value = $this->replaceSpecialValues( $value );
    return array('key' => $key, 'value' => $value);
  }

	function arrayAsHtmlTable($ar)
	{
    $str ="<table>\n";
    foreach ($ar as $key => $value )
    {
      if ( true === $value )
      {
        $value = "true";
      }
      else if ( false == $value )
      {
        $value = "false";
      }
      $str .= "<tr><td>$key</td><td>$value</td></tr>\n";
    }
    $str .= "</table>\n";
    return $str;
	}	

  function asSortedHtml()
  {
    $str ="<table>\n";
    $keys = array_keys( $this->varSet);
    sort($keys);
    foreach ($keys as $key )
    {
      $str .= "<tr><td>$key</td><td>{$this->varSet[$key]}</td></tr>\n";
    }
    $str .= "</table>\n";
    return $str;
  }

  function asHtml()
  {
		return $this->arrayAsHtml( $this->varSet );
  }

}

?>
