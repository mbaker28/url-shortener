<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ShortenerException;
use App\Service\ShortenerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class UrlShortenerController extends AbstractController
{
    public function __construct(
        private ShortenerService $shortener
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('url-shortener.html.twig');
    }

    #[Route('/', name: 'url_shortener_create', methods: ['POST'])]
    public function createShortUrl(Request $request, ValidatorInterface $validator): Response
    {
        $statusCode = Response::HTTP_OK;
        $error = null;
        $shortCode = null;
        $url = $request->request->getString('url');

        $violations = $validator->validate($url, [
            new Assert\NotBlank(),
            new Assert\Url()
        ]);

        if (count($violations) > 0) {
            $error = (string) $violations;
            $statusCode = Response::HTTP_BAD_REQUEST;
        } else {
            try {
                $entity = $this->shortener->urlToShortUrl($url);
                $shortCode = $entity->getShortCode();
            } catch (ShortenerException $e) {
                $error = $e->getMessage();
                $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        }

        return $this->render('partials/_result.html.twig', [
            'error' => $error,
            'shortCode' => $shortCode,
            'url' => $url,
        ], new Response(status: $statusCode));
    }

    #[Route('/{shortCode}', name: 'short_url_redirect', methods: ['GET'])]
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
