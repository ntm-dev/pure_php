<?php

namespace Core\Http\Exception;

/**
 * Raised when a user sends a malformed request.
 */
class NotFoundException extends \UnexpectedValueException implements RequestExceptionInterface
{
    /* http status code */
    const HTTP_STATUS_CODE = 404;

    /* http response text */
    const HTTP_RESPONSE_TEXT = 'Page NOT FOUND!!!';

    public function __construct(string $message = '', array $header = [])
    {
        $this->response($message, $header);
        die;
    }

    /**
     * Response error page
     * 
     * @param  string  $message
     * @param  array   $header
     */
    private function response(string $message = '', array $header = [])
    {
        http_response_code(static::HTTP_STATUS_CODE);
        return view('error.' . static::HTTP_STATUS_CODE, [
            'status' => static::HTTP_STATUS_CODE,
            'message' => static::HTTP_RESPONSE_TEXT,
        ]);
    }
}
