<?php

namespace MiniLaravel\Routing;

interface RequestMatcher {
    public function match(Request $request) : bool;
}