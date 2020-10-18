<?php

declare(strict_types=1);

namespace App\Api\Error;

use App\Api\Exception\UserDataExceptionInterface;
use App\Api\Exception\UserMessageExceptionInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * RFC 7807 problem details.
 */
class Problem
{
    private string $title = 'An error occurred.';
    private int $code = UserCodes::UNKNOWN_ERROR;
    private int $status = Response::HTTP_INTERNAL_SERVER_ERROR;
    private string $detail;
    private array $headers = [];

    /**
     * Additional data to be exposed to API users.
     *
     * @var mixed
     */
    private $data;

    private ?\Throwable $exception;
    private ?FlattenException $flattenException;

    public function __construct()
    {
        $this->detail = Response::$statusTexts[$this->status];
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return $this
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     *
     * @return $this
     */
    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasData(): bool
    {
        return null !== $this->data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function fromException(\Throwable $exception): self
    {
        if ($exception instanceof UserMessageExceptionInterface) {
            $this->title = $exception->getUserMessage();
            $this->code = $exception->getUserCode();
        }

        $flattenException = FlattenException::createFromThrowable($exception, $this->status, $this->headers);

        $this->status = $flattenException->getStatusCode();
        $this->detail = $flattenException->getStatusText();
        $this->headers = $flattenException->getHeaders();

        if ($exception instanceof UserDataExceptionInterface) {
            $this->data = $exception->getUserData();
        }

        $this->exception = $exception;
        $this->flattenException = $flattenException;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return null !== $this->exception;
    }

    /**
     * @return \Throwable|null
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    /**
     * @return FlattenException|null
     */
    public function getFlattenException(): ?FlattenException
    {
        return $this->flattenException;
    }
}
