<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ShortUrl;
use App\Url\Shortener;
use App\Url\ShortenerException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UrlController extends AbstractController
{
	public function __construct(private Shortener $shortener)
	{
	}

	#[Route('/api', methods: ['GET'])]
	public function __invoke(): Response
	{
		return new Response('Hello world!');
	}

	#[Route('/api/short-code', methods: ['POST'])]
	public function createShortCode(Request $request): JsonResponse
	{
		$entity = $this->shortener->urlToShortCode($request->request->getString('url'));

		return $this->json([
			'url' => $entity->getLongUrl(),
			'shortCode' => $entity->getShortCode()
		], 200, ['Content-Type' => 'application/json']);
	}

	#[Route('/api/{shortCode}', methods: ['GET'])]
	public function redirectToLongUrl(string $shortCode): Response
	{
		try {
			$entity = $this->shortener->shortCodeToUrl($shortCode);

			return $this->redirect($entity->getLongUrl());
		} catch (ShortenerException $e) {
			return new Response($e->getMessage());
		}
	}
}