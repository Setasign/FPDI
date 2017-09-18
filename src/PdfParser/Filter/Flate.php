<?php
/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2017 Setasign - Jan Slabon (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 * @version   2.0.0-rc1
 */

namespace setasign\Fpdi\PdfParser\Filter;

/**
 * Class for handling zlib/deflate encoded data
 *
 * @package setasign\Fpdi\PdfParser\Filter
 */
class Flate implements FilterInterface
{
    /**
     * Checks whether the zlib extension is loaded.
     *
     * Used for testing purpose.
     *
     * @return boolean
     * @internal
     */
    protected function extensionLoaded()
    {
        return extension_loaded('zlib');
    }

    /**
     * Decodes a flate compressed string.
     *
     * @param string $data The input string
     * @return string
     * @throws FlateException
     */
    public function decode($data)
    {
        if ($this->extensionLoaded()) {
            $oData = $data;
            $data = @((strlen($data) > 0) ? gzuncompress($data) : '');
            if (false === $data) {
                // Try this fallback
                $tries = 1;
                while ($tries < 10 && ($data === false || strlen($data) < (strlen($oData) - $tries - 1))) {
                    $data = @(gzinflate(substr($oData, $tries)));
                    $tries++;
                }

                if (false === $data) {
                    throw new FlateException(
                        'Error while decompressing stream.',
                        FlateException::DECOMPRESS_ERROR
                    );
                }
            }
        } else {
            throw new FlateException(
                'To handle FlateDecode filter, enable zlib support in PHP.',
                FlateException::NO_ZLIB
            );
        }

        return $data;
    }
}
