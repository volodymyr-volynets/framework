<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace NF;

use Object\Content\LocalizationConstants;

class Status extends LocalizationConstants
{
    public static $prefix = 'NF.ST.';
    public const Status100Continue = ['NF.Status.Continue' => 'Continue','http_status_code' => 100,'errno' => 'NF.ST.0002'];
    public const Status101SwitchingProtocols = ['NF.Status.SwitchingProtocols' => 'Switching Protocols','http_status_code' => 101,'errno' => 'NF.ST.0003'];
    public const Status102Processing = ['NF.Status.Processing' => 'Processing','http_status_code' => 102,'errno' => 'NF.ST.0004'];
    public const Status103EarlyHints = ['NF.Status.EarlyHints' => 'Early Hints','http_status_code' => 103,'errno' => 'NF.ST.0005'];
    public const Status200OK = ['NF.Status.OK' => 'OK','http_status_code' => 200,'errno' => 'NF.ST.0007'];
    public const Status201Created = ['NF.Status.Created' => 'Created','http_status_code' => 201,'errno' => 'NF.ST.0008'];
    public const Status202Accepted = ['NF.Status.Accepted' => 'Accepted','http_status_code' => 202,'errno' => 'NF.ST.0009'];
    public const Status203NonAuthoritativeInformation = ['NF.Status.Non-AuthoritativeInformation' => 'Non-Authoritative Information','http_status_code' => 203,'errno' => 'NF.ST.0010'];
    public const Status204NoContent = ['NF.Status.NoContent' => 'No Content','http_status_code' => 204,'errno' => 'NF.ST.0011'];
    public const Status205ResetContent = ['NF.Status.ResetContent' => 'Reset Content','http_status_code' => 205,'errno' => 'NF.ST.0012'];
    public const Status206PartialContent = ['NF.Status.PartialContent' => 'Partial Content','http_status_code' => 206,'errno' => 'NF.ST.0013'];
    public const Status207MultiStatus = ['NF.Status.Multi-Status' => 'Multi-Status','http_status_code' => 207,'errno' => 'NF.ST.0014'];
    public const Status208AlreadyReported = ['NF.Status.AlreadyReported' => 'Already Reported','http_status_code' => 208,'errno' => 'NF.ST.0015'];
    public const Status226IMUsed = ['NF.Status.IMUsed' => 'IM Used','http_status_code' => 226,'errno' => 'NF.ST.0016'];
    public const Status300MultipleChoices = ['NF.Status.MultipleChoices' => 'Multiple Choices','http_status_code' => 300,'errno' => 'NF.ST.0018'];
    public const Status301MovedPermanently = ['NF.Status.MovedPermanently' => 'Moved Permanently','http_status_code' => 301,'errno' => 'NF.ST.0019'];
    public const Status302MovedTemporarily = ['NF.Status.MovedTemporarily' => 'Moved Temporarily','http_status_code' => 302,'errno' => 'NF.ST.0020'];
    public const Status303SeeOther = ['NF.Status.SeeOther' => 'See Other','http_status_code' => 303,'errno' => 'NF.ST.0021'];
    public const Status304NotModified = ['NF.Status.NotModified' => 'Not Modified','http_status_code' => 304,'errno' => 'NF.ST.0022'];
    public const Status305UseProxy = ['NF.Status.UseProxy' => 'Use Proxy','http_status_code' => 305,'errno' => 'NF.ST.0023'];
    public const Status306Unused = ['NF.Status.Unused' => 'Unused','http_status_code' => 306,'errno' => 'NF.ST.0024'];
    public const Status307TemporaryRedirect = ['NF.Status.TemporaryRedirect' => 'Temporary Redirect','http_status_code' => 307,'errno' => 'NF.ST.0025'];
    public const Status308PermanentRedirect = ['NF.Status.PermanentRedirect' => 'Permanent Redirect','http_status_code' => 308,'errno' => 'NF.ST.0026'];
    public const Status400BadSRequest = ['NF.Status.BadsRequest' => 'Bad sRequest','http_status_code' => 400,'errno' => 'NF.ST.0028'];
    public const Status401Unauthorized = ['NF.Status.Unauthorized' => 'Unauthorized','http_status_code' => 401,'errno' => 'NF.ST.0029'];
    public const Status402PaymentRequired = ['NF.Status.PaymentRequired' => 'Payment Required','http_status_code' => 402,'errno' => 'NF.ST.0030'];
    public const Status403Forbidden = ['NF.Status.Forbidden' => 'Forbidden','http_status_code' => 403,'errno' => 'NF.ST.0031'];
    public const Status404NotFound = ['NF.Status.NotFound' => 'Not Found','http_status_code' => 404,'errno' => 'NF.ST.0032'];
    public const Status405MethodNotAllowed = ['NF.Status.MethodNotAllowed' => 'Method Not Allowed','http_status_code' => 405,'errno' => 'NF.ST.0033'];
    public const Status406NotAcceptable = ['NF.Status.NotAcceptable' => 'Not Acceptable','http_status_code' => 406,'errno' => 'NF.ST.0034'];
    public const Status407ProxyAuthenticationRequired = ['NF.Status.ProxyAuthenticationRequired' => 'Proxy Authentication Required','http_status_code' => 407,'errno' => 'NF.ST.0035'];
    public const Status408RequestTimeout = ['NF.Status.RequestTimeout' => 'Request Timeout','http_status_code' => 408,'errno' => 'NF.ST.0036'];
    public const Status409Conflict = ['NF.Status.Conflict' => 'Conflict','http_status_code' => 409,'errno' => 'NF.ST.0037'];
    public const Status410Gone = ['NF.Status.Gone' => 'Gone','http_status_code' => 410,'errno' => 'NF.ST.0038'];
    public const Status411LengthRequired = ['NF.Status.LengthRequired' => 'Length Required','http_status_code' => 411,'errno' => 'NF.ST.0039'];
    public const Status412PreconditionFailed = ['NF.Status.PreconditionFailed' => 'Precondition Failed','http_status_code' => 412,'errno' => 'NF.ST.0040'];
    public const Status413PayloadTooLarge = ['NF.Status.PayloadTooLarge' => 'Payload Too Large','http_status_code' => 413,'errno' => 'NF.ST.0041'];
    public const Status414URITooLong = ['NF.Status.URITooLong' => 'URI Too Long','http_status_code' => 414,'errno' => 'NF.ST.0042'];
    public const Status415UnsupportedMediaType = ['NF.Status.UnsupportedMediaType' => 'Unsupported Media Type','http_status_code' => 415,'errno' => 'NF.ST.0043'];
    public const Status416RangeNotSatisfiable = ['NF.Status.RangeNotSatisfiable' => 'Range Not Satisfiable','http_status_code' => 416,'errno' => 'NF.ST.0044'];
    public const Status417ExpectationFailed = ['NF.Status.ExpectationFailed' => 'Expectation Failed','http_status_code' => 417,'errno' => 'NF.ST.0045'];
    public const Status418IAmATeapot = ['NF.Status.Iamateapot' => 'I am a teapot','http_status_code' => 418,'errno' => 'NF.ST.0046'];
    public const Status421MisdirectedRequest = ['NF.Status.MisdirectedRequest' => 'Misdirected Request','http_status_code' => 421,'errno' => 'NF.ST.0047'];
    public const Status422UnprocessableContent = ['NF.Status.UnprocessableContent' => 'Unprocessable Content','http_status_code' => 422,'errno' => 'NF.ST.0048'];
    public const Status423Locked = ['NF.Status.Locked' => 'Locked','http_status_code' => 423,'errno' => 'NF.ST.0049'];
    public const Status424FailedDependency = ['NF.Status.FailedDependency' => 'Failed Dependency','http_status_code' => 424,'errno' => 'NF.ST.0050'];
    public const Status425TooEarly = ['NF.Status.TooEarly' => 'Too Early','http_status_code' => 425,'errno' => 'NF.ST.0051'];
    public const Status426UpgradeRequired = ['NF.Status.UpgradeRequired' => 'Upgrade Required','http_status_code' => 426,'errno' => 'NF.ST.0052'];
    public const Status428PreconditionRequired = ['NF.Status.PreconditionRequired' => 'Precondition Required','http_status_code' => 428,'errno' => 'NF.ST.0053'];
    public const Status429TooManyRequests = ['NF.Status.TooManyRequests' => 'Too Many Requests','http_status_code' => 429,'errno' => 'NF.ST.0054'];
    public const Status431RequestHeaderFieldsTooLarge = ['NF.Status.RequestHeaderFieldsTooLarge' => 'Request Header Fields Too Large','http_status_code' => 431,'errno' => 'NF.ST.0055'];
    public const Status451UnavailableForLegalReasons = ['NF.Status.UnavailableForLegalReasons' => 'Unavailable For Legal Reasons','http_status_code' => 451,'errno' => 'NF.ST.0056'];
    public const Status500InternalServerError = ['NF.Status.InternalServerError' => 'Internal Server Error','http_status_code' => 500,'errno' => 'NF.ST.0058'];
    public const Status501NotImplemented = ['NF.Status.NotImplemented' => 'Not Implemented','http_status_code' => 501,'errno' => 'NF.ST.0059'];
    public const Status502BadGateway = ['NF.Status.BadGateway' => 'Bad Gateway','http_status_code' => 502,'errno' => 'NF.ST.0060'];
    public const Status503ServiceUnavailable = ['NF.Status.ServiceUnavailable' => 'Service Unavailable','http_status_code' => 503,'errno' => 'NF.ST.0061'];
    public const Status504GatewayTimeout = ['NF.Status.GatewayTimeout' => 'Gateway Timeout','http_status_code' => 504,'errno' => 'NF.ST.0062'];
    public const Status505HTTPVersionNotSupported = ['NF.Status.HTTPVersionNotSupported' => 'HTTP Version Not Supported','http_status_code' => 505,'errno' => 'NF.ST.0063'];
    public const Status506VariantAlsoNegotiates = ['NF.Status.VariantAlsoNegotiates' => 'Variant Also Negotiates','http_status_code' => 506,'errno' => 'NF.ST.0064'];
    public const Status507InsufficientStorage = ['NF.Status.InsufficientStorage' => 'Insufficient Storage','http_status_code' => 507,'errno' => 'NF.ST.0065'];
    public const Status508LoopDetected = ['NF.Status.LoopDetected' => 'Loop Detected','http_status_code' => 508,'errno' => 'NF.ST.0066'];
    public const Status510NotExtended = ['NF.Status.NotExtended' => 'Not Extended','http_status_code' => 510,'errno' => 'NF.ST.0067'];
    public const Status511NetworkAuthenticationRequired = ['NF.Status.NetworkAuthenticationRequired' => 'Network Authentication Required','http_status_code' => 511,'errno' => 'NF.ST.0068'];
    public const StatusTypes100 = ['NF.Status.Types100' => 'Types 100','http_status_code' => 'ST100','errno' => 'NF.ST.0001'];
    public const StatusTypes200 = ['NF.Status.Types200' => 'Types 200','http_status_code' => 'ST200','errno' => 'NF.ST.0006'];
    public const StatusTypes300 = ['NF.Status.Types300' => 'Types 300','http_status_code' => 'ST300','errno' => 'NF.ST.0017'];
    public const StatusTypes400 = ['NF.Status.Types400' => 'Types 400','http_status_code' => 'ST400','errno' => 'NF.ST.0027'];
    public const StatusTypes500 = ['NF.Status.Types500' => 'Types 500','http_status_code' => 'ST500','errno' => 'NF.ST.0057'];
}
