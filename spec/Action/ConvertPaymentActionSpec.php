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
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\PaymentInterface;

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
        $payment->getAmount()->willReturn(445535);
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
        $orderItemUnit1 = new OrderItemUnit($orderItem1);
        $orderItemUnit2 = new OrderItemUnit($orderItem1);

        $orderItem2 = new OrderItem();
        $orderItem2->setProductName('A small shirt');
        $orderItem2->setVariantName('White');
        $orderItemUnit3 = new OrderItemUnit($orderItem2);

        $order->getItems()->willReturn(
            new ArrayCollection(
                [
                    $orderItem1,
                    $orderItem2,
                ]
            )
        );

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
                            'price' => 0,
                            'vat' => 0,
                            'discount' => 0,
                            'unit' => '-',
                        ],
                        [
                            'item_no' => null,
                            'count' => 1,
                            'title' => 'A small shirt - White',
                            'price' => 0,
                            'vat' => 0,
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
