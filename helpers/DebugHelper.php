<?php

namespace app\helpers;

class DebugHelper {

    private static function show($fn, $obj, $isDie) {
        echo "<pre>";
        call_user_func($fn, $obj);
        echo "</pre>";

        if($isDie) {
            die(__METHOD__);
        }
    }


    public static function vd($obj, $isDie=false) {
        self::show("var_dump", $obj, $isDie);
    }

    public static function pr($obj, $isDie=false) {
        self::show("print_r", $obj, $isDie);
    }
}