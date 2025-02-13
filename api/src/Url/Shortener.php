<?php

declare(strict_types=1);

namespace App\Url;

use App\Entity\ShortUrl;
use App\Repository\ShortUrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Shortener
{
	private const CHARS = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
	private const CODE_LENGTH = 7;
	private const CHECK_URL_EXISTS = false;

	private string $timestamp;
	private ShortUrlRepository $repo;

	public function __construct(private EntityManagerInterface $em, private HttpClientInterface $client)
	{
		$this->timestamp = date('Y-m-d H:i:s');
		$this->repo = $em->getRepository(ShortUrl::class);
	}

	/**
	 * Get short code from URL.
	 * 
	 * @throws ShortenerException
	 */
	public function urlToShortCode(string $url): ShortUrl
	{
		if (empty($url)) {
			throw new ShortenerException('No URL was specified.');
		}

		if ($this->validateUrlFormat($url) === false) {
			throw new ShortenerException('Url does not have a valid format.');
		}

		if (self::CHECK_URL_EXISTS) {
			if (!$this->verifyUrlExists($url)) {
				throw new ShortenerException('URL does not appear to exist.');
			}
		}

		$entity = $this->urlExistsInDb($url);
		if (!$entity instanceof ShortUrl) {
			$entity = $this->createShortCode($url);
		}

		return $entity;
	}

	private function validateUrlFormat(string $url): string|bool
	{
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	private function verifyUrlExists(string $url): bool
	{
		try {
			$response = $this->client->request('GET', $url);
		} catch (\Exception) {
			return false;
		}

		return $response->getStatusCode() !== 404;
	}

	private function urlExistsInDb(string $url): ?ShortUrl
	{
		return $this->repo->findOneByLongUrl($url);
	}

	private function createShortCode(string $url): ShortUrl
	{
		$entity = new ShortUrl($this->timestamp);
		$shortCode = $this->generateRandomString(self::CODE_LENGTH);

		$entity
			->setShortCode($shortCode)
			->setLongUrl($url);

		$this->em->persist($entity);
		$this->em->flush();

		return $entity;
	}

	private function generateRandomString(int $length = 6): string
	{
		$sets = explode('|', self::CHARS);
		$all = '';
		$randString = '';
		foreach ($sets as  $set) {
			$randString .= $set[array_rand(str_split($set))];
			$all .= $set;
		}

		$all = str_split($all);
		for ($i = 0; $i < $length - count($sets); $i++) {
			$randString .= $all[array_rand($all)];
		}

		$randString = str_shuffle($randString);
		return $randString;
	}

	public function shortCodeToUrl(string $code, bool $increment = true): ShortUrl
	{
		if (empty($code)) {
			throw new ShortenerException('NO short code was specified.');
		}

		if (false === $this->validateShortCode($code)) {
			throw new ShortenerException('Short code does not have a valid format.');
		}

		$entity = $this->repo->findOneByShortCode($code);

		if (!$entity instanceof ShortUrl) {
			throw new ShortenerException('Short code does not appear to exist.');
		}

		if ($increment) {
			$this->incrementCounter($entity);
		}

		return $entity;
	}

	private function validateShortCode(string $code): bool
	{
		$rawChars = str_replace('|', '', self::CHARS);

		foreach (str_split($code) as $char) {
			if (!preg_match("/[$rawChars]+/", $char)) {
				return false;
			}
		}

		return true;
	}

	private function incrementCounter(ShortUrl $entity): void
	{
		$id = $entity->getId();
		$entity->setHits($id + 1);

		$this->em->persist($entity);
		$this->em->flush();
	}
}