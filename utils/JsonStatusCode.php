<?php


namespace ValueObject;

final class JsonStatusCode
{
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NO_CONTENT = 204;
    const STATUS_UNPROCESSABLE = 422;

    protected $status = array(
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        204 => '204 No Content',
        400 => '400 Bad Request',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
    );

    protected function __construct(int $value)
    {
        $this->guardStatusCode($value);
        parent::__construct($value);
    }

    public function getStatusHeader()
    {
       return $this->status[$this->value()];
    }

    private function guardStatusCode(int $value) {
        if(!isset($this->status[$value])) {
            throw new \Exception(sprintf("Código de estado no reconocido <%d>", $value));
        }
    }

}