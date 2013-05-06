<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

    public function valid_emails($str)
    {
        $symbol = false;

        $is_comma = strpos($str, ',');

        if ($is_comma) {
            $symbol = ',';
        }

        $is_semicolon = strpos($str, ';');

        if ($is_semicolon) {
            $symbol = ';';
        }

        if ($is_comma && $is_semicolon) {
            return false;
        }

        if (!$is_comma && !$is_semicolon)
        {
            return $this->valid_email(trim($str));
        }

        foreach (explode($symbol, $str) as $email)
        {
            if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
            {
                return FALSE;
            }
        }

        return TRUE;
    }
}