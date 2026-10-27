<?php

namespace MiniLaravel\Routing;

class TemplatePathRegexCompiler {
    public function compile(string $templatePath) : string {
        $regex = preg_replace_callback(
            '~\{(\w+)\}|([^{}]+)~',
            fn($m) => $m[1] !== ''
                ? '(?<' . $m[1] . '>[a-zA-Z_\-0-9]+)'
                : preg_quote($m[2], '~'),
            $templatePath
        );

        return '^' . $regex . '$';
    }


    // public function compile(string $templatePath) : string {
    //     // /user/{user_id}/task/{user_task}
    //     // ^/user/(?<user_id>[a-zA-Z_\-0-9]+)/task/(?<user_task>[a-zA-Z_\-0-9]+)$

    //     $closeBracket = -1;
    //     $regex = "^";
    //     while(true) { 
    //         $openBracket = strpos($templatePath, "{", $closeBracket + 1);
    //         if(!$openBracket){
    //             if ($closeBracket == strlen($templatePath))
    //                 break;
    //             $regex .= substr($templatePath, $closeBracket + 1);
    //             break;
    //         }
    //         $regex .= substr($templatePath, $closeBracket + 1, $openBracket - $closeBracket - 1);
            
    //         $closeBracket = strpos($templatePath, "}", $openBracket);
    //         $variable_name = substr($templatePath, $openBracket + 1, $closeBracket - $openBracket - 1);
    //         echo $variable_name . PHP_EOL;
    //         $regex .= "(?<$variable_name>[a-zA-Z_\-0-9]+)";
    //     }
        
    //     return $regex . "$";
    // }
}