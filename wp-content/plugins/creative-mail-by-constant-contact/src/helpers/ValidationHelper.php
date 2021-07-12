<?php

namespace CreativeMail\Helpers;


class ValidationHelper
{
    public static function is_null_or_empty($value)
    {
        return !isset($value) || empty($value);
    }
}