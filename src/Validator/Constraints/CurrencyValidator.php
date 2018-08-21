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

use Debricked\SyliusBillogramPlugin\BillogramGatewayFactoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class CurrencyValidator extends ConstraintValidator
{
    /**
     * @param PaymentMethodInterface $paymentMethod
     * @param Constraint|Currency    $constraint
     *
     * {@inheritdoc}
     */
    public function validate($paymentMethod, Constraint $constraint): void
    {
        Assert::isInstanceOf($paymentMethod, PaymentMethodInterface::class);

        Assert::isInstanceOf($constraint, Currency::class);

        /** @var ChannelInterface $channel */
        foreach ($paymentMethod->getChannels() as $channel) {
            if (
                null === $channel->getBaseCurrency() ||
                false === in_array(
                    strtoupper($channel->getBaseCurrency()->getCode()),
                    BillogramGatewayFactoryInterface::CURRENCIES_AVAILABLE
                )
            ) {
                $message = isset($constraint->message) ? $constraint->message : null;

                $this->context->buildViolation(
                    $message,
                    [
                        '{{ currencies }}' => implode(', ', BillogramGatewayFactoryInterface::CURRENCIES_AVAILABLE),
                    ]
                )->atPath('channels')->addViolation();

                return;
            }
        }
    }
}
