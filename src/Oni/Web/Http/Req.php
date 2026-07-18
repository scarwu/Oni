<?php
declare(strict_types=1);
/**
 * Request
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Http;

class Req
{
    /**
     * @var ?self
     */
    private static ?self $_instance = null;

    /**
     * Initialize
     */
    public static function init(): self
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Method
     *
     * @return string
     */
    public function method(): string
    {
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_SERVER['REQUEST_METHOD'];

        return strtolower($method);
    }

    /**
     * Content Length
     *
     * @return int
     */
    public function contentLength(): int
    {
        return (isset($_SERVER['CONTENT_LENGTH']) && '' !== $_SERVER['CONTENT_LENGTH'])
            ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
    }

    /**
     * Content Type
     *
     * @return string|null
     */
    public function contentType(): ?string
    {
        // Content Type
        //     * text/plain
        //     * multipart/form-data
        //     * multipart/form-data; boundary=----WebKitFormBoundaryKw2qnJFfEWBNPPYK
        //     * application/x-www-form-urlencoded
        //     * application/json
        return (isset($_SERVER['CONTENT_TYPE']) && '' !== $_SERVER['CONTENT_TYPE'])
            ? explode(';', $_SERVER['CONTENT_TYPE'])[0] : null;
    }

    /**
     * Get Protocol
     *
     * @return string
     */
    public function protocol(): string
    {
        return strtolower($_SERVER['SERVER_PROTOCOL']);
    }

    /**
     * Get Scheme
     *
     * @return string
     */
    public function scheme(): string
    {
        if (true === isset($_SERVER['REQUEST_SCHEME'])) {
            return strtolower($_SERVER['REQUEST_SCHEME']);
        }

        return (true === isset($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'])
            ? 'https' : 'http';
    }

    /**
     * Get Host
     *
     * @return string
     */
    public function host(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * Get URI
     *
     * @return string
     */
    public function uri(): string
    {
        $uri = $_SERVER['PATH_INFO']
            ?? (isset($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'])[0] : null);

        return trim((string) $uri, '/');
    }

    /**
     * Body
     *
     * @return string
     */
    public function body(): string
    {
        return (string) file_get_contents('php://input');
    }

    /**
     * Query
     *
     * @return array
     */
    public function query(): array
    {
        return $_GET;
    }

    /**
     * Content
     *
     * @return array<string, mixed>|string|null
     */
    public function content(): array|string|null
    {
        switch ($this->contentType()) {
        case 'application/x-www-form-urlencoded':
        case 'multipart/form-data':
            return $_POST;
        case 'application/json':
            return json_decode($this->body(), true);
        default:
            return $this->body();
        }
    }

    /**
     * File
     *
     * @return array
     */
    public function file(): array
    {
        return $_FILES;
    }

    /**
     * Is Ajax
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH'];
    }
}
