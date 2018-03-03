<?php

namespace Amrounix\Converter;

use \DateTime;
use \DateTimeZone;

abstract class Converter {
	protected static $_SQL="VARCHAR({max})";
	protected static $_HTML="<span class='{name}'>{value}</span>";
	protected static $_INPUT="<div class='input-wrapper'><input id='{id}' type='text' placeholder=\"{lib}\"  name='{name}' value=\"{value}\" {mandatory} {maxlength} {readonly} class='{class}' /></div>";
	protected static $_TABLE="<span class='{name}'>{value}</span>";
	protected static $_LIB = "String";
	
	public static function toText($obj,$valdata=null) {
		return normalizeHTML($obj);
	}
	
	public static function toHTML($obj,$valdata=null) {
		return static::toText($obj,$valdata);
	}

	public static function toHTMLData($obj,$valdata=null) {
		return "";
	}

	public static function fromText($text) {
		return $text;
	}
	
	public static function getSQL() {
		return static::$_SQL;
	}
	
	public static function getLib() {
		return static::$_LIB;
	}
	
	public static function getTplHTML() {
		return static::$_HTML;
	}
	public static function getTplInput() {
		return static::$_INPUT;
	}
	public static function getTplTable() {
		return static::$_TABLE;
	}
}

class StringConverter extends Converter {
	
}

class HiddenConverter extends Converter {
    protected static $_LIB = "Address";
    protected static $_INPUT="<input id='{id}' type='hidden' name='{name}' value=\"{value}\" {mandatory} {maxlength} {readonly}  />";
    
}

class AddressConverter extends Converter {
	protected static $_LIB = "Address";
	protected static $_INPUT="<input id='{id}adresse' type='text' placeholder=\"{lib}\" onFocus='geolocate()' name='{name}' value=\"{value}\" {mandatory} {maxlength} {readonly} style='width:100%' /><input id='{id}coord' name='{name}coord' type='hidden' value=''>";

}

class BooleanConverter extends Converter {
	protected static $_SQL = "TINYINT(1)";
	protected static $_LIB = "Boolean";
	protected static $_INPUT="<input type='checkbox' id='{id}' name='{name}' {checked} {mandatory} {maxlength}  {readonly} value='1'/><label for='{id}'>{lib}</label>";
	
	public static function toText($obj,$valdata=null) {
		return $obj=="1" ? "1":"0";
	}

	public static function toHTML($obj,$valdata=null) {
		return $obj=="1" ? "<i class='fa fa-check-square-o'></i>":"<i class='fa fa-square-o'></i>";
	}

	public static function fromText($text) {
		return $text=="1" ? "1":"0";
	}

}

class AmountConverter extends Converter {
	protected static $_SQL = "FLOAT({max},2)";
	protected static $_LIB = "Amount";
	
	public static function toText($obj,$valdata=null) {
		return $obj;
	}
	
	public static function toHTML($obj,$valdata=null) {
		return $obj."&euro;";
	}	
}

class TimeConverter extends Converter {
    protected static $_SQL = "STRING";
    protected static $_LIB = "time";
    protected static $_INPUT ="<div class='time-wrapper'><input type='text' id='{id}' name='{name}' value='{value}' {readonly} {mandatory} {maxlength} data-format='HH:ii' autocomplete='off' /></div>";
    
    public static function toText($obj,$valdata=null) {
        if ($obj==null || $obj=="") {
            return "";
        }
        
        if ($obj instanceof DateTime) {
            return $obj->format("H:i");
        }
        
        return (new DateTime($obj, new DateTimeZone("Europe/Paris")))->format("H:i");
    }
    
    public static function fromText($text) {
        if ($text==null || $text=="") {
            return "";
        }
        $date = DateTime::createFromFormat("H:i",$text, new DateTimeZone("Europe/Paris"));
        return $date->format("H:i");
    }
    
}


class DateConverter extends Converter {
	protected static $_SQL = "DATE";
	protected static $_LIB = "Date";
	//protected static $_INPUT ="<input class='form-control' type='date' id='{id}' name='{name}' value='{value}' {readonly} {mandatory} {maxlength} data-format='dd/MM/yyyy' autocomplete='off' />";
	protected static $_INPUT ="<div class='date-wrapper'><input type='text' id='{id}' name='{name}' value='{value}' {readonly} {mandatory} {maxlength} data-format='dd/MM/yyyy' autocomplete='off' /></div>";
	
	public static function toText($obj,$valdata=null) {
		if ($obj==null || $obj=="" || $obj=="0000-00-00") {
			return "";
		}
		
        if ($obj instanceof DateTime) {
		    return $obj->format("d/m/Y");
		}
		
		return (new DateTime($obj, new DateTimeZone("Europe/Paris")))->format("d/m/Y");
	}

	public static function fromText($text) {
		if ($text==null || $text=="") {
			return "";
		}
		$date = DateTime::createFromFormat("d/m/Y",$text, new DateTimeZone("Europe/Paris"));
		return $date->format("Y-m-d");
	}
	
}


class DateTimeConverter extends Converter {
	protected static $_SQL = "DATETIME";
	protected static $_LIB = "Date time";
	protected static $_INPUT ="<div class='datetimepickerInput'><input type='text' id='{id}' name='{name}' value='{value}' {readonly} {mandatory} {maxlength} data-format='dd/MM/yyyy' style='width:8em;' /><span class='add-on'><i class='fa fa-calendar'></i></span></div>";

	public static function toText($obj,$valdata=null) {
		if ($obj==null || $obj=="" || $obj=="0000-00-00 00:00") {
			return "";
		}
		return (new DateTime($obj, new DateTimeZone("Europe/Paris")))->format("d/m/Y H:i");
	}
	
	public static function fromText($text) {
		if ($text==null || $text=="") {
			return "";
		}
		$date = DateTime::createFromFormat("d/m/Y H:i",$text, new DateTimeZone("Europe/Paris"));
		return $date->format("Y-m-d H:i");
	}
}

class PointConverter extends Converter {
	protected static $_SQL = "POINT";
	protected static $_LIB = "Point";
	
	public static function toText($obj,$valdata=null) {
		if($obj!=null) {
			
			if (preg_match("/\\d+(.\\d+)*?,\\d+(.\\d+)*?/", $obj)) {
				$coor = explode(",", $obj);
				return $coor[0].",".$coor[1];
				
			} else 
			{			
				$coor = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $obj);
				return $coor["lat"].",".$coor["lon"];
			}
				
		} else {
			return "";
		}
	}
	
	public static function toHTML($obj,$valdata=null) {
		if($obj!=null) {
		$coor = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $obj);
		return $coor["lat"]." / ".$coor["lon"];
		} else {
			return "";
		}
	}
	
}


class IntegerConverter extends Converter {
	protected static $_SQL = "INTEGER({max})";
	protected static $_LIB = "Integer";
}

class SelectConverter extends Converter {
	protected static $_LIB = "Select";
	protected static $_INPUT="<div class='select-wrapper'><select id='{id}' name='{name}' {mandatory} {readonly} >{data}</select></div>";
	
	public static function toText($obj,$valData = null) {
		return data2val($valData, $obj);
	}
	
	public static function toHTMLData($value,$valdata=null) {
		$data="";
		//$chunks = array_chunk(preg_split('/(-|,)/', $valdata), 2);
		//$result = array_combine(array_column($chunks, 0), array_column($chunks, 1));
		$dx = explode(",",$valdata);
		foreach ($dx as $k){
			$x = explode(":",$k);
			$data.="<option value='".$x[0]."'  ".($x[0]==$value?"selected":"")." >".(isset($x[1])?$x[1]:$x[0])."</option>";
		}
		return $data;
	}
	
}

class ChoiceConverter extends Converter {
	protected static $_LIB = "Choice";
	protected static $_INPUT="{data}";
	
	public static function toText($obj,$valData = null) {
	    return data2val($valData, $obj);
	}
	
	public static function toHTMLData($value,$valdata=null) {
	    $data="";
	    //$chunks = array_chunk(preg_split('/(-|,)/', $valdata), 2);
	    //$result = array_combine(array_column($chunks, 0), array_column($chunks, 1));
	    $dx = explode(",",$valdata);
	    foreach ($dx as $k){
	        $x = explode(":",$k);
	        $key = $x[0];
	        $val = isset($x[1])?$x[1]:$x[0];
	        $data.="<input type='radio' id='{name}".$key."' name='{name}' value='".$key."' ".($x[0]==$value?"checked":"")." ><label for='{name}".$key."'>".$val."</label>";
	    }
	    return $data;
	}
}

class PasswordConverter extends Converter {
	protected static $_LIB = "Password";
	protected static $_HTML="<span><i>[Protected]</i></span>";
	protected static $_INPUT="<input id='{id}' type='password' placeholder=\"{lib}\"  name='{name}' value='{value}' {mandatory} {maxlength} {readonly} style='max-width:{size}em;width:100%' />";
	public static function toHTML($obj,$valdata=null) {
		return "<span><i>[Protected]</i></span>";
	}
}

class ImageConverter extends Converter {
	protected static $_LIB = "Image";
	protected static $_HTML="<img src='{src}'/>";
	protected static $_INPUT="<div class='image-editor'><input type='file' class='cropit-image-input' /><a href='#' id='delpic' class='button small'><i class='fa fa-times'></i> Supprimer l'image</a><div class='cropit-image-preview-container'><div class='cropit-image-preview'></div></div><div class='image-size-label'>Redimensionner l'image</div><input type='range' class='cropit-image-zoom-input'><input type='hidden' name='imagedata' class='imagedata'></div><input type='hidden' id='{id}' name='{name}' value='{value}' />";
	protected static $_TABLE="<img src='{src}' style='width:210px;'/>";
}

class TextConverter extends Converter {
	protected static $_SQL= "MEDIUMTEXT";
	protected static $_LIB = "Select";
	protected static $_INPUT = "<textarea id='{id}' placeholder=\"{lib}\"  name='{name}' {mandatory} {maxlength} {readonly} style='width:100%;height:180px;' >{value}</textarea>";
	
	public static function toHTML($obj,$valdata=null) {
		$str= normalizeHTML($obj);
		if (strlen($str) > 200 ) {
			$str = substr( $str, 0, 200)."...";
		}
		return "<span class='{name}'>".$str."</span>";
	}
}

class VilleConverter extends Converter {
	protected static $_LIB = "Select";
}

class EmailConverter extends Converter {
	protected static $_LIB = "Email";
	protected static $_INPUT = "<input id='{id}' data-validation='email' type='text' placeholder=\"{lib}\"  name='{name}' value='{value}' {mandatory} {maxlength} {readonly} style='max-width:{size}em;width:100%' />";
}

class URLConverter extends Converter {
	protected static $_LIB = "URL";
	protected static $_INPUT = "<input id='{id}' data-validation='url' type='text' placeholder=\"{lib}\"  name='{name}' value='{value}' {mandatory} {maxlength} {readonly} style='max-width:{size}em;width:100%' />";
	}

class PhoneConverter extends Converter {
    protected static $_LIB = "Phone";
    protected static $_INPUT = "<input id='{id}' type='text' placeholder=\"{lib}\"  name='{name}' value='{value}' {mandatory} {maxlength} {readonly} style='max-width:{size}em;width:100%' class='phone' />";
}

class StringNormalizedConverter extends Converter {
	protected static $_LIB = "String Normalized";
	protected static $_INPUT = "<input id='{id}' data-validation='alphanumeric' data-validation-allowing='_' type='text' placeholder=\"{lib}\"  name='{name}' value='{value}' {mandatory} {maxlength} {readonly} style='max-width:{size}em;width:100%' />";
	
}


class MultiChoiceConverter extends Converter {
	protected static $_LIB = "MultiChoice";
	protected static $_INPUT ="<span class='{name}'>{data}</span>";
	
	public static function toHTMLData($value,$valdata=null) {
		$data="";
		
		$dx = explode(",",$valdata);
		foreach ($dx as $k){
			$x = explode(":",$k);
			$data.="<input type='checkbox' value='".$x[0]."' ".($x[0]==$value?"checked":"")." />";				
			if ($x[2]) {
				$data.="<i class='fa fa-".$x[2]."'></i>";
			}
		
			$data.="<span style='margin-right:48px;'>".$x[1]."</span>";
		
		}
		
		return $data;
	}
	
	public static function toHTML($value,$valdata=null) {

		if ($valdata==null) {
			return $value;
		}
		$data="";
		$dx = explode(",",$valdata);
		foreach ($dx as $k){
			$x = explode(":",$k);
			if ($x[0]==$value) {
				if ($x[2]) {
					$data.="<i class='fa fa-".$x[2]."'></i>";
				} else {
					$data.=$x[i];						
				}
				
			}
		
			return $data;
			
		}
	}
	
}


?>