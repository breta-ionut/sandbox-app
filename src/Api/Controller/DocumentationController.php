<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DocumentationController extends AbstractController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('api/documentation.html.php');
    }

    /**
     * @return BinaryFileResponse
     */
    public function configuration(): BinaryFileResponse
    {
        return $this->file($this->getParameter('app.api.doc_config_file'), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}