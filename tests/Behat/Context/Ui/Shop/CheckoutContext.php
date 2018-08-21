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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Order\ShowPageInterface;
use Tests\Debricked\SyliusBillogramPlugin\Behat\Mocker\BillogramMocker;
use Tests\Debricked\SyliusBillogramPlugin\Behat\Page\External\PaymentPageInterface;
use Tests\Debricked\SyliusBillogramPlugin\Behat\Page\Shop\Checkout\CompletePageInterface;

final class CheckoutContext implements Context
{
    /**
     * @var CompletePageInterface
     */
    private $summaryPage;

    /**
     * @var ShowPageInterface
     */
    private $orderDetails;

    /**
     * @var BillogramMocker
     */
    private $billogramApiMocker;

    /**
     * @var PaymentPageInterface
     */
    private $paymentPage;

    /**
     * @param CompletePageInterface $summaryPage
     * @param ShowPageInterface     $orderDetails
     * @param BillogramMocker       $billogramApiMocker
     * @param PaymentPageInterface  $paymentPage
     */
    public function __construct(
        CompletePageInterface $summaryPage,
        ShowPageInterface $orderDetails,
        BillogramMocker $billogramApiMocker,
        PaymentPageInterface $paymentPage
    ) {
        $this->summaryPage = $summaryPage;
        $this->orderDetails = $orderDetails;
        $this->billogramApiMocker = $billogramApiMocker;
        $this->paymentPage = $paymentPage;
    }

    /**
     * @When I confirm my order with Billogram payment
     * @Given I have confirmed my order with Billogram payment
     */
    public function iConfirmMyOrderWithBillogramPayment(): void
    {
        $this->billogramApiMocker->mockCreateInvoice(function () {
            $this->summaryPage->confirmOrder();
        });
    }

}
