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

namespace spec\Debricked\SyliusBillogramPlugin\Validator\Constraints;

use Debricked\SyliusBillogramPlugin\Validator\Constraints\Currency;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

final class CurrencySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Currency::class);
    }

    function it_extends_constraint_class(): void
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_has_a_message(): void
    {
        $this->message->shouldReturn(null);
    }

    function it_is_validate_by_unique_user_email_validator(): void
    {
        $this->validatedBy()->shouldReturn('debricked_sylius_billogram_plugin_currency');
    }

    function it_has_targets(): void
    {
        $this->getTargets()->shouldReturn(Constraint::CLASS_CONSTRAINT);
    }
}
