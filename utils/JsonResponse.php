<?php


namespace Utils;


use ValueObject\Proveedores\JsonStatusCode;
use ValueObject\Proveedores\StatusCode;

final class JsonResponse
{
    private $statusCode;
    private $message;

    public function __construct(int $statusCode, array $message)
    {
        $this->statusCode = JsonStatusCode::from($statusCode);
        $this->message = $message;
    }

    public function toJson()
    {
        // clear the old headers
        header_remove();
        // set the actual code
        http_response_code($this->statusCode->value());
        // treat this as json
        header('Content-Type: application/json');
        // ok, validation error, or failure
        header('Status: '.$this->statusCode->getStatusHeader());
        // return the encoded json
        return json_encode(array(
            'status' => $this->statusCode->value(), // success or not?
            'message' => $this->message
        ));

    }

    public static function fromCreatedCode(array $message) : JsonResponse
    {
        $response = new self(StatusCode::CREATED, $message);
        return $response;
    }

    public static function fromAcceptedCode(array $message) : JsonResponse
    {
        $response = new self(StatusCode::ACCEPTED, $message);
        return $response;
    }

    public static function fromNoContentCode(array $message) : JsonResponse
    {
        $response = new self(StatusCode::NO_CONTENT, $message);
        return $response;
    }

    public static function fromUnprocessableCode(array $message) : JsonResponse
    {
        $response = new self(StatusCode::UNPROCESSABLE, $message);
        return $response;
    }
}