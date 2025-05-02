<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class UrlShortenerController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    #[Route('/url-shortener', name: 'url_shortener', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('url-shortener.html.twig');
    }
}