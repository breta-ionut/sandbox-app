<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DocumentationController extends AbstractController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('api/documentation.html.php');
    }
}
