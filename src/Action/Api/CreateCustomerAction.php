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

namespace Debricked\SyliusBillogramPlugin\Action\Api;

use Billogram\Exception\Domain\NotFoundException;
use Billogram\Exception\Domain\ValidationException;
use Billogram\Model\Customer\Customer;
use Billogram\Model\Customer\CustomerBillingAddress;
use Billogram\Model\Customer\CustomerContact;
use Billogram\Model\Customer\CustomerDeliveryAddress;
use Debricked\SyliusBillogramPlugin\Request\Api\CreateCustomer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

final class CreateCustomerAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * {@inheritdoc}
     *
     * @param CreateCustomer $request
     *
     * @throws ValidationException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            $createdCustomer = $this->billogramClient->customers()->fetch($model['customerId']);
        } catch (NotFoundException $e) {
            $customerContact = new CustomerContact();
            $customerContact = $customerContact->withName($model['fullName']);
            $customerContact = $customerContact->setEmail($model['email']);
            $customerContact = $customerContact->setPhone($model['phoneNumber']);

            $customerBillingAddress = new CustomerBillingAddress();
            $customerBillingAddress = $customerBillingAddress->withZipCode($model['billingAddress']['postCode']);
            $customerBillingAddress = $customerBillingAddress->withCity($model['billingAddress']['city']);
            $customerBillingAddress = $customerBillingAddress->withStreetAddress($model['billingAddress']['street']);
            $customerBillingAddress = $customerBillingAddress->withCountry($model['billingAddress']['country']);

            $customerDeliveryAddress = new CustomerDeliveryAddress();
            $customerDeliveryAddress = $customerDeliveryAddress->withZipCode($model['shippingAddress']['postCode']);
            $customerDeliveryAddress = $customerDeliveryAddress->withCity($model['shippingAddress']['city']);
            $customerDeliveryAddress = $customerDeliveryAddress->withStreetAddress($model['shippingAddress']['street']);
            $customerDeliveryAddress = $customerDeliveryAddress->withCountry($model['shippingAddress']['country']);

            $customer = new Customer();
            $customer = $customer->withCustomerNo($model['customerId']);
            $customer = $customer->withName($model['fullName']);
            $customer = $customer->withContact($customerContact);
            $customer = $customer->withCompanyType('individual');
            $customer = $customer->withAddress($customerBillingAddress);
            $customer = $customer->withDeliveryAddress($customerDeliveryAddress);

            $createdCustomer = $this->billogramClient->customers()->create($customer->toArray());
        }

        $model['billogram_customer_no'] = $createdCustomer->getCustomerNo();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof CreateCustomer &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
