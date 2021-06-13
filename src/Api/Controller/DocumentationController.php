<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/doc', name: 'documentation_', defaults: ['_api_endpoint' => false])]
class DocumentationController extends AbstractController
{
    #[Route(name: 'index', methods: 'GET')]
    public function index(): Response
    {
        $configUrl = $this->generateUrl('app_api_documentation_configuration', [], RouterInterface::ABSOLUTE_URL);

        return $this->render('api/documentation.html.php', ['api_doc_config_url' => $configUrl]);
    }

    #[Route('/config', name: 'configuration', methods: 'GET')]
    public function configuration(): BinaryFileResponse
    {
        return $this->file($this->getParameter('app.api.doc_config_file'), null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
