<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PsalmStubs;

use Composer\InstalledVersions;

/**
 * Installed version of shopware/core in the analysed project. Psalm loads the autoloader of the project before the
 * plugins, so the Composer runtime API also sees the packages of the project (plugin vendor or Shopware installation).
 */
final class ShopwareVersion
{
    public static function installed(): ?string
    {
        try {
            if (!InstalledVersions::isInstalled('shopware/core')) {
                return null;
            }

            return InstalledVersions::getVersion('shopware/core');
        } catch (\Throwable) {
            return null;
        }
    }
}
