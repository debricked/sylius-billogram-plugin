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

use Debricked\SyliusBillogramPlugin\BillogramGatewayFactory;
use Debricked\SyliusBillogramPlugin\Validator\Constraints\Currency;
use Debricked\SyliusBillogramPlugin\Validator\Constraints\CurrencyValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\Model\GatewayConfigInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CurrencyValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $executionContextInterface): void
    {
        $this->initialize($executionContextInterface);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(CurrencyValidator::class);
    }

    function it_extends_constraint_validator_class(): void
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_validates(
        PaymentMethodInterface $paymentMethod,
        GatewayConfigInterface $gatewayConfig
    ): void {
        $currencyConstraint = new Currency();
        $gatewayConfig->getFactoryName()->willReturn(BillogramGatewayFactory::FACTORY_NAME);
        $paymentMethod->getGatewayConfig()->willReturn($gatewayConfig);
        $paymentMethod->getChannels()->willReturn(new ArrayCollection([]));

        $this->validate($paymentMethod, $currencyConstraint);
    }
}
