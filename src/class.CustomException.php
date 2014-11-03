<?php

/**
 *
 */
class CustomException extends ErrorException
{
    /**
     * @param $code
     * @param $string
     * @param $file
     * @param $line
     *
     * @throws CustomException
     */
    public static function errorHandlerCallback($code, $string, $file, $line /*, $context*/)
    {
        $oException = new self($string, $code);
        $oException->line = $line;
        $oException->file = $file;

        throw $oException;
    }
}

