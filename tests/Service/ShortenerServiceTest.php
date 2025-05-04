<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ShortUrl;
use App\Exception\ShortenerException;
use App\Repository\ShortUrlRepository;
use App\Service\ShortenerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ShortenerServiceTest extends TestCase
{
	private ShortenerService $shortener;
	private ShortUrlRepository $repo;
	private EntityManagerInterface $em;
	private HttpClientInterface $client;

	private string $longUrl = 'https://xyz.com';

	public function setUp(): void
	{
		parent::setUp();
		$this->repo = $this->createMock(ShortUrlRepository::class);
		$this->em = $this->createMock(EntityManagerInterface::class);
		$this->client = $this->createMock(HttpClientInterface::class);

		$this->em->expects($this->once())
			->method('getRepository')
			->with(ShortUrl::class)
			->willReturn($this->repo);

		$this->shortener = new ShortenerService($this->em, $this->client);
	}

	public function testUrlToShortUrl(): void
	{
		$this->repo->expects($this->once())
			->method('findOneBy')
			->willReturn(null);

		$this->em->expects($this->once())
			->method('persist');

		$this->em->expects($this->once())->method('flush');

		$entity = $this->shortener->urlToShortUrl($this->longUrl);

		$this->assertEquals($this->longUrl, $entity->getLongUrl());
	}

	public function testUrlToShortUrlShouldThrowExceptionOnEmptyUrl(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No URL was specified.');

		$this->shortener->urlToShortUrl('');
	}

	public function testUrlToShortUrlShouldThrowExceptionOnInvalidUrlFormat(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('URL does not have a valid format.');

		$this->shortener->urlToShortUrl('invalid-url');
	}

	public function testShortCodeToUrl(): void
	{
		$method = new \ReflectionMethod($this->shortener, 'generateRandomString');
		$method->setAccessible(true);

		$code = $method->invoke($this->shortener, 7);

		$entity = new ShortUrl();
		$entity->setLongUrl($this->longUrl)
			->setShortCode($code);

		$this->repo->expects($this->once())
			->method('findOneBy')
			->with($this->equalTo(['shortCode' => $code]))
			->willReturn($entity);

		$res = $this->shortener->shortCodeToUrl($code, false);

		$this->assertSame($entity, $res);
	}

	public function testShortCodeToUrlShouldThrowExceptionOnEmptyCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No short code was specified.');

		$this->shortener->shortCodeToUrl('', false);
	}

	public function testShortCodeToUrlShouldThrowExceptionOnInvalidCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('Short code does not have a valid format.');

		$this->shortener->shortCodeToUrl('invalid-code', false);
	}
}