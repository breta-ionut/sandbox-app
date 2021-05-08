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

    /**
     * Can be the text of the status when there's no need for other info.
     *
     * @var string
     */
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
    public function setTitle(string $title): static
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
    public function setCode(int $code): static
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
     * @param int  $status
     * @param bool $updateDetail
     *
     * @return $this
     */
    public function setStatus(int $status, bool $updateDetail = true): static
    {
        $this->status = $status;

        if ($updateDetail) {
            $this->detail = Response::$statusTexts[$status];
        }

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
    public function setDetail(string $detail): static
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
    public function setHeaders(array $headers): static
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
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function fromException(\Throwable $exception): static
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
