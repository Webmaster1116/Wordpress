<?php

namespace CreativeMail\Exceptions;

use Exception;

class CreativeMailException extends Exception {

    public function __construct ( $message )
    {
        parent::__construct( '[Creative Mail] ' . $message );
    }

}
