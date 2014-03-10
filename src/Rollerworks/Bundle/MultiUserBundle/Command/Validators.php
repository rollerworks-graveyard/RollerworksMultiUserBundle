<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Validators as BaseValidators;

/**
 * Validator functions.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class Validators extends BaseValidators
{
    public static function validateFormat($format)
    {
        $format = strtolower($format);

        if (!in_array($format, array('php', 'xml', 'yml'))) {
            throw new \RuntimeException(sprintf('Format "%s" is not supported.', $format));
        }

        return $format;
    }

    public static function validateDbDriver($format)
    {
        $format = strtolower($format);

        if (!in_array($format, array('orm', 'mongodb', 'couchdb', 'custom'))) {
            throw new \RuntimeException(sprintf('Format "%s" is not supported.', $format));
        }

        return $format;
    }
}
