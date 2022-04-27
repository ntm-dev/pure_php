<?php

namespace Core\Http\Exception;

class HttpException extends \RuntimeException
{
    /**
     * Response error page
     * 
     * @param  int  $httpCode
     * @param  string   $header
     */
    public function response()
    {
        $httpCode = $this->getResponseCode();
        $httpResponseText = $this->getResponseText();
        http_response_code($httpCode);

        return view('error.' . $httpCode, [
            'status' => $httpCode,
            'message' => $httpResponseText,
        ]);
    }

    protected function getResponseCode()
    {
        if (is_null(static::HTTP_STATUS_CODE)) {
            throw new \Exception("Class " . static::class . " must conttain \"HTTP_STATUS_CODE\" constant.");
        }

        return static::HTTP_STATUS_CODE;
    }

    protected function getResponseText()
    {
        if (is_null(static::HTTP_RESPONSE_TEXT)) {
            throw new \Exception("Class " . static::class . " must conttain \"HTTP_RESPONSE_TEXT\" constant.");
        }

        return static::HTTP_RESPONSE_TEXT;
    }
}
