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

use Billogram\Api\Endpoints\PaymentEndpoint;
use Debricked\SyliusBillogramPlugin\Action\NotifyAction;
use Debricked\SyliusBillogramPlugin\spec\BillogramClientHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PhpSpec\ObjectBehavior;

final class NotifyActionSpec extends ObjectBehavior
{
    function let(GetHttpRequest $getHttpRequest): void
    {
        $this->beConstructedWith($getHttpRequest);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(NotifyAction::class);
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
        Notify $request,
        \ArrayObject $arrayObject,
        GatewayInterface $gateway
    ): void {
        $this->setGateway($gateway);

        $billogramClient = BillogramClientHelper::createBillogramClient();
        $this->setApi($billogramClient);
        $request->getModel()->willReturn($arrayObject);

        $this->execute($request);
    }

    function it_supports_only_notify_request_and_array_access(
        Notify $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
