<?php

namespace App\Helpers;

class HttpResponseCode{

    // successful response
    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;

    // redirection messages
    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;

    // client side errors
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;

    // server error responses
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const SERVICE_UNAVAILABLE = 503;
}