<?php

declare(strict_types=1);

namespace App\Emailable\Adapter;

use App\Emailable\EmailMetadataInterface;
use App\Entity\News\News;

class EmailableNewsAdapter implements EmailMetadataInterface
{
    private readonly News $news;

    public function __construct(News $news)
    {
        $this->news = $news;
    }

    public function getEmailData(): array
    {
        return [
            'news.fields.title' => $this->news->getTitle(),
            'news.fields.date' => $this->news->getDate()->format('Y-m-d'),
            'news.fields.content' => $this->news->getContent(),
            'news.fields.author' => null !== $this->news->getPeople() ? $this->news->getPeople()->getLastname().' '.$this->news->getPeople()->getFirstname() : '',
        ];
    }

    public function getEmailSubject(): string
    {
        return $this->news->getTitle();
    }

    public function getTranslationDomain(): string
    {
        return 'news';
    }

    public static function supports($resource): bool
    {
        return \is_object($resource) && $resource instanceof News;
    }
}
