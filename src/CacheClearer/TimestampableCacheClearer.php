<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\CacheClearer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class TimestampableCacheClearer implements CacheClearerInterface
{
    public const METADATA_CACHE_FILENAME = 'timestampable_metadata.php';

    private Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    public function clear(string $cacheDir): void
    {
        $filesystem = $this->filesystem;
        $metadataFile = $cacheDir.'/'.self::METADATA_CACHE_FILENAME;
        if ($filesystem->exists($metadataFile)) {
            $filesystem->remove($metadataFile);
        }
    }
}
