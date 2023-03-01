<?php

declare(strict_types=1);

namespace App\Tests\Url;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Url\ShortenerException;
use App\Url\Shortener;
use App\Repository\ShortUrlRepository;
use App\Entity\ShortUrl;

class ShortenerTest extends TestCase
{
	private Shortener $shortener;
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
			->willReturn($this->repo);
		
		$this->shortener = new Shortener($this->em, $this->client);
	}

	public function testUrlToShortCode(): void
	{
		$this->repo->expects($this->once())
			->method('findOneBy')
			->willReturn(null);

		$this->em->expects($this->once())
			->method('persist');

		$this->em->expects($this->once())->method('flush');

		$entity = $this->shortener->urlToShortCode($this->longUrl);

		$this->assertEquals($this->longUrl, $entity->getLongUrl());
	}

	public function testUrlToShortCodeShouldThrowExceptionOnEmptyUrl(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No URL was specified.');

		$this->shortener->urlToShortCode('');
	}

	public function testUrlToShortCodeShouldThrowExceptionOnInvalidUrlFormat(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('URL does not have a valid format.');

		$this->shortener->urlToShortCode('foo');
	}

	public function testShortCodeToUrl(): string
	{
		$method = new \ReflectionMethod($this->shortener, 'generateRandomString');
		$method->setAccessible(true);

		$code = $method->invoke($this->shortener);

		$entity = new ShortUrl();
		$entity->setLongUrl($this->longUrl);
		$entity->setShortCode($code);

		$this->repo->expects($this->once())
			->method('findOneBy')
			->with($this->equalTo(['short_code' => $code]))
			->willReturn($entity);

		$res = $this->shortener->shortCodeToUrl($code, false);

		$this->assertSame($entity, $res);

		return $code;
	}

	public function testShortCodeToUrlThrowsExceptionOnEmptyCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('No short code was specified.');

		$this->shortener->shortCodeToUrl('', false);
	}

	public function testShortCodeToUrlThrowsExceptionOnInvalidCode(): void
	{
		$this->expectException(ShortenerException::class);
		$this->expectExceptionMessage('Short code does not have a valid format.');

		$this->shortener->shortCodeToUrl('foo', false);
	}
}