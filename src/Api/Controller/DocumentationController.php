<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\RouterInterface;

class DocumentationController extends AbstractController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        $configUrl = $this->generateUrl('app_api_documentation_configuration', [], RouterInterface::ABSOLUTE_URL);

        return $this->render('api/documentation.html.php', ['api_doc_config_url' => $configUrl]);
    }

    /**
     * @return BinaryFileResponse
     */
    public function configuration(): BinaryFileResponse
    {
        return $this->file($this->getParameter('app.api.doc_config_file'), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
