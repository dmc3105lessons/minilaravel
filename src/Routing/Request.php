<?php

namespace MiniLaravel\Routing;

class Request {
    public function method() : string {
        return $_SERVER["REQUEST_METHOD"] ?? "GET";
    }

    public function path() : string {
        return parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH) ? : "/";
    }

    public function query(?string $key) : mixed
    {
        return $key === null ? $_GET : ($_GET[$key] ?? null);
    }

    public function input(?string $key = null)
    {
        $data = json_decode(file_get_contents("php://input", true), true) ?? [];
        array_replace($_POST, $data);
        return $key === null ? $data : ($data[$key] ?? null);
    }
}