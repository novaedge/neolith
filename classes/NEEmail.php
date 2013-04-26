<?php
/**
 * Defines the NEEmail class
 * @package neolith
 * @version $Id: NEEmail.php 3663 2007-10-23 00:18:38Z gkrupa $
 * @author NovaEdge Technologies LLC
 * @copyright Copyright &copy; 2007, NovaEdge Technologies LLC
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

// This functionality is a wrapper to PEAR's MIME/email classes
require_once('Mail.php');
require_once('Mail/mime.php');
require_once 'Mail/RFC822.php';

/**
 * Defines the NEEmailObject class as base class for email messaging
 * @package neolith
 */
class NEEmailObject extends NEObject
{
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }

	// default EOL string for SMTP
	function eolStr()
	{
		return "\r\n";
	}
    
}
 
/**
 * Defines the NEEmail class for email messaging
 * @package neolith
 */
class NEEmail extends NEEmailObject
{
  function __construct()
  {
    parent::__construct();
    
    $this->headers = array();
    $this->mimer = new Mail_mime( $this->eolStr() );
  }

/*
	function eolStr()
	{
		return "\n";  // if using Pear's Mail to send email, need this -- otherwise use CRLF
	}
*/

	// MUST call bodyAsString() once (and only once) before calling Mail_mime::headers()	
	function headersAsString()
	{
		$this->normalizeHeaders();
		$this->headers = $this->mimer->headers( $this->headers );

		$results = "";
		foreach( $this->headers as $name => $value )
		{
			//skip Subject to prevent duplication
      if ($name != 'Subject')
      {
        $results .= "{$name}: {$value}{$this->eolStr()}";
      }
		}
		
		return $results;
	}
	
	function normalizeHeaders()
	{
		// find missing headers, infer information, etc.
	}
	
	function determineMimeBuildParams()
	{
		return array( 'text_encoding' => 'quoted-printable', 'html_encoding' => 'quoted-printable' );
	}

	function bodyAsString()
	{
		$params = $this->determineMimeBuildParams();
		return $this->mimer->get( $params ); // has to be called before Mail_mime::headers()
	}
	
	function addHeader( $name, $value )
	{
		$name = trim( rtrim($name, " :\n\r\t")); // trim leading/trailing whitespace and  trailing colon
		$this->headers[$name] = $value;
	}
	
	function addHeaders( $ar )
	{
		foreach( $ar as $name => $value )
		{
			$this->addHeader( $name, $value );
		}
	}

	function addBody( $str )
	{
		$this->mimer->setTXTBody( $str );
	}
	
	function addHtmlBody( $str )
	{
		$this->mimer->setHTMLBody( $str );
	}

	// filename: path to file to attach (used to get file from local file system)
	// name: what to list as this attachment's 'name' and 'filename' in message -- defaults to file name (sans path) of $filename
	function attachFile( $filename, $content_type = 'application/octet-stream', $name = '' )
	{
		return $this->addAttachment( $filename, $content_type, $name, true );
	}

	function addAttachment( $dataOrFilename, $content_type = 'application/octet-stream', $name = '', $isFilename = false )
	{
		$this->mimer->addAttachment( $dataOrFilename, $content_type, $name, $isFilename );
	}

	function parseEmailList( $str )
	{
		return Mail::parseRecipients( $str );
	}

	function recipients()
	{
		$results = array();

		foreach( $this->headers as $name => $value )
		{
			$hdr = strtolower( $name );
			
			if ( 'to' == $hdr || 'cc' == $hdr || 'bcc' == $hdr )
			{
				$addrs = $this->parseEmailList($value);
				foreach( $addrs as $addr )
				{
					array_push( $results, $addr );
				}
			}
		}

		return $results;
	}

	// returns false if message was not sent/passed to MTA for each recipient
	// unless $returnOnly set -- then returns the ASCII string of the msg
	function sendTo( $toAddress = null, $debugPrint = false, $returnOnly = false )
	{
		// MUST be called in this order
		$body = $this->bodyAsString();
		$headers = $this->headersAsString();

#		$recipients = $this->recipients();
#
#		if ( 0 == count($recipients) )
#		{
#			return false; // why send if not sending to anyone?
#		}

		$result = true;

		if ( $debugPrint || $returnOnly )
		{
			$result = $headers . $this->eolStr() . $body;
			if ( $debugPrint )
			{
				echo htmlentities( $result );
			}
		}

		if ( $returnOnly )
		{
			return $result;  # do not send message -- just print; no message sent
		}

		# PHP mail() passes to sendmail which reads headers --
		#  so we do not want to loop thru recipients; let sendmail do the heavy lifting
		$result = mail( $toAddress, $this->headers['Subject'], $body, $headers );
		return $result;
	}

	// convenience function meant to be called statically on class:
	// NEEmail::sendEx( array( 'to' => 'nobody@null.com', 'from' => '"Project Mailer" <mailer@novaedge.com>',
	//									'subject' => 'Welcome to the Project', 'txtBody' => $str, 'htmlBody' => $htmlStr ) );
	// all array keys are case insensitive.
	function sendEx( $ar )
	{
		$email = new NEEmail;
		
		$debug = false;
		$returnOnly = false;
		$result = false;
		
		$mainRecipients = null;
		
		foreach( $ar as $key => $value )
		{
			$key = strtolower( $key );
			if ( 'to' == $key )
			{
				$mainRecipients = $value;
				//$email->addHeader( 'To', $value );  # not needed since PHP's mail() function passes this to sendmail, which adds the "To:" header
			}
			else if ( 'from' == $key )
			{
				$email->addHeader( 'From', $value );
			}
			else if ( 'cc' == $key )
			{
				$email->addHeader('Cc', $value );
			}
			else if ( 'bcc' == $key )
			{
				$email->addHeader('Bcc', $value );
			}
			else if ( 'subject' == $key )
			{
				$email->addHeader('Subject', $value );
			}
			else if ( 'txtbody' == $key  || 'textbody' == $key || 'body' == $key )
			{
				$email->addBody( $value );
			}
			else if ( 'htmlbody' == $key )
			{
				$email->addHtmlBody( $value );
			}
			else if ( 'debug' == $key )
			{
				$debug = $value;
			}
			else if ( 'return_only' == $key )
			{
				$returnOnly = $value;
			}
			else
			{
				// unknown key
				// tbd: add attachment type?  or maybe an 'image' and 'file' type to be more specific
			}
		}

		$result = $email->sendTo( $mainRecipients, $debug, $returnOnly );

		return $result;
	}

	var $mimer;
  var $headers;  
}
 
?>