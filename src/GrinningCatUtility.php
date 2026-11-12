<?php

namespace DataMat\GrinningCat;

class GrinningCatUtility
{
    public static function classize(string $function): string
    {
        return '\\Endpoints\\' . ucfirst($function) . 'Endpoint';
    }

    public static function camelCase(string $s): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $s))));
    }
}
