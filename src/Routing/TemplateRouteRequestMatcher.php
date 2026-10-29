<?php

namespace MiniLaravel\Routing;

use RuntimeException;

class TemplateRouteRequestMatcher implements RequestMatcher {
    private TemplatePathRegexCompiler $compiler;
    public string $templatePathRegex;
    private string $method;
    
    public function __construct(TemplatePathRegexCompiler $compiler, string $templatePath, string $method = "GET")
    {
        $this->compiler = $compiler;
        $this->templatePathRegex = $this->compilePath($templatePath);
        $this->method = $method;
    }

    public function match(Request $request) : bool
    {
        if ($request->method() != $this->method)
            return false;
        
        $matches = [];
        $result = preg_match($this->templatePathRegex, $request->path(), $matches);
        $matches = array_filter(
            $matches,
            fn($key) => is_string($key),
            ARRAY_FILTER_USE_KEY
        );

        if ($result === false)
        {
            throw new RuntimeException("Invalid regular expression");
        }

        if ($result == 1)
        {
            $request->putPathVariables($matches);
            return true;
        }
        return false;
    }
    
    private function compilePath($templatePath) : string {
        return "~" . $this->compiler->compile($templatePath) . "~";
    }   
}


