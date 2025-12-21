<?php

namespace App\Dto;

class ProcessedImage
{
    protected ?string $thumbnailPath = null;

    public function __construct(
        private string $filePath,
        private ?string $disk = null,
        private ?string $directory = null
    ) {}

    public function setThumbnailPath(?string $thumbnailPath): ProcessedImage
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): ProcessedImage
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getDisk(): ?string
    {
        return $this->disk;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }
}
