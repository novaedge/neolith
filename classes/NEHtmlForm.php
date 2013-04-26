<?php
/**
 * Defines the class to build HTML forms
 * @package neolith
 * @version $Id: NEHtmlForm.php 3556 2007-09-28 19:15:22Z pancoast $
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
 
include_once('NEHtmlRequestObject.php');

/**
 * Defines the class to build HTML forms
 * @package neolith
 */
class NEHtmlForm extends NEHtmlRequestObject
{
  var $form = null;
  var $formInputSet = null;  
  var $formClass = null;
  
  /**
   * $selectDefaultMessage is the default message for a select box
   */
  var $selectDefaultMessage = 'Please pick a value';
  
  /**
   * the class constructor sets the form attributes
   * @param string $name sets the name for the form
   * @param string $action sets the action value for the form
   * @param string $method sets the method value for the form
   */
  function __construct($name, $action = null, $method = null, $target = null)
  {
    parent::__construct();
    if ($action == null)
    {
      $action = $_SERVER['PHP_SELF'];
    }
    
    if ($method == null)
    {
      $method = 'POST';
    }
    
    //set the id for this form using the form name
    $this->setId($name);
    
    $this->form = array('name' => $name, 'action' => $action, 'method' => $method, 'target' => $target);    
  }
  
  /**
   * add a text input field to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param string $value the value for the field; leave null to use the page request variables
   * @param int $maxlength the maxlength in characters for the input field
   */
  function addInputText($name, $label, $value = null, $maxlength = null)
  {
    if ($value == null)
    {
      $value = $this->get($name);
    }
    $this->formInputSet[] = array('label' => $label, 'type' => 'text', 'name' => $name, 'value' => $value, 'maxlength' => $maxlength);
  }
  
  /**
   * add a group of checkbox fields to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param array $data the array containing the data to include in the field
   * @param string $valueColumn the data column that will set the keyed value for each checkbox
   * @param string $textColumn the data column that will set the text value for each checkbox
   * @param array $valueSelected the value for the field; leave null to use the page request variables
   * @param array $valueDisabled array of disabled fields
   * @param boolean $omitUnselected true if we should leave non-selected fields out of possible choices (e.g., for viewing/listing only those selected)
   */
  function addInputCheckboxFromData($name, $label, $data, $valueColumn, $textColumn, $valueSelected = null, $valueDisabled = null, $omitUnselected = false)
  {
    $checkboxData = array();
    if ($valueSelected == null)
    {
      $valueSelected = $this->get($name);
    }
    
    foreach ($data as $row)
    {
    	$selected = $this->getCheckboxSelectStatus($row[$valueColumn], $valueSelected);

    	if ( false == $omitUnselected || $selected )
    	{
	      $checkboxData[] = array('name' => $name, 'value' => $row[$valueColumn], 'text' => $row[$textColumn], 
																'selected' => $selected,
																'disabled' => $this->getCheckboxSelectStatus($row[$valueColumn], $valueDisabled));
    	}
    }            
    
    $this->formInputSet[] = array('label' => $label, 'type' => 'checkbox', 'name' => $name, 'data' => $checkboxData);
  }
  
  /**
   * sets which checkboxes will be checked out of a group of check boxes
   * @param string $valueColumn the value of the current row (i think)
   * @param array $valueSelectedArray an array of check box selected status (i think)
   */
  function getCheckboxSelectStatus($valueColumn, $valueSelectedArray)
  {
    if ($valueSelectedArray)
    {
      foreach ($valueSelectedArray as $row)
      {
        if ($row == $valueColumn)
        {
          return true;
        }          
      }
    }
    return false;
  }
      
  /**
   * add a checkbox field to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param string $value the value for the field
   * @param string $valueSelected the selected value for the field; leave null to use the page request variables
   */
  function addInputCheckbox($name, $label, $value, $text, $valueSelected = null)
  {
    $checkboxData = array();    
    if ($valueSelected == null)
    {
      $valueSelected = $this->get($name);
    }
    $checkboxData[] = array('name' => $name, 'value' => $value, 'text' => $text, 'selected' => ($value == $valueSelected ? true : false));
    $this->formInputSet[] = array('label' => $label, 'type' => 'checkbox', 'name' => $name, 'data' => $checkboxData);    
  }
  
  /**
   * add a password input field
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param string $value the value for the field; leave null to use the page request variables
   */
  function addInputPassword($name, $label, $value = null, $customParams = null)
  {
    $this->formInputSet[] = array('label' => $label, 'type' => 'password', 'name' => $name, 'value' => $value, 'customParams' => $customParams);
  }
  
  /**
   * add a group of radio buttons to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param array $data the array containing the data to include in the field
   * @param string $valueColumn the data column that will set the keyed value for each checkbox
   * @param string $textColumn the data column that will set the text value for each checkbox
   * @param string $valueSelected the value for the field; leave null to use the page request variables
   */
  function addInputRadio($name, $label, $data, $valueColumn, $textColumn, $valueSelected = null)
  {
    if ($valueSelected == null)
    {
      $valueSelected = $this->get($name);
    }
    
    foreach ($data as $row)
    {            
      $radioSet[] = array('name' => $name, 'value' => $row[$valueColumn], 'text' => $row[$textColumn], 'selected' => ($row[$valueColumn] == $valueSelected ? true : false));      
    } 
    
    $this->formInputSet[] = array('label' => $label, 'type' => 'radio', 'data' => $radioSet);   
  }

  /**
   * add a selection box to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param array $data the array containing the data to include in the field
   * @param string $valueColumn the data column that will set the keyed value for each checkbox
   * @param string $textColumn the data column that will set the text value for each checkbox
   * @param string $valueSelected the value for the field; leave null to use the page request variables
   * @param boolean $submitOnChange adds an onChange parameter to the select box to submit the form on change
   * @param integer $sizeVisible sets the visible size of the list, to display in a list box
   * @param boolean $multipleSelect allow multiple select on the select list
   */
  function addInputSelect($name, $label, $data, $valueColumn, $textColumn, $valueSelected = null, $submitOnChange = false, $sizeVisible = null, $multipleSelect = false, $customParams = null)
  {    
    if ($valueSelected == null)
    {
      $valueSelected = $this->get($name);
    }
    
    if (is_array($data))
    {
      foreach ($data as $row)
      {      
        $selectData[] = array('value' => $row[$valueColumn], 'text' => $row[$textColumn], 'selected' => ($row[$valueColumn] == $valueSelected ? true : false));     
      }
    }
    
    $this->formInputSet[] = array('label' => $label, 'type' => 'select', 'data' => $selectData, 'name' => $name, 'submitOnChange' => $submitOnChange, 'sizeVisible' => $sizeVisible, 'multipleSelect' => $multipleSelect, 'customParams' => $customParams);
  }
  
  function addInputRadioYesNo($name, $label, $valueSelected = null)
  {
  	$this->formInputSet[] = array('label' => $label, 'type' => 'radioyn', 'name' => $name, 'selected' => $valueSelected);
  }
  
  function addInputSelectYesNo($name, $label, $valueSelected = null, $submitOnChange = false)
  {
    $this->formInputSet[] = array('label' => $label, 'type' => 'selectyn', 'name' => $name, 'selected' => $valueSelected, 'submitOnChange' => $submitOnChange);
  	
  }
  
  //function addInputRadioYes
  
  /**
   * add a hidden input field to the form
   * @param string $name the key name for the field
   * @param string $value the value for the field; leave null to use the page request variables
   */
  function addInputHidden($name, $value = null)
  {
    if ($value == null)
    {
      $value = $this->get($name);
    }
    $this->formInputSet[] = array('type' => 'hidden', 'name' => $name, 'value' => $value);
  }
  
  /**
   * add a generic text field to the form; can be used for custom input fields
   * @param string $text the text to display
   * @param string $label the accompanying label to the field
   */
  function addTextField($text, $label = null)
  {
    $this->formInputSet[] = array('type'=> 'none', 'label' => $label, 'text' => $text);
  }
  
  /**
   * add a text area to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label to the field
   * @param string $rows number of rows to draw for the text area
   * @param string $columns number of columns to draw for the text area
   * @param string $value the value for the field; leave null to use the page request variables   
   */
  function addTextArea($name, $label, $rows, $columns, $value = null, $readOnly = false)
  {
    if ($value == null)
    {
      $value = $this->get($name);
    }
    
    $this->formInputSet[] = array('type' => 'textarea', 'label' => $label, 'name' => $name, 'value' => $value, 'rows' => $rows, 'columns' => $columns, 'readOnly' => $readOnly);  	
  }
  
  /**
   * add a static text field to the form that is translated from a keyed data set 
   * @param string $key the key that you want translated; also the default text to display 
   * @param string $label the accompanying label to the field
   * @param array $data the array containing the data to include in the field
   * @param string $valueColumn the data column that will set the keyed value for each checkbox
   * @param string $textColumn the data column that will set the text value for each checkbox
   */
  function addKeyedText($key, $label, $data, $valueColumn, $textColumn)
  {
    $keyedText = $key;
    if (is_array($data))
    {
      foreach ($data as $row)
      {
        if ($key == $row[$valueColumn])
        {
          $keyedText = $row[$textColumn];
          break;
        }
      }
    }
    $this->formInputSet[] = array('type'=> 'none', 'label' => $label, 'text' => $keyedText);
  }
  
  /**
   * add a submit button to the form
   * @param string $name the key name for the field
   * @param string $value the value to show on the button
   * @param boolean $cancel whether or not to include a cancel/back button
   */
  function addInputSubmit($name, $value, $cancel = false)
  {
    $this->formInputSet[] = array('type' => 'submit', 'value' => $value, 'name' => $name, 'cancel' => $cancel);
  }
   
  /**
   * add a javascript popup date picker to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label for the field
   * @param string $value the value of the text input field
   * @param string $buttonValue 
   * @param string $format the way in which to format the date
   */  
  function addDatePicker($name, $label, $value, $buttonValue, $format)
  {
    $this->formInputSet[] = array('label' => $label, 'type' => 'datepicker', 'name' => $name, 'value' => $value, 'buttonValue' => $buttonValue, 'format' => $format);
  }
  
  /**
   * add static HTML text to the form; can be used for custom HTML layout
   * @param string $text the HTML text to display
   */
  function addStaticText($text)
  {
    $this->formInputSet[] = array('type'=> 'static', 'text' => $text);
  }
  
  /**
   * add an open file button to the form
   * @param string $name the key name for the field
   * @param string $label the accompanying label for the field
   * @param string $value the value for the field; leave null to use the page request variables
   */
  function addFileUpload($name, $label, $value = null)
  {
    if ($value == null)
    {
      $value = $this->get($name);
    }
    $this->formInputSet[] = array('label' => $label, 'type' => 'file', 'name' => $name, 'value' => $value);  	
  }
  
  /**
   * gets the HTML string for a text input
   * @return string an HTML string containing a text input
   */
  function getTextHTML($data)
  {    
    $maxlength = ( $data['maxlength'] === null ? '' : " maxlength=\"{$data['maxlength']}\"" );
    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    $dataString .= "<input type=\"text\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" value=\"{$data['value']}\"{$maxlength}/>";

    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString; 
  }
  
  /**
   * gets the HTML string for a password input
   * @return string an HTML string containing a password input
   */
  function getPasswordHTML($data)
  {
    $customParams = null;

    if ($data['customParams'])
    {
      $customParams = ' '.$data['customParams'];
    }

    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    $dataString .= "<input type=\"password\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" value=\"{$data['value']}\"$customParams/>";
    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString;    
  }
  
  /**
   * gets the HTML string for a hidden input
   * @return string an HTML string containing a hidden input
   */
  function getHiddenHTML($data)
  {
    $dataString = "<input type=\"hidden\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" value=\"{$data['value']}\"/>\n";
    return $dataString;    
  }
  
  /**
   * gets the HTML string for a radio button set
   * @return string an HTML string containing a radio button set
   */  
  function getRadioHTML($data)
  {
    $selected = null;
    $dataString = "\n<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    
    foreach ($data['data'] as $row)
    {
      if($row['selected'] == true)
      {
        $selected = 'checked="checked"';      
      }
      else
      {
        $selected = null;
      } 
      $dataString .= "\n<span class=\"".$this->getSubClass($row['name'])."RadioItem\"><input type=\"radio\" class=\"".$this->getSubClass($row['name'])."\" name=\"{$row['name']}\" value=\"{$row['value']}\" $selected><span class=\"{$this->getSubClass($row['name'])}RadioLabel\">{$row['text']}</span></span>\n";
    }
    if ($this->getPageValidator())
    {
			$name = $data['data'][0]['name'];
			$err = $this->getPageValidator()->getError($name);
      if ($err)
      {
        $dataString .= "\n<span class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>{$err}</p></span>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString;
  }
  
  function getRadioYNHTML($data)
  {
  	$selected = null;
    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    $dataString .= "<input type=\"radio\" class=\"".$this->getSubClass($data['name'])."\" name=\"Yes\" value=\"1\" \n";
    $dataString .= "</div></div>\n";
  }
  
  function getSelectYNHTML($data)
  {
  	
  }
  
  /**
   * gets the HTML string for a checkbox set
   * @return string an HTML string containing a checkbox set
   */
  function getCheckboxHTML($data)
  {
    
    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    foreach ($data['data'] as $row)
    {      
      if($row['selected'] == true)
      {
        $selected = 'checked="checked"';      
      } 
      else
      {
        $selected = null;
      }
      ($row['disabled'] == true) ? $disabled = 'disabled="disabled"' : $disabled = null;
      //embed the checkbox and its companion label in a div to keep them together
      $dataString .= "<div class=\"".$this->getSubClass($row['name'])."\">";
      $dataString .= "<input type=\"checkbox\" name=\"{$row['name']}[]\" value=\"{$row['value']}\" $disabled $selected>{$row['text']}";
      $dataString .= "</div>\n";
    }
    if ($this->getPageValidator())
    {
			$name = $data['data'][0]['name'];
			$err = $this->getPageValidator()->getError($name);
      if ($err)
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>{$err}</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString;    
  }
  
  
  /**
   * gets the HTML string for a selection box
   * @return string an HTML string containing a selection box
   */
  function getSelectHTML($data)
  {
    $selectParams = null;
    //add onChange param if requested
    if ($data['submitOnChange'])
    {
      $selectParams .= " onchange=\"this.form.submit();\"";
    }

    if($data['customParams'])
    {
      $selectParams .= ' '.$data['customParams'];
    }
    
    //add size parameter if requested
    if ($data['sizeVisible'])
    {
      $selectParams .= " size=\"{$data['sizeVisible']}\"";
    }
    
    //add mulitple select parameter if requested
    if ($data['multipleSelect'])
    {
      $selectParams .= " multiple";
    }

    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";

    if ($data['multipleSelect'])
    {
      $dataString .= "<select name=\"{$data['name']}[]\" class=\"".$this->getSubClass($data['name'])."\"$selectParams>\n";
    }
    else
    {
      $dataString .= "<select name=\"{$data['name']}\" class=\"".$this->getSubClass($data['name'])."\"$selectParams>\n";
    }
    
    //hide the instructional text if the display is a list box
    if (!isset($data['multipleSelect']) || !isset($data['sizeVisible']))
    {
      $dataString .= "<option value=\"\">$this->selectDefaultMessage</option>\n";
    }
    //TODO: get config option working
    //$dataString .= "<option value=\"\">".$this->getConfig()->get('selectOptionText')."</option>\n";
    
    //checks to make sure there is data going to the foreach loop
    if ($data['data'] != null)
    {
      foreach ($data['data'] as $row)
      { 
        if($row['selected'] == true)
        {
          $selected = 'selected="selected"';
        }
        else
        {
          $selected = null;
        } 
        $dataString .= "<option value=\"{$row['value']}\" $selected/>{$row['text']}</option>\n";
      }
    }
    
    $dataString .= "</select>\n";    
    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString;    
  }
  
  /**
   * gets the HTML string for plain text
   * @return string an HTML string containing plain text
   */
  function getTextFieldHTML($data)
  {
    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\"><div class=\"fieldtext\">{$data['text']}</div></div></div>\n";
    return $dataString;
  }
  
  /**
   * gets the HTML string for a submit button set
   * @return string an HTML string containing a submit button set
   */
  function getSubmitHTML($data)
  {  
    $dataString = "<div class=\"fieldset\"><div class=\"fieldnameempty\">{$data['label']}</div><div class=\"fieldvalueempty\">";
    $dataString .= "<input type=\"submit\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" value=\"{$data['value']}\"/>";
    if ($data['cancel'])
    {
      $dataString .= "<input type=\"button\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}Cancel\" value=\"Cancel\" onClick=\"history.go(-1)\"/>";
    }
    $dataString .= "</div></div>\n";
    return $dataString;    
  }
  
  /**
   * gets the HTML string for a date picker
   * @return string an HTML string containing a date picker
   */
  function getDatePickerHTML($data)
  {        
    $format = $data['format'];
    $formName = "this.form." . $data['name'];
    $dataString .= "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">" 
      ."<link rel=\"stylesheet\" href=\"includes/neolith/js/calendar.css\" media=\"screen\"></link>"
      ."<script type=\"text/javascript\" src=\"includes/neolith/js/calendar.js\"></script>"
      ."<input type=\"text\" class=\"".$this->getSubClass($data['name'])."\" value=\"{$data['value']}\" name=\"{$data['name']}\">"
      ."<input type=\"button\" class=\"".$this->getSubClass($data['name'])."\" value=\"{$data['buttonValue']}\" onclick=\"displayCalendar($formName, '$format',this)\">";
    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString; 
  }  
  
  /**
   * gets the HTML string for a text area
   * @return string an HTML string containing a text area 
   */
  function getTextAreaHTML($data)
  {
  	  $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    $dataString .= "<textarea class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" rows=\"{$data['rows']}\" cols=\"{$data['columns']}\"".($data['readOnly']? ' READONLY':null).">\n"
                ."{$data['value']}"
                ."</textarea>";
    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString; 
  }
  
  /**
   * gets the HTML string for an open file button
   * @return string an HTML string containing a open file button
   */
  function getFileUploadHTML($data)
  {
    $dataString = "<div class=\"fieldset\"><div class=\"fieldname\">{$data['label']}</div><div class=\"fieldvalue\">";
    $dataString .= "<input type=\"file\" class=\"".$this->getSubClass($data['name'])."\" name=\"{$data['name']}\" value=\"{$data['value']}\"/>";
    if ($this->getPageValidator())
    {
      if ($this->getPageValidator()->getError($data['name']))
      {
        $dataString .= "<div class=\"fielderror\"><img src=\"includes/neolith/images/alert.red.gif\"><p>".$this->getPageValidator()->getError($data['name'])."</p></div>";
      }
    }
    $dataString .= "</div></div>\n";
    return $dataString; 
  }
  
  /**
   * sets the default string for select box message
   */
  function setSelectDefaultMessage($string)
  {
    $this->selectDefaultMessage = $string;
  }
  
  /**
   * gets the HTML string for the form
   * @return string an HTML string containing the form
   */
  function getForm()
  {    
    $dataString = null;
    $classString = null;
    $formTagString = null;
    $setenctype = false;
    
    if ($this->getClass())
    {
      $classString = " class=\"".$this->getClass()."\"";
    }
   
    $dataString .= "<div class=\"fieldgroup\">\n";
    
    if ($this->formInputSet == null)
    {
      return false;	
    }
    
    foreach ($this->formInputSet as $row)
    {
      switch($row['type'])
      {        
        case 'text':
          $dataString .= $this->getTextHTML($row);
          break;
        case 'password':
          $dataString .= $this->getPasswordHTML($row);
          break;
        case 'hidden':
          $dataString .= $this->getHiddenHTML($row);
          break;
        case 'radio':
          $dataString .= $this->getRadioHTML($row);
          break;
        case 'checkbox':        
          $dataString .= $this->getCheckboxHTML($row);
          break;
        case 'select':
          $dataString .= $this->getSelectHTML($row);
          break;
        case 'submit':
          $dataString .= $this->getSubmitHTML($row);
          break;
        case 'datepicker':
          $dataString .= $this->getDatePickerHTML($row);
          break;          
        case 'none':
          $dataString .= $this->getTextFieldHTML($row);
          break;
        case 'textarea':
          $dataString .= $this->getTextAreaHTML($row);
          break;
        case 'file':
          $dataString .= $this->getFileUploadHTML($row);
          $setenctype = true;
          break;
        case 'static':
          $dataString .= $row['text'];
          break;
        case 'radioyn':
          $dataString .= $this->getRadioYNHTML($row);
          break;
        case 'selectyn':
          $dataString .= $this->getSelectYNHTML($row);
          break;
        default:
          break;
      }      
    }
    
    //set the target if set
    $target = null;
    if (isset($this->form['target']))
    {
      $target = " target=\"{$this->form['target']}\"";
    }
    
    // if a file upload button is present then the proper enctype is set on the form tag
    if ($setenctype)
    {
    	  $formTagString = "<form id=\"{$this->getId()}\" enctype=\"multipart/form-data\" name=\"".$this->form['name']."\" action=\"".$this->form['action']."\" method=\"".$this->form['method']."\"$classString$target>\n";
    }
    else
    {
    	  $formTagString = "<form id=\"{$this->getId()}\" name=\"".$this->form['name']."\" action=\"".$this->form['action']."\" method=\"".$this->form['method']."\"$classString$target>\n";
    }
    
    $dataString = $formTagString.$dataString;
    $dataString .= "</div>\n";
    $dataString .= "</form>\n";
    return $dataString;    
  }
  
}
?>
