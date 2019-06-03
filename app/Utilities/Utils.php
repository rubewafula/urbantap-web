<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Log;
use \Exception;

class Utils
{

    static function loadTemplateData($template_data, $data)
    {

        Log::info("Data to Load " . print_r($data, 1));
        $dd = [];
        array_walk($data, function ($value, $key) use (&$dd) {
            Log::info("Last ITEM -Walk $key ==> " . print_r($value, 1));
            if (is_array($value)) {
                array_walk($value, function ($val, $k) use (&$dd) {
                    $dd[$k] = $val;
                });
            } else {
                $dd[$key] = $value;
            }
        });
        Log::info("Last after walking  " . print_r($dd, 1));


        $patterns = array_keys($dd);
        $keys = [];
        array_walk($patterns, function ($v, $k) use (&$keys) {
            $keys[] = '/\[\[(\s+)?' . $v . '(\s+)?\]\]/';
        });

        $html= preg_replace($keys, array_values($dd), $template_data);

        $confirm_rex = "/\[\[(\s\+)?[\w-]+(\s\+)?\]\]/";
        
        if(preg_match_all($confirm_rex, $html)){
           throw new Exception('Email template not properly completed. ');
        }

        return $html;

    }

}

?>
