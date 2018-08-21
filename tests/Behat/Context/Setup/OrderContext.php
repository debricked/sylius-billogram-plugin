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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Debricked\SyliusBillogramPlugin\BillogramGatewayFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\PaymentTransitions;

final class OrderContext implements Context
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StateMachineFactoryInterface
     */
    private $stateMachineFactory;

    /**
     * @var RegistryInterface|Payum
     */
    private $payum;

    /**
     * @param ObjectManager $objectManager
     * @param StateMachineFactoryInterface $stateMachineFactory
     * @param RegistryInterface $payum
     */
    public function __construct(
        ObjectManager $objectManager,
        StateMachineFactoryInterface $stateMachineFactory,
        RegistryInterface $payum
    ) {
        $this->objectManager = $objectManager;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->payum = $payum;
    }

    /**
     * @Given /^(this order) with billogram payment is already paid$/
     */
    public function thisOrderWithBillogramPaymentIsAlreadyPaid(OrderInterface $order): void
    {
        $this->applyBillogramPaymentTransitionOnOrder($order, PaymentTransitions::TRANSITION_COMPLETE);

        $this->objectManager->flush();
    }

    /**
     * @param OrderInterface $order
     * @param $transition
     *
     * @throws \SM\SMException
     */
    private function applyBillogramPaymentTransitionOnOrder(OrderInterface $order, $transition): void
    {
        foreach ($order->getPayments() as $payment) {
            /** @var PaymentMethodInterface $paymentMethod */
            $paymentMethod = $payment->getMethod();

            if (BillogramGatewayFactory::FACTORY_NAME === $paymentMethod->getGatewayConfig()->getFactoryName()) {
                $refundToken = $this->payum->getTokenFactory()->createRefundToken('billogram', $payment);

                $metadata = [];

                $metadata['refund_token'] = $refundToken->getHash();

                $model['metadata'] = $metadata;

                $model['amount'] = $payment->getAmount() / 100;
                $model['billogram_invoice_id'] = 'test';

                $payment->setDetails($model);
            }

            $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH)->apply($transition);
        }
    }
}
