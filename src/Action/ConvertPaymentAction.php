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

namespace Debricked\SyliusBillogramPlugin\Action;

use Debricked\SyliusBillogramPlugin\Action\Api\BaseApiAwareAction;
use Debricked\SyliusBillogramPlugin\BillogramGatewayFactoryInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class ConvertPaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var PaymentDescriptionProviderInterface
     */
    private $paymentDescriptionProvider;

    /**
     * @param PaymentDescriptionProviderInterface $paymentDescriptionProvider
     */
    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $details = [
            'amount' =>
                [
                    'currency' => $currency->code,
                ],
            'description' => $this->paymentDescriptionProvider->getPaymentDescription($payment),
            'invoice_no' => $order->getId(),
            'customerId' => $customer->getId() ?? null,
            'fullName' => $customer->getFullName() ?? '',
            'email' => $customer->getEmail() ?? '',
            'phoneNumber' => $customer->getPhoneNumber() ?? '',
            'billingAddress' =>
                [
                    'postCode' => $billingAddress->getPostcode(),
                    'city' => $billingAddress->getCity(),
                    'street' => $billingAddress->getStreet(),
                    'country' => $billingAddress->getCountryCode(),
                ],
            'shippingAddress' =>
                [
                    'postCode' => $shippingAddress->getPostcode(),
                    'city' => $shippingAddress->getCity(),
                    'street' => $shippingAddress->getStreet(),
                    'country' => $shippingAddress->getCountryCode(),
                ],
        ];

        $details['amount']['currency'] = \in_array(
            $order->getCurrencyCode(),
            BillogramGatewayFactoryInterface::CURRENCIES_AVAILABLE
        ) ? $order->getCurrencyCode() : 'SEK';

        $details['items'] = [];
        foreach ($order->getItems() as $orderItem) {
            $orderItemArray = [];

            $orderItemArray['item_no'] = $orderItem->getId();
            $orderItemArray['count'] = $orderItem->getQuantity();
            $orderItemArray['title'] = $orderItem->getProductName().' - '.$orderItem->getVariantName();
            $orderItemArray['price'] = $orderItem->getUnitPrice() / 100;
            if ($orderItem->getTaxTotal() > 0) {
                $orderItemArray['vat'] = (($orderItem->getTotal() / $orderItem->getTaxTotal()) - 1) * 100;
            }
            else {
                $orderItemArray['vat'] = 0;
            }
            $orderItemArray['discount'] = ($orderItem->getQuantity() * ($orderItem->getUnitPrice()
                        - $orderItem->getDiscountedUnitPrice())) / 100;
            $orderItemArray['unit'] = '-';

            $details['items'][] = $orderItemArray;
        }

        $request->setResult($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array';
    }
}
