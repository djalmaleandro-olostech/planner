<?php

declare(strict_types=1);

namespace App\Helper;


final class VersionHelper
{
    public function __construct(){}

    private function formattedVersion(string $version): ?array
    {
        if (empty($version)) {
            return null;
        }
        $version = preg_replace('/[^0-9.]/', '', $version);
        $auxVersionObject = explode('.', $version);
        $versionObject = [
            'major' => sprintf('%02d', $auxVersionObject[0]),
            'minor' => isset($auxVersionObject[1]) ? sprintf('%02d', $auxVersionObject[1]) : '00',
            'patch' => isset($auxVersionObject[2]) ? sprintf('%02d', $auxVersionObject[2]) : '00',
        ];
        $versionObject['full_version'] = sprintf(
            '%s.%s.%s',
            $versionObject['major'],
            $versionObject['minor'],
            $versionObject['patch']
        );
        $versionObject['version_number'] = sprintf(
            '%s%s%s',
            $versionObject['major'],
            $versionObject['minor'],
            $versionObject['patch']
        );

        return $versionObject;
    }

    public function isVersionBelow(string $versionCurrent, string $versionTarget): bool
    {
        $versionCurrent = $this->formattedVersion($versionCurrent);
        $versionTarget = $this->formattedVersion($versionTarget);

        return (int) ($versionCurrent['version_number']) < (int) ($versionTarget['version_number']);
    }

    public function isVersionAbove(string $versionCurrent, string $versionTarget): bool
    {
        $versionCurrent = $this->formattedVersion($versionCurrent);
        $versionTarget = $this->formattedVersion($versionTarget);

        return (int) ($versionCurrent['version_number']) >= (int) ($versionTarget['version_number']);
    }
}