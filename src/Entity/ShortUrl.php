<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use DateTimeImmutable;
use App\Repository\ShortUrlRepository;

#[ORM\Entity(repositoryClass: ShortUrlRepository::class)]
#[ORM\Table(name: 'short_urls')]
final class ShortUrl
{
	#[ORM\Id]
	#[ORM\Column(type: Types::INTEGER)]
	#[ORM\GeneratedValue]
	private int $id;

	#[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
	private string $longUrl;

	#[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
	private string $shortCode;

	#[ORM\Column(type: Types::INTEGER)]
	private int $hits = 0;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
	private DateTimeInterface $created;

	public function __construct(?string $time = null)
	{
		$this->created = new DateTimeImmutable($time ?? 'now');
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getLongUrl(): string
	{
		return $this->longUrl;
	}

	public function setLongUrl(string $url): void
	{
		$this->longUrl = $url;
	}

	public function getShortCode(): string
	{
		return $this->shortCode;
	}

	public function setShortCode(string $code): void
	{
		$this->shortCode = $code;
	}

	public function getHits(): int
	{
		return $this->hits;
	}

	public function setHits(int $hits): void
	{
		$this->hits = $hits;
	}
}