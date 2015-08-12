<?

class FormValidator
{
    public static $regexes = Array(
        'date' => "^[0-9]{4}[-/][0-9]{1,2}[-/][0-9]{1,2}\$",
        'number' => "^[-]?[0-9,]+\$",
        'alfanum' => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
        'not_empty' => "[a-z0-9A-Z]+",
        'words' => "^[A-Za-z]+[A-Za-z \\s]*\$",
        'phone' => "^[0-9]{10,11}\$",
    );
    private $validations, $errors, $corrects, $fields;


    public function __construct($validations=array())
    {
        $this->validations = $validations;
        $this->errors = array();
        $this->corrects = array();
    }

    public function validate($items)
    {
        $this->fields = $items;
        $havefailures = false;
        foreach($items as $key=>$val)
        {
            if((strlen($val) == 0 || array_search($key, $this->validations) === false))
            {
                $this->corrects[] = $key;
                continue;
            }
            $result = self::validateItem($val, $this->validations[$key]);
            if($result === false) {
                $havefailures = true;
                $this->addError($key, $this->validations[$key]);
            }
            else
            {
                $this->corrects[] = $key;
            }
        }

        return(!$havefailures);
    }

    private function addError($field, $type='string')
    {
        $this->errors[$field] = $type;
    }

    public static function validateItem($var, $type)
    {
        if(array_key_exists($type, self::$regexes))
        {
            $returnval =  filter_var($var, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>'!'.self::$regexes[$type].'!i'))) !== false;
            return($returnval);
        }
        $filter = false;
        switch($type)
        {
            case 'email':
                $var = substr($var, 0, 254);
                $filter = FILTER_VALIDATE_EMAIL;
                break;
            case 'int':
                $filter = FILTER_VALIDATE_INT;
                break;
            case 'boolean':
                $filter = FILTER_VALIDATE_BOOLEAN;
                break;
            case 'ip':
                $filter = FILTER_VALIDATE_IP;
                break;
            case 'url':
                $filter = FILTER_VALIDATE_URL;
                break;
        }
        return ($filter === false) ? false : filter_var($var, $filter) !== false ? true : false;
    }



}