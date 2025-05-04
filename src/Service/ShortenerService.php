<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ShortUrl;
use App\Exception\ShortenerException;
use App\Repository\ShortUrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ShortenerService
{
	private const CHARS = 'abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789';
	private const CODE_LENGTH = 7;
	private const CHECK_URL_EXISTS = false;

	private ShortUrlRepository $repo;

	public function __construct(private EntityManagerInterface $em, private HttpClientInterface $client)
	{
		$this->repo = $em->getRepository(ShortUrl::class);
	}

	public function urlToShortUrl(string $url): ShortUrl
	{
		if (empty($url)) {
			throw new ShortenerException('No URL was specified.');
		}

		if (!$this->validateUrlFormat($url)) {
			throw new ShortenerException('URL does not have a valid format.');
		}

		if (self::CHECK_URL_EXISTS && !$this->verifyUrlExists($url)) {
			throw new ShortenerException('URL does not appear to exist.');
		}

		try {
			$entity = $this->urlExistsInDb($url);
		} catch (\Throwable $e) {
			throw $e;
		}

		if (!$entity instanceof ShortUrl) {
			$entity = $this->createShortUrl($url);
		}

		return $entity;
	}

	private function validateUrlFormat(string $url): bool
	{
		return filter_var($url, FILTER_VALIDATE_URL) !== false;
	}

	private function verifyUrlExists(string $url): bool
	{
		try {
			$response = $this->client->request('GET', $url);
		} catch (\Throwable $e) {
			return false;
		}

		return $response->getStatusCode() !== 404;
	}

	private function urlExistsInDb(string $url): ?ShortUrl
	{
		return $this->repo->findOneBy(['longUrl' => $url]);
	}

	private function createShortUrl(string $url): ShortUrl
	{
		$entity = new ShortUrl();
		$shortCode = $this->generateRandomString(self::CODE_LENGTH);

		$entity->setShortCode($shortCode)
			->setLongUrl($url);

		try {
			$this->em->persist($entity);
			$this->em->flush($entity);
		} catch (\Throwable $e) {
			throw new ShortenerException('Unable to create short URL.');
		}

		return $entity;
	}

	private function generateRandomString(int $length): string
	{
		$sets = explode('|', self::CHARS);
		$all = '';
		$randString = '';
		foreach ($sets as $set) {
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
			throw new ShortenerException('No short code was specified.');
		}

		if (!$this->validateShortCode($code)) {
			throw new ShortenerException('Short code does not have a valid format.');
		}

		$entity = $this->repo->findOneBy(['shortCode' => $code]);

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

		foreach (str_split($code) as  $char) {
			if (!preg_match("/[$rawChars]+/", $char)) {
				return false;
			}
		}

		return true;
	}

	private function incrementCounter(ShortUrl $entity): void
	{
		$hits = (int) $entity->getHits();
		$entity->setHits($hits + 1);

		$this->em->persist($entity);
		$this->em->flush();
	}
}