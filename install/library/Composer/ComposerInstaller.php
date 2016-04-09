<?php

namespace Beaver\Install\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use InvalidArgumentException;

class ComposerInstaller extends LibraryInstaller
{
    const TYPE = 'beaver';

    private static $installedPaths = [];

    /**
     * @inheritdoc
     */
    public function getInstallPath(PackageInterface $package)
    {
        $directory = false;
        $prettyName = $package->getPrettyName();
        if ($this->composer->getPackage()) {
            $topExtra = $this->composer->getPackage()->getExtra();
            if (isset($topExtra['beaver-install-dir'])) {
                $directory = $topExtra['beaver-install-dir'];
                if (is_array($directory)) {
                    $directory = isset($directory[$prettyName]) ? $directory[$prettyName] : false;
                }
            }
        }

        $extra = $package->getExtra();
        if (!$directory && isset($extra['beaver-install-dir'])) {
            $directory = $extra['beaver-install-dir'];
        }
        if (!$directory) {
            $directory = 'beaver';
        }

        if (isset(self::$installedPaths[$directory])) {
            throw new InvalidArgumentException('Two packages cannot share the same directory!');
        }

        self::$installedPaths[$directory] = $prettyName;
        return $directory;
    }

    /**
     * @inheritdoc
     */
    public function supports($packageType)
    {
        return self::TYPE === $packageType;
    }
}