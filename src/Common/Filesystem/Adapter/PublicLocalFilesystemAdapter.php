<?php

declare(strict_types=1);

namespace App\Common\Filesystem\Adapter;

use App\Common\Filesystem\PublicFilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\MimeTypeDetection\MimeTypeDetector;
use Symfony\Component\Routing\RequestContext;

class PublicLocalFilesystemAdapter extends LocalFilesystemAdapter implements PublicFilesystemAdapter
{
    private RequestContext $requestContext;

    /**
     * @param string                   $location
     * @param RequestContext           $requestContext
     * @param VisibilityConverter|null $visibility
     * @param int                      $writeFlags
     * @param int                      $linkHandling
     * @param MimeTypeDetector|null    $mimeTypeDetector
     */
    public function __construct(
        string $location,
        RequestContext $requestContext,
        VisibilityConverter $visibility = null,
        int $writeFlags = LOCK_EX,
        int $linkHandling = self::DISALLOW_LINKS,
        MimeTypeDetector $mimeTypeDetector = null
    ) {
        parent::__construct($location, $visibility, $writeFlags, $linkHandling, $mimeTypeDetector);

        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritDoc}
     */
    public function publicUrl(string $path): string
    {
        $path = '' === $path || '/' === $path[0] ? $path : "/$path";

        return $this->schemeAuthority().$this->requestContext->getBaseUrl().$path;
    }

    /**
     * @return string
     */
    private function schemeAuthority(): string
    {
        $scheme = $this->requestContext->getScheme();
        $host = $this->requestContext->getHost();

        if (\in_array($scheme, ['', 'http', 'https'], true) && '' === $host) {
            return '';
        }

        $schemeAuthority = ('' === $scheme ? '//' : "$scheme://").$host;

        if ('http' === $scheme && 80 !== $this->requestContext->getHttpPort()) {
            $schemeAuthority .= ':'.$this->requestContext->getHttpPort();
        } elseif ('https' === $scheme && 443 !== $this->requestContext->getHttpsPort()) {
            $schemeAuthority .= ':'.$this->requestContext->getHttpsPort();
        }

        return $schemeAuthority;
    }
}
