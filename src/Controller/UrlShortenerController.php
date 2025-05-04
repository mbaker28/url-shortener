<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ShortenerException;
use App\Service\ShortenerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class UrlShortenerController extends AbstractController
{
    public function __construct(
        private ShortenerService $shortener
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    #[Route('/url-shortener', name: 'url_shortener', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('url-shortener.html.twig');
    }

    #[Route('/url-shortener/create', name: 'url_shortener_create', methods: ['POST'])]
    public function createShortUrl(Request $request): JsonResponse
    {
        try {
            $entity = $this->shortener->urlToShortUrl($request->request->getString('url'));
        } catch (ShortenerException $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }

        return $this->json([
            'url' => $entity->getLongUrl(),
            'shortCode' => $entity->getShortCode(),
        ], Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    #[Route('/{shortCode}', methods: ['GET'])]
    public function redirectToLongUrl(string $shortCode): Response
    {
        try {
            $entity = $this->shortener->shortCodeToUrl($shortCode);

            return $this->redirect($entity->getLongUrl());
        } catch (ShortenerException $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}