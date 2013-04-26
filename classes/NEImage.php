<?php
/**
 * Defines the NEImage class to handle image manipulations
 * @package neolith
 * @version $Id: NEImage.php 3361 2007-09-01 16:32:01Z jreed $
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
 * Defines the NEImage class to handle image manipulations
 * @package neolith
 */
class NEImage extends NEFile
{
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
     
  /**
   * resizes image based on specified pixel height and width
   * @param string $filename name of file to resize
   * @param string $destination name of thumbnail file
   * @param int $th_height pixel height of image
   * @param int $th_width pixel width of image
   * @param int $forcefill not quite sure what this does yet
   */
  function resizeImage($filename, $destination, $th_width, $th_height, $forcefill)
  {
    if ($filename == null)
    {
        return false;
    }
    list($width, $height) = getimagesize($filename);

    //returns an array with the image properties
    $extension = getimagesize($filename);
        
    //based on the mime type it calls the proper function to create the image    
    switch ($extension['mime'])
    {
      case 'image/jpeg':
        $source = imagecreatefromjpeg($filename);
        break;
      case 'image/gif':
        $source = imagecreatefromgif($filename);
        break;
      case 'image/png':
        $source = imagecreatefrompng($filename);
        break;
      default:
        return false;
        break;
    }

		$result = true;
		    
    if ($width > $th_width || $height > $th_height)
    {
      $a = $th_width/$th_height;
      $b = $width/$height;

      if (($a > $b)^$forcefill)
      {
        $src_rect_width  = $a * $height;
        $src_rect_height = $height;
        if (!$forcefill)
        {
          $src_rect_width = $width;
          $th_width = $th_height/$height*$width;
        }
      }
      else
      {
        $src_rect_height = $width/$a;
        $src_rect_width  = $width;
        if (!$forcefill)
        {
          $src_rect_height = $height;
          $th_height = $th_width/$width*$height;
        }
      }

      $src_rect_xoffset = ($width - $src_rect_width)/2*intval($forcefill);
      $src_rect_yoffset = ($height - $src_rect_height)/2*intval($forcefill);

      $thumb  = imagecreatetruecolor($th_width, $th_height);

      // Should this method have a switch for imagecopyresized and imagecopyresampled?
      //imagecopyresized($thumb, $source, 0, 0, $src_rect_xoffset, $src_rect_yoffset, $th_width, $th_height, $src_rect_width, $src_rect_height);
      imagecopyresampled($thumb, $source, 0, 0, $src_rect_xoffset, $src_rect_yoffset, $th_width, $th_height, $src_rect_width, $src_rect_height);
      
      //based on the mime type it calls the proper function to create the image
      switch ($extension['mime'])
      {
        case 'image/jpeg':
          $result = imagejpeg($thumb,$destination);
          break;
        case 'image/gif':
          $result = imagegif($thumb,$destination);
          break;
        case 'image/png':
          $result = imagepng($thumb,$destination);
          break;
        default:
        	$result = false;  // should be caught and returned above
        	break;
      }            
    }
    else
    {
    	// original image smaller than desired resize
    	return true;
    }

    return $result;
  }

  /**
   * resizes image based on max width/height while keeping ratio
   * @param string $filename name of file to resize
   * @param string $destination name of thumbnail file
   * @param int $width pixel width of image
   * @param int $height pixel height of image
   * @param int $forcefill not quite sure what this does yet
   */
  function maxLimitResizeImage($filename, $destination, $width = 0, $height = 0, $forcefill)
  {
    // Get cur dimensions
    list($width_orig, $height_orig) = getimagesize($filename);

    $ratio_orig = $width_orig/$height_orig;
    
    //check that both w & h are valued
    if ($width != 0 && $height != 0) 
    {
      if ($width/$height > $ratio_orig) 
      {
        $width = $height*$ratio_orig;
      } 
      
      else 
      {
        $height = $width/$ratio_orig;
      }
    }
    else if ($width == 0)
    {
      //height is resize limit, set width by ratio
      $width = $height*$ratio_orig; 
    }
    else if ($height == 0)
    {
      //width is resize limit, set height by ratio 
      $height = $width/$ratio_orig;
    }
    else
    {
      return false;
    }

    return $this->resizeImage($filename, $destination, $width, $height, $forcefill);
  }
  
  /**
   * creates thumbnail of specified image
   * @param string $filename name of file on server file system
   * @return string name of guid thumb filename 
   */
  function createThumb($filename, $path, $imageHeight = 100, $imageWidth = 120, $keepRatio = false, $sameFilename = false)
  {
    if (($filename == null) || ($path == null))
    {
        return false;
    }
    
    $thumb_filename = ($sameFilename == true) ? $filename : $this->getImageName();

    if ($keepRatio == false)
    {
      $resizeOk = $this->resizeImage($path.$filename, $path.$thumb_filename, $imageWidth, $imageHeight, 0);
    }
    else
    {
      $resizeOk = $this->maxLimitResizeImage($path.$filename, $path.$thumb_filename, $imageWidth, $imageHeight, 0);
      //$resizeOk = $this->resizeImage($path.$filename, $path.$thumb_filename, $imageWidth, $imageHeight, 0);
    }

		// thumb won't exist w/unsupported file type, or if image already smaller than desured thumb size
    if ( !file_exists( $path.$thumb_filename ) )
    {
    	if ( $resizeOk )
			{
				// image was small enough already
				copy($path.$filename, $path.$thumb_filename);
			}
			else
			{
				// copy a "no thumb available" or similar image to $path.$thumb_filename
				copy( "includes/neolith/images/alert.red.gif", $path.$thumb_filename );
				// tbd: NEHtmlForm.php line 622 also hard-codes this filename and path
			}
    }
    return $thumb_filename;          
  }
  
  /**
   * creates a cropped thumbnail of specified image
   * @param string $filename name of file on server file system
   * @param string $path name of file path on server file system
   * @param int $nw the new width of the image
   * @param int $nh the new height of the image
   * @return string name of guid thumb filename 
   */
  function cropImage($filename, $path, $nw, $nh)
  {
    $source = $path.$filename;
    $dest = $this->getImageName();
    
    //returns an array with the image properties
    $size = getimagesize($source);
    $w = $size[0];
    $h = $size[1];
        
    //based on the mime type it calls the proper function to create the image    
    switch ($size['mime'])
    {
      case 'image/gif':
        $simg = imagecreatefromgif($source);
        break;
      case 'image/jpeg':
        $simg = imagecreatefromjpeg($source);
        break;
      case 'image/png':
        $simg = imagecreatefrompng($source);
        break;
    }

    $dimg = imagecreatetruecolor($nw, $nh);

    $wm = $w/$nw;
    $hm = $h/$nh;

    $h_height = $nh/2;
    $w_height = $nw/2;

    if ($w > $h)
    {
      $adjusted_width = $w / $hm;
      $half_width = $adjusted_width / 2;
      $int_width = $half_width - $w_height;

      imagecopyresampled($dimg,$simg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
    } 
    elseif ($w <= $h)
    {
      $adjusted_height = $h / $wm;
      $half_height = $adjusted_height / 2;
      $int_height = $half_height - $h_height;

      imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
    }
    else
    {
      imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
    }

    imagejpeg($dimg, $path.$dest, 100);
    
    return $dest;
  }


	/**
	 * @param string filename to get extension from
	 * @return string the extension portion of the filename
	 */
  function extensionFromFilename($filename)
  {
  	$parts = explode( '.', $filename);
  	return strtolower( array_pop($parts) ); // always use lower-case extensions
  }
 
  /**
   * Adds image from user filesystem to server file system
   * @param string $tmp_name name of file in the tmp directory
   * @return string name of guid filename
   */
  function addImage($tmp_name, $path, $orig_name = null)
  {
    if (($tmp_name == null) || ($path == null))
    {
    	  return false;
    }

    $filename = $this->getImageName($orig_name);
    $this->upload($filename, $tmp_name, $path, 0644);    
    return $filename;                             
  }
  
  /**
   * Generates a GUID and appends the .jpg extention
	 * @param string optional the original filename - used to determind extension of file
   * @return string a GUID for an image with the jpg extension
   */
  function getImageName($origFilename=null)
  {
  		$ext = $origFilename ? $this->extensionFromFilename($origFilename) : 'jpg';
			return $this->guid().'.'.$ext;
  }
  
  /**
   * Gets file size of image
   * @param string $filename filename
   * @param string $filepath path to file relative to site
   * @return int size of file in kilobytes
   */
  function getImageSize($filename, $filepath)
  {
    if (($filename == null) || ($filepath == null))
    {
    	  return false;
    }
    
    //checks to make sure file exist on filesystem before trying to open
    if (file_exists($filepath.$filename) == false)
    {
    	  return false;
    }
  	  
    $bytes = filesize($filepath.$filename);
    return number_format($bytes/1024, 0);
  }
  
  /**
   * Gets image info such as height, width, mime type
   * @param string $filename name of file
   * @param string $filepath path to file relative to site
   * @return array array contains width, height, type, attributes, and mime type
   */
  function getImageInfo($filename, $filepath)
  { 
    if (($filename == null) || ($filepath == null))
    {
        return false;
    }    
    
    if (file_exists($filepath.$filename) == false)
    {
        return false;
    }
    
    $imageData = getimagesize($filepath.$filename);
    $imageSize = $this->getImageSize($filename, $filepath);
    $imageinfo = array('width' => $imageData[0], 'height' => $imageData[1], 'type' => $imageData[2], 'attr' => $imageData[3], 'mime' => $imageData['mime'], 'size' => $imageSize);    
    return $imageinfo;   	    
  }   
  
  /**
   * Deletes specified image from filesystem
   * @param string $filename image filename on filesystem
   * @param string $filepath path to image relative to the site
   * @return boolean true or false depending on success or failure
   */
  function deleteImage($filename, $filepath)
  {
  	  
    if ($this->delete($filename, $filepath) == false)
    {
    	  return false;
    }
    
    return true;      
  }

  function getFilenameFromSuffix($filename, $suffix)
  {
    // filename could have an extension
    if ( preg_match("/^(.*?)(\..*)$/", $filename, $match))
    {
      $file = $match[1] . $suffix . $match[2]; 
    }
    else
    {
      $file = $filename . $suffix;
    }

    return $file;
  }
}
 
?>
