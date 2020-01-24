<?php

namespace App\Frontend\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exposes the FE application HTML entrypoint. All pages are built and handled by JS from that point on.
 */
class IndexController extends AbstractController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('frontend/index.html.php');
    }
}
