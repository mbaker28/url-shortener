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
	private string $longUrl = 'https://xyz.com';

	private function createShortener(ShortUrlRepository $repo, ?EntityManagerInterface $em = null): ShortenerService
	{
		$em ??= $this->createMock(EntityManagerInterface::class);
		$client = $this->createStub(HttpClientInterface::class);

		$em->expects($this->once())
			->method('getRepository')
			->with(ShortUrl::class)
			->willReturn($repo);

		return new ShortenerService($em, $client);
	}

	public function testUrlToShortUrl(): void
	{
		$repo = $this->createMock(ShortUrlRepository::class);
		$em = $this->createMock(EntityManagerInterface::class);
		$shortener = $this->createShortener($repo, $em);

		$repo->expects($this->once())
			->method('findOneBy')
			->willReturn(null);

		$em->expects($this->once())
			->method('persist');

		$em->expects($this->once())->method('flush');

		$entity = $shortener->urlToShortUrl($this->longUrl);

		$this->assertEquals($this->longUrl, $entity->getLongUrl());
	}

	public function testUrlToShortUrlShouldThrowExceptionOnEmptyUrl(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No URL was specified.');

		$shortener = $this->createShortener($this->createStub(ShortUrlRepository::class));

		$shortener->urlToShortUrl('');
	}

	public function testUrlToShortUrlShouldThrowExceptionOnInvalidUrlFormat(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('URL does not have a valid format.');

		$shortener = $this->createShortener($this->createStub(ShortUrlRepository::class));

		$shortener->urlToShortUrl('invalid-url');
	}

	public function testShortCodeToUrl(): void
	{
		$repo = $this->createMock(ShortUrlRepository::class);
		$shortener = $this->createShortener($repo);
		$method = new \ReflectionMethod($shortener, 'generateRandomString');

		$code = $method->invoke($shortener, 7);

		$entity = new ShortUrl();
		$entity->setLongUrl($this->longUrl)
			->setShortCode($code);

		$repo->expects($this->once())
			->method('findOneBy')
			->with($this->equalTo(['shortCode' => $code]))
			->willReturn($entity);

		$res = $shortener->shortCodeToUrl($code, false);

		$this->assertSame($entity, $res);
	}

	public function testShortCodeToUrlShouldThrowExceptionOnEmptyCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No short code was specified.');

		$shortener = $this->createShortener($this->createStub(ShortUrlRepository::class));

		$shortener->shortCodeToUrl('', false);
	}

	public function testShortCodeToUrlShouldThrowExceptionOnInvalidCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('Short code does not have a valid format.');

		$shortener = $this->createShortener($this->createStub(ShortUrlRepository::class));

		$shortener->shortCodeToUrl('invalid-code', false);
	}
}
