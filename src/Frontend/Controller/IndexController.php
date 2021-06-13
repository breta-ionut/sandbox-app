<?php

declare(strict_types=1);

namespace App\Frontend\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Exposes the FE application HTML entrypoint. All pages are built and handled by JS from that point on.
 */
#[Route(name: 'index_')]
class IndexController extends AbstractController
{
    #[Route('/{path<^(?!api/).*?>}', name: 'index')]
    public function index(): Response
    {
        return $this->render('frontend/index.html.php');
    }
}
