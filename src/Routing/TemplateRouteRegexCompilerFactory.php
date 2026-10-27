<?php

namespace MiniLaravel\Routing;

class TemplateRouteRegexCompilerFactory
{
    public function __construct(
        private TemplatePathRegexCompiler $compiler
    ) {}

    public function createMatcher(string $templateRoute, string $method = "GET")
    {
        return new TemplateRouteRequestMatcher($this->compiler, $templateRoute, $method);
    }
}
