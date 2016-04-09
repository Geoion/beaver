<?php

namespace Beaver\Install\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class ComposerPlugin implements PluginInterface
{
    /**
     * @inheritdoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new ComposerInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}