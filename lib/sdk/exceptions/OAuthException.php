<?php
    class RW_OAuthException extends RW_Exception
    {
        public function __construct($pResult)
        {
            parent::__construct($pResult);
        }
    }