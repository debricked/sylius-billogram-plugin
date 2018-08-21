<?php
/**
 * This file is part of the SyliusBillogramPlugin.
 *
 * Copyright (c) debricked AB
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


declare(strict_types=1);

namespace Debricked\SyliusBillogramPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class Currency extends Constraint
{
    /**
     * @var string
     */
    public $message;

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'debricked_sylius_billogram_plugin_currency';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
