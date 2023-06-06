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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class UrlController extends AbstractController
{
	public function __construct(private Shortener $shortener)
	{
	}

	#[Route('/', name: 'index', methods: ['GET', 'POST'])]
	public function __invoke(EntityManagerInterface $em, Request $request): Response
	{
		$repo = $em->getRepository(ShortUrl::class);

		$urls = $repo->findAll();

		$form = $this->createFormBuilder()
			->add('url', TextType::class, ['label' => 'Enter a URL:'])
			->add('submit', SubmitType::class, ['label' => 'Shorten'])
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$entity = $this->shortener->urlToShortCode($data['url']);

			return $this->json([
				'url' => $entity->getLongUrl(),
				'shortCode' => $entity->getShortCode()
			]);
		}

		return $this->render('shortener.twig', ['urls' => $urls, 'form' => $form]);
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