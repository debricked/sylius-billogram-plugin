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

namespace spec\Debricked\SyliusBillogramPlugin\Action\Api;

use Debricked\SyliusBillogramPlugin\Action\Api\BaseApiAwareAction;
use Debricked\SyliusBillogramPlugin\Action\Api\CreateCustomerAction;
use Debricked\SyliusBillogramPlugin\Request\Api\CreateCustomer;
use Debricked\SyliusBillogramPlugin\spec\BillogramClientHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use PhpSpec\ObjectBehavior;

final class CreateCustomerActionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(CreateCustomerAction::class);
    }

    function it_implements_action_interface(): void
    {
        $this->shouldHaveType(ActionInterface::class);
    }

    function it_implements_api_aware_interface(): void
    {
        $this->shouldHaveType(ApiAwareInterface::class);
    }

    function it_extends_base_api_aware(): void
    {
        $this->shouldHaveType(BaseApiAwareAction::class);
    }

    function it_executes(
        CreateCustomer $request,
        ArrayObject $arrayObject
    ): void {
        $billogramClient = BillogramClientHelper::createBillogramClient();
        $this->setApi($billogramClient);
        /* @noinspection PhpUnhandledExceptionInspection */
        $customerId = \random_int(1, 999999);
        $arrayObject->offsetGet('fullName')->willReturn('Jan Kowalski');
        $arrayObject->offsetGet('email')->willReturn('shop@example.com');
        $arrayObject->offsetGet('phoneNumber')->willReturn('0702707070');
        $arrayObject->offsetGet('customerId')->willReturn($customerId);

        $address =
            [
                'postCode' => '21423',
                'city' => 'Malmö',
                'street' => 'Gågatan 1',
                'country' => 'SE',
            ];
        $arrayObject->offsetGet('billingAddress')->willReturn($address);
        $arrayObject->offsetGet('shippingAddress')->willReturn($address);

        //$customer->withCompanyType('individual')->shouldBeCalledOnce();
        $request->getModel()->willReturn($arrayObject);

        $arrayObject->offsetSet('billogram_customer_no', $customerId)->shouldBeCalledOnce();

        $this->execute($request);
    }

    function it_supports_only_create_customer_request_and_array_access(
        CreateCustomer $request,
        \ArrayAccess $arrayAccess
    ): void {
        $request->getModel()->willReturn($arrayAccess);

        $this->supports($request)->shouldReturn(true);
    }
}
