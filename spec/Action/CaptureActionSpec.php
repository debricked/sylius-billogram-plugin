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

use Debricked\SyliusBillogramPlugin\Action\CaptureAction;
use Debricked\SyliusBillogramPlugin\spec\BillogramClientHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\PaymentInterface;

final class CaptureActionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(CaptureAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_implements_gateway_aware_interface(): void
    {
        $this->shouldHaveType(GatewayAwareInterface::class);
    }

    function it_executes(
        Capture $request,
        ArrayObject $arrayObject,
        PaymentInterface $payment,
        GatewayInterface $gateway
    ): void {
        $billogramClient = BillogramClientHelper::createBillogramClient();
        $this->setGateway($gateway);
        $this->setApi($billogramClient);
        $arrayObject->toUnsafeArray()->willReturn([]);
        $request->getModel()->willReturn($arrayObject);
        $request->getFirstModel()->willReturn($payment);

        $arrayObject->offsetGet('amount')->shouldBeCalled();
        $arrayObject->offsetGet('amount')->willReturn(
            [
                'currency' => 'SEK',
            ]
        );
        $arrayObject->offsetExists('billogram_customer_no')->shouldBeCalledOnce();
        $arrayObject->offsetSet('billogram_customer_no', Argument::type("integer"))->shouldBeCalledOnce();
        $arrayObject->offsetGet('billogram_customer_no')->shouldBeCalled();
        $arrayObject->offsetGet('billogram_customer_no')->willReturn(1);
        $arrayObject->offsetGet('items')->shouldBeCalled();
        $item2103 = [];
        $item2103['item_no'] = '2103';
        $item2103['count'] = 3;
        $item2103['title'] = 'A big shirt'.' - '.'Black';
        $item2103['price'] = 1000;
        $item2103['vat'] = ((900 / 720) - 1) * 100;
        $item2103['discount'] = 3 * (1000
                - 900);
        $item2103['unit'] = '-';
        $arrayObject->offsetGet('items')->willReturn(
            [
                $item2103,
            ]
        );
        $arrayObject->offsetExists('billogram_invoice_id')->shouldBeCalled();
        $arrayObject->offsetSet('billogram_invoice_id', Argument::type("string"))->shouldBeCalled();

        $this->execute($request);
    }

    function it_supports_only_capture_request_and_array_access(
        Capture $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
