<?php

/*
 * This file is part of the Safe NFT Metadata Provider package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\MetadataUpdater;

use App\Contract\MetadataUpdaterInterface;
use RuntimeException;

/**
 * This metadata updater replaces each metadata key with the values found inside the given JSON template.
 * Any key which is not found in the template is left as it is.
 *
 * Each template value also supports the replacement of the following placeholders:
 * - {TOKEN_ID}
 * - {INT_TOKEN_ID} (a value matching this string exactly will be replaced with the token ID as an integer value)
 * - {ASSET_URI} (please remember that the "image" key is already replaced by default!)
 *
 * Limitations: this updater supports first-level keys only.
 *
 * Template example:
 * {
 *   "name": "My awesome token #{TOKEN_ID}"
 * }
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class TemplatedMetadataUpdater implements MetadataUpdaterInterface
{
    /**
     * @var string
     */
    private const TOKEN_ID_PLACEHOLDER = '{TOKEN_ID}';

    /**
     * @var string
     */
    private const INT_TOKEN_ID_PLACEHOLDER = '{INT_TOKEN_ID}';

    /**
     * @var string
     */
    private const ASSET_URI_PLACEHOLDER = '{ASSET_URI}';

    /**
     * @var string
     */
    private const ASSET_URI1_PLACEHOLDER = '{ASSET_URI1}';

    /**
     * @param array<string, string> $template
     */
    public function __construct(
        private readonly ?array $template,
        private readonly ?array $template1,
    ) {
    }

    public function updateMetadata(array &$metadata,array &$metadata1, int $tokenId, string $assetUri, string $assetUri1): void
    {
        if (null === $this->template) {
            return;
        }

        foreach ($this->template as $key => $value) {
            if (! is_string($value) || (isset($metadata[$key]) && ! is_string($metadata[$key]))) {
                throw new RuntimeException('Deep level replacement is not supported in METADATA_TEMPLATE.');
            }

            $metadata[$key] = $this->replacePlaceholders($value, $value1, $tokenId, $assetUri, $assetUri1);
        }
    }

    public function updateMetadata1(array &$metadata,array &$metadata1, int $tokenId, string $assetUri, string $assetUri1): void
    {
        if (null === $this->template1) {
            return;
        }

        foreach ($this->template1 as $key1 => $value1) {
            if (! is_string($value1) || (isset($metadata1[$key1]) && ! is_string($metadata1[$key1]))) {
                throw new RuntimeException('Deep level replacement is not supported in METADATA_TEMPLATE.');
            }
    
            $metadata1[$key1] = $this->replacePlaceholders($value, $value1, $tokenId, $assetUri, $assetUri1);
        }  
    }

    private function replacePlaceholders(string $value, string $value1, int $tokenId, string $assetUri, string $assetUri1): string|int
    {
        if (self::INT_TOKEN_ID_PLACEHOLDER === $value) {
            return $tokenId;
        }

        return str_replace(
            [self::TOKEN_ID_PLACEHOLDER, self::ASSET_URI_PLACEHOLDER],
            [(string) $tokenId, $assetUri, $assetUri1],
            $value, $value1
        );
    }
}
