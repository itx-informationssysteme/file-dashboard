<?php

declare(strict_types=1);

namespace Itx\FileDashboard\Event;

final class FileRenameEvent
{
    public function __construct(
        private string $fileName,
        private readonly string $identifier,
    ) {}

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
