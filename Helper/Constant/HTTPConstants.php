<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Helper\Constant;

class HTTPConstants
{
    /**
     * User agent
     */
    public const USERAGENT = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)";

    /**
     * Statuses
     */
    // Informationals
    public const StatusTypes100 = 'ST100';
    public const Status100Continue = 100;
    public const Status101SwitchingProtocols = 101;
    public const Status102Processing = 102;
    public const Status103EarlyHints = 103;

    // Success
    public const StatusTypes200 = 'ST200';
    public const Status200OK = 200;
    public const Status201Created = 201;
    public const Status202Accepted = 202;
    public const Status203NonAuthoritativeInformation = 203;
    public const Status204NoContent = 204;
    public const Status205ResetContent = 205;
    public const Status206PartialContent = 206;
    public const Status207MultiStatus = 207;
    public const Status208AlreadyReported = 208;
    public const Status226IMUsed = 226;

    // Redirections
    public const StatusTypes300 = 'ST300';
    public const Status300MultipleChoices = 300;
    public const Status301MovedPermanently = 301;
    public const Status302MovedTemporarily = 302;
    public const Status303SeeOther = 303;
    public const Status304NotModified = 304;
    public const Status305UseProxy = 305;
    public const Status306Unused = 306;
    public const Status307TemporaryRedirect = 307;
    public const Status308PermanentRedirect = 308;

    // Client Errors
    public const StatusTypes400 = 'ST400';
    public const Status400BadRequest = 400;
    public const Status401Unauthorized = 401;
    public const Status402PaymentRequired = 402;
    public const Status403Forbidden = 403;
    public const Status404NotFound = 404;
    public const Status405MethodNotAllowed = 405;
    public const Status406NotAcceptable = 406;
    public const Status407ProxyAuthenticationRequired = 407;
    public const Status408RequestTimeout = 408;
    public const Status409Conflict = 409;
    public const Status410Gone = 410;
    public const Status411LengthRequired = 411;
    public const Status412PreconditionFailed = 412;
    public const Status413PayloadTooLarge = 413;
    public const Status414URITooLong = 414;
    public const Status415UnsupportedMediaType = 415;
    public const Status416RangeNotSatisfiable = 416;
    public const Status417ExpectationFailed = 417;
    public const Status418ImATeapot = 418;
    public const Status421MisdirectedRequest = 421;
    public const Status422UnprocessableContent = 422;
    public const Status423Locked = 423;
    public const Status424FailedDependency = 424;
    public const Status425TooEarly = 425;
    public const Status426UpgradeRequired = 426;
    public const Status428PreconditionRequired = 428;
    public const Status429TooManyRequests = 429;
    public const Status431RequestHeaderFieldsTooLarge = 431;
    public const Status451UnavailableForLegalReasons = 451;

    // Server Errors
    public const StatusTypes500 = 'ST500';
    public const Status500InternalServerError = 500;
    public const Status501NotImplemented = 501;
    public const Status502BadGateway = 502;
    public const Status503ServiceUnavailable = 503;
    public const Status504GatewayTimeout = 504;
    public const Status505HTTPVersionNotSupported = 505;
    public const Status506VariantAlsoNegotiates = 506;
    public const Status507InsufficientStorage = 507;
    public const Status508LoopDetected = 508;
    public const Status510NotExtended = 510;
    public const Status511NetworkAuthenticationRequired = 511;

    /**
     * @var array
     */
    public const STATUSES = [
        // Informational
        self::StatusTypes100 => ['name' => 'Types 100', 'type' => 'ST100'],
        self::Status100Continue => ['name' => 'Continue', 'type' => 'ST100'],
        self::Status101SwitchingProtocols => ['name' => 'Switching Protocols', 'type' => 'ST100'],
        self::Status102Processing => ['name' => 'Processing', 'type' => 'ST100'],
        self::Status103EarlyHints => ['name' => 'Early Hints', 'type' => 'ST100'],
        // Success
        self::StatusTypes200 => ['name' => 'Types 200', 'type' => 'ST200'],
        self::Status200OK => ['name' => 'OK', 'type' => 'ST200'],
        self::Status201Created => ['name' => 'Created', 'type' => 'ST200'],
        self::Status202Accepted => ['name' => 'Accepted', 'type' => 'ST200'],
        self::Status203NonAuthoritativeInformation => ['name' => 'Non-Authoritative Information', 'type' => 'ST200'],
        self::Status204NoContent => ['name' => 'No Content', 'type' => 'ST200'],
        self::Status205ResetContent => ['name' => 'Reset Content', 'type' => 'ST200'],
        self::Status206PartialContent => ['name' => 'Partial Content', 'type' => 'ST200'],
        self::Status207MultiStatus => ['name' => 'Multi-Status', 'type' => 'ST200'],
        self::Status208AlreadyReported => ['name' => 'Already Reported', 'type' => 'ST200'],
        self::Status226IMUsed => ['name' => 'IM Used', 'type' => 'ST200'],
        // Redirections
        self::StatusTypes300 => ['name' => 'Types 300', 'type' => 'ST300'],
        self::Status300MultipleChoices => ['name' => 'Multiple Choices', 'type' => 'ST300'],
        self::Status301MovedPermanently => ['name' => 'Moved Permanently', 'type' => 'ST300'],
        self::Status302MovedTemporarily => ['name' => 'Moved Temporarily', 'type' => 'ST300'],
        self::Status303SeeOther => ['name' => 'See Other', 'type' => 'ST300'],
        self::Status304NotModified => ['name' => 'Not Modified', 'type' => 'ST300'],
        self::Status305UseProxy => ['name' => 'Use Proxy', 'type' => 'ST300'],
        self::Status306Unused => ['name' => 'Unused', 'type' => 'ST300'],
        self::Status307TemporaryRedirect => ['name' => 'Temporary Redirect', 'type' => 'ST300'],
        self::Status308PermanentRedirect => ['name' => 'Permanent Redirect', 'type' => 'ST300'],
        // Client Errors
        self::StatusTypes400 => ['name' => 'Types 400', 'type' => 'ST400'],
        self::Status400BadRequest => ['name' => 'Bad sRequest', 'type' => 'ST400'],
        self::Status401Unauthorized => ['name' => 'Unauthorized', 'type' => 'ST400'],
        self::Status402PaymentRequired => ['name' => 'Payment Required', 'type' => 'ST400'],
        self::Status403Forbidden => ['name' => 'Forbidden', 'type' => 'ST400'],
        self::Status404NotFound => ['name' => 'Not Found', 'type' => 'ST400'],
        self::Status405MethodNotAllowed => ['name' => 'Method Not Allowed', 'type' => 'ST400'],
        self::Status406NotAcceptable => ['name' => 'Not Acceptable', 'type' => 'ST400'],
        self::Status407ProxyAuthenticationRequired => ['name' => 'Proxy Authentication Required', 'type' => 'ST400'],
        self::Status408RequestTimeout => ['name' => 'Request Timeout', 'type' => 'ST400'],
        self::Status409Conflict => ['name' => 'Conflict', 'type' => 'ST400'],
        self::Status410Gone => ['name' => 'Gone', 'type' => 'ST400'],
        self::Status411LengthRequired => ['name' => 'Length Required', 'type' => 'ST400'],
        self::Status412PreconditionFailed => ['name' => 'Precondition Failed', 'type' => 'ST400'],
        self::Status413PayloadTooLarge => ['name' => 'Payload Too Large', 'type' => 'ST400'],
        self::Status414URITooLong => ['name' => 'URI Too Long', 'type' => 'ST400'],
        self::Status415UnsupportedMediaType => ['name' => 'Unsupported Media Type', 'type' => 'ST400'],
        self::Status416RangeNotSatisfiable => ['name' => 'Range Not Satisfiable', 'type' => 'ST400'],
        self::Status417ExpectationFailed => ['name' => 'Expectation Failed', 'type' => 'ST400'],
        self::Status418ImATeapot => ['name' => 'I am a teapot', 'type' => 'ST400'],
        self::Status421MisdirectedRequest => ['name' => 'Misdirected Request', 'type' => 'ST400'],
        self::Status422UnprocessableContent => ['name' => 'Unprocessable Content', 'type' => 'ST400'],
        self::Status423Locked => ['name' => 'Locked', 'type' => 'ST400'],
        self::Status424FailedDependency => ['name' => 'Failed Dependency', 'type' => 'ST400'],
        self::Status425TooEarly => ['name' => 'Too Early', 'type' => 'ST400'],
        self::Status426UpgradeRequired => ['name' => 'Upgrade Required', 'type' => 'ST400'],
        self::Status428PreconditionRequired => ['name' => 'Precondition Required', 'type' => 'ST400'],
        self::Status429TooManyRequests => ['name' => 'Too Many Requests', 'type' => 'ST400'],
        self::Status431RequestHeaderFieldsTooLarge => ['name' => 'Request Header Fields Too Large', 'type' => 'ST400'],
        self::Status451UnavailableForLegalReasons => ['name' => 'Unavailable For Legal Reasons', 'type' => 'ST400'],
        // Server Errors
        self::StatusTypes500 => ['name' => 'Types 500', 'type' => 'ST500'],
        self::Status500InternalServerError => ['name' => 'Internal Server Error', 'type' => 'ST500'],
        self::Status501NotImplemented => ['name' => 'Not Implemented', 'type' => 'ST500'],
        self::Status502BadGateway => ['name' => 'Bad Gateway', 'type' => 'ST500'],
        self::Status503ServiceUnavailable => ['name' => 'Service Unavailable', 'type' => 'ST500'],
        self::Status504GatewayTimeout => ['name' => 'Gateway Timeout', 'type' => 'ST500'],
        self::Status505HTTPVersionNotSupported => ['name' => 'HTTP Version Not Supported', 'type' => 'ST500'],
        self::Status506VariantAlsoNegotiates => ['name' => 'Variant Also Negotiates', 'type' => 'ST500'],
        self::Status507InsufficientStorage => ['name' => 'Insufficient Storage', 'type' => 'ST500'],
        self::Status508LoopDetected => ['name' => 'Loop Detected', 'type' => 'ST500'],
        self::Status510NotExtended => ['name' => 'Not Extended', 'type' => 'ST500'],
        self::Status511NetworkAuthenticationRequired => ['name' => 'Network Authentication Required', 'type' => 'ST500'],
    ];

    /**
     * @var array
     */
    public $loc_constants = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        foreach (self::STATUSES as $k => $v) {
            if (is_numeric($k)) {
                $key = 'NF.Status.' . str_replace(' ', '', $v['name']);
                $constant_name = '\NF\Status::Status' . $k . (new \String2($v['name']))->englishOnly()->toString();
                $this->loc_constants[$constant_name] = [$key => $v['name'], 'http_status_code' => $k];
            } else {
                $key = 'NF.Status.' . str_replace(' ', '', $v['name']);
                $constant_name = '\NF\Status::StatusTypes' . str_replace('ST', '', $k);
                $this->loc_constants[$constant_name] = [$key => $v['name'], 'http_status_code' => $k];
            }
        }
    }
}
