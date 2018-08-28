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
use Sylius\Component\Core\Model\AdjustmentInterface;
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

        $fullName = null;
        if (empty($fullName = $customer->getFullName())) {
            if (empty($fullName = $billingAddress->getFullName())) {
                $fullName = '';
            }
        }
        $phoneNumber = null;
        if (empty($phoneNumber = $customer->getPhoneNumber())) {
            if (empty($phoneNumber = $billingAddress->getPhoneNumber())) {
                $phoneNumber = '';
            }
        }

        $details = [
            'amount' =>
                [
                    'currency' => $currency->code,
                ],
            'description' => $this->paymentDescriptionProvider->getPaymentDescription($payment),
            'invoice_no' => $order->getId(),
            'customerId' => $customer->getId() ?? null,
            'fullName' => $fullName,
            'email' => $customer->getEmail() ?? '',
            'phoneNumber' => $phoneNumber,
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
            $vat = 0;
            if ($orderItem->getTaxTotal() > 0) {
                $vat = $orderItem->getTaxTotal() / ($orderItem->getTotal() - $orderItem->getTaxTotal());
            }
            $orderItemArray['price'] = $this->formatPrice($orderItem->getUnitPrice() * ($vat + 1));
            $orderItemArray['vat'] = $vat * 100;
            $orderItemArray['discount'] = $this->formatPrice(
                $orderItem->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_ITEM_PROMOTION_ADJUSTMENT)
            );
            $orderItemArray['unit'] = '-';

            $details['items'][] = $orderItemArray;
        }

        $shippingItem = [];
        $shippingItem['count'] = 1;
        foreach ($order->getShipments() as $shipment) {
            if (empty($shipmentTitle = $shipment->getMethod()->getName()) === false) {
                if (\array_key_exists('title', $shippingItem) === true) {
                    $shippingItem['title'] .= ' & ';
                }
                else {
                    $shippingItem['title'] = '';
                }
                $shippingItem['title'] .= $shipmentTitle;
            }
            if (empty($shipmentDescription = $shipment->getMethod()->getDescription()) === false) {
                if (\array_key_exists('description', $shippingItem) === true) {
                    $shippingItem['description'] = ' & ';
                }
                else {
                    $shippingItem['description'] = '';
                }
                $shippingItem['description'] .= $shipmentDescription;
            }
        }
        $shippingItem['price'] = $this->formatPrice($order->getShippingTotal());
        if ($order->getTaxTotal() > 0) {
            $shippingItem['vat'] = $this->formatPrice(
                $order->getAdjustmentsTotalRecursively(AdjustmentInterface::TAX_ADJUSTMENT)
            );
        }
        else {
            $shippingItem['vat'] = 0;
        }
        $shippingItem['discount'] = $this->formatPrice(
            $order->getAdjustmentsTotalRecursively(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT)
        );
        $shippingItem['unit'] = '-';
        $details['items'][] = $shippingItem;

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

    /**
     * @param float $price
     *
     * @return int
     */
    private function formatPrice(float $price): int
    {
        return intval(\round($price / 100, 0));
    }
}
