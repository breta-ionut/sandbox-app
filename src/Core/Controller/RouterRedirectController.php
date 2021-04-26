<?php

declare(strict_types=1);

namespace App\Core\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RouterRedirectController
{
    /**
     * @param Request     $request
     * @param string      $path
     * @param string|null $scheme
     * @param int         $httpPort
     * @param int         $httpsPort
     *
     * @return RedirectResponse
     *
     * @throws HttpException
     */
    public function __invoke(
        Request $request,
        string $path,
        ?string $scheme,
        int $httpPort,
        int $httpsPort
    ): RedirectResponse {
        if ('' == $path) {
            throw new HttpException(Response::HTTP_GONE);
        }

        if (null === $scheme) {
            $scheme = $request->getScheme();
        }

        $port = '';
        if ('http' === $scheme && 80 != $httpPort) {
            $port = ":$httpPort";
        } elseif ('https' === $scheme && 443 !== $httpsPort) {
            $port = ":$httpsPort";
        }

        if ($queryString = $request->getQueryString()) {
            $queryString = '?'.$queryString;
        }

        $url = $scheme.'://'.$request->getHost().$port.$request->getBaseUrl().$path.$queryString;

        return new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY);
    }
}
