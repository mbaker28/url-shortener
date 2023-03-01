<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Url\ShortenerException;
use App\Url\Shortener;
use App\Entity\ShortUrl;

final class UrlController extends AbstractController
{
	public function __construct(private Shortener $shortener)
	{
	}

	#[Route(
		'/',
		name: 'convert',
		methods: ['POST'],
		condition: "request.request.get('url') != null"
	)]
	public function convert(Request $request): Response
	{
		try {
			$entity = $this->shortener->urlToShortCode($request->request->get('url'));
		} catch (ShortenerException $e) {
			return $this->json([
				'error' => $e->getMessage()
			]);
		}

		return $this->json([
			'url' => $entity->getLongUrl(),
			'shortCode' => $entity->getShortCode()
		]);
	}

	#[Route('/', name: 'index', methods: ['GET'])]
	public function index(EntityManagerInterface $em): Response
	{
		$repo = $em->getRepository(ShortUrl::class);

		$urls = $repo->findAll();

		return $this->render('shortener.twig', ['urls' => $urls]);
	}

	#[Route('/{shortCode}', methods: ['GET'])]
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