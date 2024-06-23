<?php

declare(strict_types=1);

namespace OAuth\Enum;

class ErrorCode
{
    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_INVALID_REQUEST = 'invalid_request';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_INVALID_CLIENT = 'invalid_client';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_UNAUTHORIZED_CLIENT = 'unauthorized_client';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-3.1.2.4
     */
    public const ERROR_REDIRECT_URI_MISMATCH = 'redirect_uri_mismatch';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     */
    public const ERROR_USER_DENIED = 'access_denied';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     */
    public const ERROR_UNSUPPORTED_RESPONSE_TYPE = 'unsupported_response_type';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     */
    public const ERROR_INVALID_SCOPE = 'invalid_scope';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_INVALID_GRANT = 'invalid_grant';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_UNSUPPORTED_GRANT_TYPE = 'unsupported_grant_type';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2.2.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public const ERROR_INSUFFICIENT_SCOPE = 'invalid_scope';
}
