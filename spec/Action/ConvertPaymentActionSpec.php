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

namespace spec\Debricked\SyliusBillogramPlugin\Action;

use Debricked\SyliusBillogramPlugin\Action\ConvertPaymentAction;
use Debricked\SyliusBillogramPlugin\spec\BillogramClientHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Convert;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\Shipment;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Order\Model\Adjustment;

final class ConvertPaymentActionSpec extends ObjectBehavior
{
    function let(PaymentDescriptionProviderInterface $paymentDescriptionProvider): void
    {
        $this->beConstructedWith($paymentDescriptionProvider);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ConvertPaymentAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_executes(
        Convert $request,
        PaymentInterface $payment,
        OrderInterface $order,
        CustomerInterface $customer,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress,
        GatewayInterface $gateway,
        PaymentDescriptionProviderInterface $paymentDescriptionProvider
    ): void {
        $this->setApi(BillogramClientHelper::createBillogramClient());
        $this->setGateway($gateway);
        $customer->getFullName()->willReturn('Jan Kowalski');
        $customer->getEmail()->willReturn('shop@example.com');
        $customer->getPhoneNumber()->willReturn('0702112233');
        $customer->getId()->willReturn(1);
        $order->getId()->willReturn(1);
        $order->getCurrencyCode()->willReturn('SEK');
        $order->getLocaleCode()->willReturn('pl_PL');
        $order->getCustomer()->willReturn($customer);
        $payment->getOrder()->willReturn($order);
        $payment->getCurrencyCode()->willReturn('EUR');
        $paymentDescriptionProvider->getPaymentDescription($payment)->willReturn('description');
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $order->getBillingAddress()->willReturn($billingAddress);
        $billingAddress->getPostcode()->willReturn('21423');
        $billingAddress->getCity()->willReturn('Malmö');
        $billingAddress->getStreet()->willReturn('Gågatan 1');
        $billingAddress->getCountryCode()->willReturn('SE');

        $order->getShippingAddress()->willReturn($shippingAddress);
        $shippingAddress->getPostcode()->willReturn('21423');
        $shippingAddress->getCity()->willReturn('Malmö');
        $shippingAddress->getStreet()->willReturn('Gågatan 1');
        $shippingAddress->getCountryCode()->willReturn('SE');

        $orderItem1 = new OrderItem();
        $orderItem1->setProductName('A big shirt');
        $orderItem1->setVariantName('Black');
        $orderItem1->setUnitPrice(7000);
        $orderItemUnit1 = new OrderItemUnit($orderItem1);
        $orderItemUnit2 = new OrderItemUnit($orderItem1);

        $orderItem2 = new OrderItem();
        $taxAdjustment = new Adjustment();
        $taxAdjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);
        $taxAdjustment->setAmount(200);
        $orderItem2->addAdjustment($taxAdjustment);
        $orderItem2->setProductName('A small shirt');
        $orderItem2->setVariantName('White');
        $orderItem2->setUnitPrice(800);
        $orderItemUnit3 = new OrderItemUnit($orderItem2);

        $order->getItems()->willReturn(
            new ArrayCollection(
                [
                    $orderItem1,
                    $orderItem2,
                ]
            )
        );

        $shipment1 = new Shipment();
        $shippingMethod1 = new ShippingMethod();
        $shippingMethod1->setCurrentLocale('en');
        $shippingMethod1->setName('First shipping method');
        $shipment1->setMethod($shippingMethod1);
        $shipment2 = new Shipment();
        $shippingMethod2 = new ShippingMethod();
        $shippingMethod2->setCurrentLocale('en');
        $shippingMethod2->setName('Second shipping method');
        $shipment2->setMethod($shippingMethod2);
        $order->getShipments()->willReturn(
            new ArrayCollection(
                [
                    $shipment1,
                    $shipment2,
                ]
            )
        );
        $order->getShippingTotal()->willReturn(
            5000
        );
        $order->getTaxTotal()->willReturn(
            2600
        );
        $order->getTotal()->willReturn(
            13000
        );
        $order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)->willReturn(0);
        $order->getAdjustmentsTotalRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->willReturn(2500);

        $request->setResult(
            [
                'amount' => [
                    'currency' => 'SEK',
                ],
                'description' => 'description',
                'invoice_no' => 1,
                'customerId' => 1,
                'fullName' => 'Jan Kowalski',
                'email' => 'shop@example.com',
                'phoneNumber' => '0702112233',
                'billingAddress' =>
                    [
                        'postCode' => '21423',
                        'city' => 'Malmö',
                        'street' => 'Gågatan 1',
                        'country' => 'SE',
                    ],
                'shippingAddress' =>
                    [
                        'postCode' => '21423',
                        'city' => 'Malmö',
                        'street' => 'Gågatan 1',
                        'country' => 'SE',
                    ],
                'items' =>
                    [
                        [
                            'item_no' => null,
                            'count' => 2,
                            'title' => 'A big shirt - Black',
                            'price' => 70,
                            'vat' => 0,
                            'discount' => 0,
                            'unit' => '-',
                        ],
                        [
                            'item_no' => null,
                            'count' => 1,
                            'title' => 'A small shirt - White',
                            'price' => 8, // Billogram multiplies this price with the VAT
                            'vat' => 25,
                            'discount' => 0,
                            'unit' => '-',
                        ],
                        [
                            'count' => 1,
                            'title' => 'First shipping method & Second shipping method',
                            'price' => 50,
                            'vat' => 25,
                            'discount' => 0,
                            'unit' => '-',
                        ],
                    ],
            ]
        )->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_only_convert_request_payment_source_and_array_to(
        Convert $request,
        PaymentInterface $payment
    ): void {
        $request->getSource()->willReturn($payment);
        $request->getTo()->willReturn('array');

        $this->supports($request)->shouldReturn(true);
    }
}
