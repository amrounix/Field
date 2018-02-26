<?php
namespace Field;

use Field\Converter\AddressConverter;
use Field\Converter\AmountConverter;
use Field\Converter\BooleanConverter;
use Field\Converter\ChoiceConverter;
use Field\Converter\DateConverter;
use Field\Converter\DateTimeConverter;
use Field\Converter\EmailConverter;
use Field\Converter\HiddenConverter;
use Field\Converter\ImageConverter;
use Field\Converter\IntegerConverter;
use Field\Converter\MultiChoiceConverter;
use Field\Converter\PasswordConverter;
use Field\Converter\PhoneConverter;
use Field\Converter\PointConverter;
use Field\Converter\SelectConverter;
use Field\Converter\StringConverter;
use Field\Converter\StringNormalizedConverter;
use Field\Converter\TextConverter;
use Field\Converter\TimeConverter;
use Field\Converter\URLConverter;
use Field\Converter\VilleConverter;

class Field
{

    public $id, $seq, $id_table, $col_name, $col_lib, $val_defaut, $col_type, $mandatory, $maxlength, $val_data, $editable, $in_listview;

    private $converter;

    const NOTHING = - 1;

    const STRING = 0;

    const BOOLEAN = 1;

    const SELECT = 2;

    const CHOICE = 3;

    const DATE = 4;

    const PASSWORD = 5;

    const IMAGE_PATH = 6;

    const AMOUNT = 7;

    const TEXT = 8;

    const VILLE = 9;

    const INTEGER = 10;

    const EMAIL = 11;

    const URL = 12;

    const STRING_NORMALIZE = 13;
 // [a-zA-Z0-9_]
    const DATETIME = 14;

    const MULTICHOICE = 15;

    // const DOUBLE=16;
    const POINT = 16;

    const ADDRESS = 17;

    const TIME = 18;

    const COUNTRY = 19;

    const HIDDEN = 20;

    const PHONE = 21;

    public static function getFieldType()
    {
        return "0:STRING,1:BOOLEAN [O/N],2:SELECT,3:CHOICE,4:DATE,5:PASSWORD,6:IMAGE,7:AMOUNT,8:TEXT,9:CITY,10:INTEGER,11:EMAIL,12:URL,13:STRING_NORMALIZED,14:DATE TIME,15:MULTI CHOICE,16:POINT,17:ADDRESS,18:TIME,19:COUNTRY,20:HIDDEN,21:PHONE
		";
    }

    public static function build($col_name, $col_lib, $val_defaut, $col_type, $mandatory, $maxlength, $val_data = "")
    {
        if ($col_type == Field::COUNTRY) {
            $col_type = Field::SELECT;
            if ($val_data == "") {
                $val_data = ":,FR:France,BE:Belgique,GB:Angleterre";
            }
        }
        
        $field = new Field();
        $field->col_name = $col_name;
        $field->col_lib = $col_lib;
        $field->val_defaut = $val_defaut;
        $field->col_type = $col_type;
        $field->mandatory = $mandatory;
        $field->maxlength = $maxlength;
        $field->val_data = $val_data;
        $field->editable = true;
        $field->in_listview = false;
        $field->seq = 0;
        $field->id = $field->col_name . "id";
        
        $field->init();
        
        return $field;
    }

    public function init()
    {
        switch ($this->col_type) {
            case Field::BOOLEAN:
                $this->converter = new BooleanConverter();
                break;
            case Field::DATE:
                $this->converter = new DateConverter();
                break;
            case Field::POINT:
                $this->converter = new PointConverter();
                break;
            case Field::INTEGER:
                $this->converter = new IntegerConverter();
                break;
            case Field::STRING:
                $this->converter = new StringConverter();
                break;
            case Field::EMAIL:
                $this->converter = new EmailConverter();
                break;
            case Field::URL:
                $this->converter = new URLConverter();
                break;
            case Field::STRING_NORMALIZE:
                $this->converter = new StringNormalizedConverter();
                break;
            case Field::SELECT:
                $this->converter = new SelectConverter();
                break;
            case Field::CHOICE:
                $this->converter = new ChoiceConverter();
                break;
            case Field::PASSWORD:
                $this->converter = new PasswordConverter();
                break;
            case Field::IMAGE_PATH:
                $this->converter = new ImageConverter();
                break;
            case Field::DATETIME:
                $this->converter = new DateTimeConverter();
                break;
            case Field::AMOUNT:
                $this->converter = new AmountConverter();
                break;
            case Field::TEXT:
                $this->converter = new TextConverter();
                break;
            case Field::VILLE:
                $this->converter = new VilleConverter();
                break;
            case Field::MULTICHOICE:
                $this->converter = new MultiChoiceConverter();
                break;
            case Field::ADDRESS:
                $this->converter = new AddressConverter();
                break;
            case Field::TIME:
                $this->converter = new TimeConverter();
                break;
            case Field::HIDDEN:
                $this->converter = new HiddenConverter();
                break;
            case Field::PHONE:
                $this->converter = new PhoneConverter();
                break;
        }
    }

    public function toSQL()
    {
        $sql = "`" . $this->col_name . "` " . $this->converter->getSQL();
        if ($this->mandatory) {
            $sql .= " NOT NULL";
        }
        $sql = preg_replace("/\{max\}/", $this->maxlength, $sql);
        return $sql;
    }

    public function toHTML($data, $imgFolder = "")
    {
        $tpl = $this->converter->getTplHTML();
        
        $val = $this->converter->toHTML($data[$this->col_name]);
        
        $htm = preg_replace("/\{name\}/", $this->col_name, $tpl);
        $htm = preg_replace("/\{src\}/", $imgFolder . "/" . $val, $htm);
        $htm = preg_replace("/\{value\}/", $val, $htm);
        
        return $htm;
    }

    public function toTABLE($data, $folderSoc, $imgFolder = "")
    {
        $this->init();
        
        $tpl = $this->converter->getTplTable();
        
        $val = $this->converter->toHTML($data[$this->col_name]);
        
        $htm = preg_replace("/\{name\}/", $this->col_name, $tpl);
        $htm = preg_replace("/\{src\}/", __ROOT__ . $folderSoc . $imgFolder . "/" . $val, $htm);
        $htm = preg_replace("/\{value\}/", $val, $htm);
        
        return $htm;
    }

    function convert($src)
    {
        $this->init();
        
        return $this->converter->fromText($src);
    }

    public function toView($item = null, $folderImg, $code = null)
    {
        $this->init();
        
        $label = $this->col_lib == null ? "" : ("<label for='{id}'>" . $this->col_lib . "</label>");
        
        $data = "";
        
        if ($item != null) {
            if (is_array($item)) {
                $value = $item[$this->col_name];
            } else {
                // $prop = new ReflectionProperty($item, $this->col_name);
                $value = $item->{$this->col_name};
            }
        }
        
        $tpl = $this->converter->getTplHTML();
        
        $value = $this->converter->toHTML($value, $this->val_data);
        
        if ($code == null) {
            $htm = $label;
            $htm .= $tpl;
        } else {
            $htm = preg_replace("/\{label\}/", $label, $code);
            $htm = preg_replace("/\{code\}/", $tpl, $htm);
        }
        
        $htm = preg_replace("/\{name\}/", $this->col_name, $htm);
        $htm = preg_replace("/\{id\}/", $this->id, $htm);
        $htm = preg_replace("/\{value\}/", $value, $htm);
        $htm = preg_replace("/\{folder\}/", $folderImg . "/" . $this->col_name, $htm);
        
        return $htm;
    }

    public function toInput2($item = null, $size = 2, $readonly = false, $form = false, $class = "")
    {
        return $this->toInput($item, "<div class='form-group row'><label class='col-sm-2 col-form-label'>{label}</label><div class='col-sm-" . $size . "'>{code}</div></div>", $readonly, $form, $class);
    }

    public function toInput($item = null, $code = null, $readonly = false, $form = false, $class = "")
    {
        $this->init();
        
        $label = $this->col_lib == null ? "" : ("<label for='{id}'>{lib}</label>");
        
        $data = "";
        
        $value = $this->val_defaut;
        
        if ($form) {
            $value = recup($this->col_name, $value);
        }
        
        if ($item != null) {
            if (is_array($item)) {
                if ($form) {
                    $value = $this->converter->fromText($value);
                    $item[$this->col_name] = $value;
                } else {
                    $value = isset($item->{$this->col_name}) ? $item->{$this->col_name} : $item[$this->col_name];
                }
            } else {
                if ($form) {
                    $value = $this->converter->fromText($value);
                    $col = $this->col_name;
                    $item->{$col} = $value;
                } else {
                    $value = $item->{$this->col_name};
                }
            }
        }
        
        if ($this->editable) {
            $tpl = $this->converter->getTplInput();
        } else {
            $tpl = $this->converter->getTplHTML();
        }
        
        // orignial data
        $data = $this->converter->toHTMLData($value, $this->val_data);
        
        // convert value
        $value = $this->converter->toText($value, $this->val_data);
        
        $maxi = $this->maxlength == null ? 8 : $this->maxlength;
        
        if ($code == null) {
            $htm = $label;
            $htm .= $tpl;
        } else {
            $htm = preg_replace("/\{label\}/", $label, $code);
            $htm = preg_replace("/\{code\}/", $tpl, $htm);
        }
        
        // data in first place for choice and select
        $htm = preg_replace("/\{data\}/", $data, $htm);
        
        $htm = preg_replace("/\{name\}/", $this->col_name, $htm);
        $htm = preg_replace("/\{id\}/", $this->id, $htm);
        $htm = preg_replace("/\{class\}/", $class, $htm);
        $htm = preg_replace("/\{lib\}/", $this->col_lib, $htm);
        $htm = preg_replace("/\{size\}/", $this->maxlength, $htm);
        $htm = preg_replace("/\{value\}/", normalizeHTML($value), $htm);
        $htm = preg_replace("/\{checked\}/", ($value != null && $value == "1") ? "checked" : "", $htm);
        $htm = preg_replace("/\{mandatory\}/", $this->mandatory ? "required" : "", $htm);
        $htm = preg_replace("/\{maxlength\}/", $this->maxlength == null ? "" : ("maxlength='" . $this->maxlength . "'"), $htm);
        $htm = preg_replace("/\{readonly\}/", $readonly ? "reaonly='true'" : "", $htm);
        
        return $htm;
    }

    function getType()
    {
        $this->init();
        return $this->converter->getLib();
    }

    public function getSQLType($addColName = true)
    {
        $this->init();
        
        $sql = ($addColName ? "`" . $this->col_name . "`" : "") . " " . $this->converter->getSQL();
        
        if ($this->mandatory) {
            $sql .= " NOT NULL";
        }
        
        if (trim($this->val_defaut) != "") {
            $sql .= " DEFAULT '" . $this->val_defaut . "'";
        }
        
        $sql = preg_replace("/\{max\}/", $this->maxlength, $sql);
        return $sql;
    }
}

?>