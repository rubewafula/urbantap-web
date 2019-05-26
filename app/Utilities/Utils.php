<?php  
namespace App\Utilities;

use Illuminate\Support\Facades\Log;

class Utils {

    static function loadTemplateData($template_data, $data){

        $mail_data = array_reduce($data, function ($lastItem, $currentItem) {
            $lastItem = $lastItem ?: array();
            return array_merge($lastItem, array_values($currentItem));
        });

        $patterns = array_keys($mail_data);
        array_walk($patterns, function(&$item){
            $item = '\[\[(\s+)?' . $item . '(\s+)?\]\]';
        });

        return preg_replace($patterns, 
            array_values($mail_data), $template_data);


    }

}

?>