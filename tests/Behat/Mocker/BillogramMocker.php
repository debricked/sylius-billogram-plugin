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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Mocker;

use Billogram\BillogramClient;
use Billogram\Model\Invoice\Invoice;
use Billogram\Api\Resources\Payment;
use Billogram\Api\Types\PaymentStatus;
use Billogram\Api\Types\SubscriptionStatus;
use Debricked\SyliusBillogramPlugin\spec\BillogramClientHelper;
use Sylius\Behat\Service\Mocker\MockerInterface;

final class BillogramMocker
{
    /**
     * @var MockerInterface
     */
    private $mocker;

    /**
     * @param MockerInterface $mocker
     */
    public function __construct(MockerInterface $mocker)
    {
        $this->mocker = $mocker;
    }

    /**
     * @param callable $action
     */
    public function mockCreateInvoice(callable $action): void
    {
        $invoice = \Mockery::mock('invoice', Invoice::class);

        $invoice->id = 1;

        $invoice
            ->shouldReceive('getCheckoutUrl')
            ->andReturn('')
        ;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('create')
            ->andReturn($invoice)
        ;

//        $client = BillogramClientHelper::createBillogramClient();
//
//        $client
//            ->shouldReceive('create')
//        ;
//
//        $client->payments = $payments;

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function mockApiSuccessfulPayment(callable $action): void
    {
        $payment = \Mockery::mock('payment', Payment::class);

        $payment->metadata = (object) [
            'order_id' => 1,
        ];

        $payment->status = PaymentStatus::STATUS_PAID;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $mock = $this->mocker->mockService('debricked_sylius_billogram_plugin.billogram_api_client', BillogramClient::class);

        $mock
            ->shouldReceive('setApiKey')
        ;

        $mock
            ->shouldReceive('isRecurringSubscription')
            ->andReturn(false)
        ;

        $mock
            ->shouldReceive('isRefunded')
            ->andReturn(false)
        ;

        $mock->payments = $payments;

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function mockApiCancelledPayment(callable $action): void
    {
        $payment =\Mockery::mock('payment', Payment::class);

        $payment->metadata = (object) [
            'order_id' => 1,
        ];

        $payment->status = PaymentStatus::STATUS_CANCELED;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $mock = $this->mocker->mockService('debricked_sylius_billogram_plugin.billogram_api_client', BillogramClient::class);

        $mock
            ->shouldReceive('setApiKey')
        ;

        $mock
            ->shouldReceive('isRecurringSubscription')
            ->andReturn(false)
        ;

        $mock
            ->shouldReceive('isRefunded')
            ->andReturn(false)
        ;

        $mock->payments = $payments;

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function mockApiRefundedPayment(callable $action): void
    {
        $payment = \Mockery::mock('payment', Payment::class);

        $payment->status = 'refund';

        $payment
            ->shouldReceive('canBeRefunded')
            ->andReturn(true)
        ;

        $payment
            ->shouldReceive('refund')
        ;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $payments
            ->shouldReceive('refund')
        ;

        $mock = $this->mocker->mockService('debricked_sylius_billogram_plugin.billogram_api_client', BillogramClient::class);

        $mock
            ->shouldReceive('setApiKey')
        ;

        $mock
            ->shouldReceive('isRecurringSubscription')
            ->andReturn(false)
        ;

        $mock
            ->shouldReceive('isRefunded')
            ->andReturn(true);

        $mock->payments = $payments;

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function mockApiCreateRecurringSubscription(callable $action): void
    {
        $payment = \Mockery::mock('payment', Payment::class);

        $payment->id = 'id_1';
        $payment->status = SubscriptionStatus::STATUS_ACTIVE;

        $payment
            ->shouldReceive('getCheckoutUrl')
            ->andReturn('')
        ;

        $payment
            ->shouldReceive('create')
            ->andReturn($payment)
        ;

        $payment
            ->shouldReceive('createMandate')
            ->andReturn($payment)
        ;

        $payment
            ->shouldReceive('createSubscription')
            ->andReturn($payment)
        ;

        $payment
            ->shouldReceive('getSubscription')
            ->andReturn($payment)
        ;

        $payment
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('create')
            ->andReturn($payment)
        ;

        $payments
            ->shouldReceive('withParentId')
            ->andReturn($payment)
        ;

        $payments
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $mock = $this->mocker->mockService('debricked_sylius_billogram_plugin.billogram_api_client', BillogramClient::class);

        $mock
            ->shouldReceive('setApiKey', 'setConfig', 'setIsRecurringSubscription')
        ;

        $mock
            ->shouldReceive('isRecurringSubscription')
            ->andReturn(true)
        ;

        $mock
            ->shouldReceive('isRefunded')
            ->andReturn(false)
        ;

        $mock
            ->shouldReceive('getConfig')
            ->andReturn([
                'times' => 12,
                'interval' => '1 months',
            ])
        ;

        $mock->payments = $payments;
        $mock->customers = $payments;
        $mock->customers_mandates = $payments;
        $mock->customers_subscriptions = $payments;

        $action();

        $this->mocker->unmockAll();
    }

    /**
     * @param callable $action
     */
    public function mockApiCancelledRecurringSubscription(callable $action): void
    {
        $payment = \Mockery::mock('payment', Payment::class);

        $payment->status = SubscriptionStatus::STATUS_CANCELED;

        $payment
            ->shouldReceive('cancelSubscription')
            ->andReturn($payment)
        ;

        $payments = \Mockery::mock('payments');

        $payments
            ->shouldReceive('get')
            ->andReturn($payment)
        ;

        $mock = $this->mocker->mockService('debricked_sylius_billogram_plugin.billogram_api_client', BillogramClient::class);

        $mock
            ->shouldReceive('setApiKey', 'setConfig', 'setIsRecurringSubscription')
        ;

        $mock
            ->shouldReceive('isRecurringSubscription')
            ->andReturn(true)
        ;

        $mock
            ->shouldReceive('isRefunded')
            ->andReturn(false)
        ;

        $mock->customers_subscriptions = $payments;
        $mock->customers = $payments;

        $action();

        $this->mocker->unmockAll();
    }
}
