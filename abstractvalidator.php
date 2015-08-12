<?

    abstract class AbstractValidator
    {
        var $validator_array;
        var $error_hash;

        function FormValidator()
        {
            $this->validator_array = array();
            $this->error_hash = array();
        }

        function addValidation($variable,$validator,$error)
        {
            $validator_obj = new ValidatorObj();
            $validator_obj->variable_name = $variable;
            $validator_obj->validator_string = $validator;
            $validator_obj->error_string = $error;
            array_push($this->validator_array,$validator_obj);
        }

        function ValidateForm()
        {
            $bret = true;

            $error_string="";
            $error_to_display = "";


            if(strcmp($_SERVER['REQUEST_METHOD'],'POST')==0)
            {
                $form_variables = $_POST;
            }
            else
            {
                $form_variables = $_GET;
            }

            foreach($this->validator_array as $val_obj)
            {
                if(!$this->ValidateObject($val_obj,$form_variables,$error_string))
                {
                    $bret = false;
                    $this->error_hash[$val_obj->variable_name] = $error_string;
                }
            }

            return $bret;
        }

        function ValidateObject($validatorobj,$formvariables,&$error_string)
        {
            $bret = true;

            $splitted = explode("=",$validatorobj->validator_string);
            $command = $splitted[0];
            $command_value = '';

            if(isset($splitted[1]) && strlen($splitted[1])>0)
            {
                $command_value = $splitted[1];
            }

            $default_error_message="";

            $input_value ="";

            if(isset($formvariables[$validatorobj->variable_name]))
            {
                $input_value = $formvariables[$validatorobj->variable_name];
            }

            $bret = $this->ValidateCommand($command,$command_value,$input_value,
                $default_error_message,
                $validatorobj->variable_name,
                $formvariables);


            if(false == $bret)
            {
                if(isset($validatorobj->error_string) &&
                    strlen($validatorobj->error_string)>0)
                {
                    $error_string = $validatorobj->error_string;
                }
                else
                {
                    $error_string = $default_error_message;
                }

            }
            return $bret;
        }

        function validate_req($input_value, &$default_error_message,$variable_name)
        {
            $bret = true;
            if(!isset($input_value) ||
                strlen($input_value) <=0)
            {
                $bret=false;
                $default_error_message = sprintf(E_VAL_REQUIRED_VALUE,$variable_name);
            }
            return $bret;
        }

        function validate_maxlen($input_value,$max_len,$variable_name,&$default_error_message)
        {
            $bret = true;
            if(isset($input_value) )
            {
                $input_length = strlen($input_value);
                if($input_length > $max_len)
                {
                    $bret=false;
                    $default_error_message = sprintf(E_VAL_MAXLEN_EXCEEDED,$variable_name);
                }
            }
            return $bret;
        }

        function validate_minlen($input_value,$min_len,$variable_name,&$default_error_message)
        {
            $bret = true;
            if(isset($input_value) )
            {
                $input_length = strlen($input_value);
                if($input_length < $min_len)
                {
                    $bret=false;
                    $default_error_message = sprintf(E_VAL_MINLEN_CHECK_FAILED,$min_len,$variable_name);
                }
            }
            return $bret;
        }

        function test_datatype($input_value,$reg_exp)
        {
            if(ereg($reg_exp,$input_value))
            {
                return false;
            }
            return true;
        }

        function validate_email($email)
        {
            return eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $email);
        }

        function validate_for_numeric_input($input_value,&$validation_success)
        {

            $more_validations=true;
            $validation_success = true;
            if(strlen($input_value)>0)
            {

                if(false == is_numeric($input_value))
                {
                    $validation_success = false;
                    $more_validations=false;
                }
            }
            else
            {
                $more_validations=false;
            }
            return $more_validations;
        }

        function validate_select($input_value,$command_value,&$default_error_message,$variable_name)
        {
            $bret=false;
            if(is_array($input_value))
            {
                foreach($input_value as $value)
                {
                    if($value == $command_value)
                    {
                        $bret=true;
                        break;
                    }
                }
            }
            else
            {
                if($command_value == $input_value)
                {
                    $bret=true;
                }
            }
            if(false == $bret)
            {
                $default_error_message = sprintf(E_VAL_SHOULD_SEL_CHECK_FAILED,
                    $command_value,$variable_name);
            }
            return $bret;
        }

        function validate_dontselect($input_value,$command_value,&$default_error_message,$variable_name)
        {
            $bret=true;
            if(is_array($input_value))
            {
                foreach($input_value as $value)
                {
                    if($value == $command_value)
                    {
                        $bret=false;
                        $default_error_message = sprintf(E_VAL_DONTSEL_CHECK_FAILED,$variable_name);
                        break;
                    }
                }
            }
            else
            {
                if($command_value == $input_value)
                {
                    $bret=false;
                    $default_error_message = sprintf(E_VAL_DONTSEL_CHECK_FAILED,$variable_name);
                }
            }
            return $bret;
        }

        function ValidateCommand($command,$command_value,$input_value,&$default_error_message,$variable_name,$formvariables)
        {
            $bret = true;
            switch ($command) {
                case 'req': {
                    $bret = $this->validate_req($input_value, $default_error_message, $variable_name);
                    break;
                }

                case 'maxlen': {
                    $max_len = intval($command_value);
                    $bret = $this->validate_maxlen($input_value, $max_len, $variable_name,
                        $default_error_message);
                    break;
                }

                case 'minlen': {
                    $min_len = intval($command_value);
                    $bret = $this->validate_minlen($input_value, $min_len, $variable_name,
                        $default_error_message);
                    break;
                }

                case 'datatype': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z0-9]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALNUM_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

                case 'num':
                case 'numeric': {
                    $bret = $this->test_datatype($input_value, "[^0-9]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_NUM_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

                case 'alpha': {
                    $bret = $this->test_datatype($input_value, "[^A-Za-z]");
                    if (false == $bret) {
                        $default_error_message = sprintf(E_VAL_ALPHA_CHECK_FAILED, $variable_name);
                    }
                    break;
                }

                case 'email': {
                    if (isset($input_value) && strlen($input_value) > 0) {
                        $bret = $this->validate_email($input_value);
                        if (false == $bret) {
                            $default_error_message = E_VAL_EMAIL_CHECK_FAILED;
                        }
                    }
                    break;
                }

                case "dontselect":
                case "dontselectchk":
                case "dontselectradio": {
                    $bret = $this->validate_dontselect($input_value,
                        $command_value,
                        $default_error_message,
                        $variable_name);
                    break;
                }//case

                case "shouldselchk":
                case "selectradio": {
                    $bret = $this->validate_select($input_value,
                        $command_value,
                        $default_error_message,
                        $variable_name);
                    break;
                }//case
            }

            return $bret;
        }

    }