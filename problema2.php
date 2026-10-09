<?php

class A
{
    public static function miFuncion()
    {
        echo __CLASS__;
    }

    public static function otraFuncion()
    {
        self::miFuncion();
    }
}


class B extends A
{
    public static function miFuncion()
    {
        echo __CLASS__;
    }
}


B::otraFuncion();

?>