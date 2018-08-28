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

use Billogram\Exception;
use Billogram\Model\Customer\Customer;
use Billogram\Model\Invoice\Invoice;
use Billogram\Model\Invoice\Item;
use Debricked\SyliusBillogramPlugin\Action\Api\BaseApiAwareAction;
use Debricked\SyliusBillogramPlugin\Request\Api\CreateCustomer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;

final class CaptureAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     *
     * @throws Exception
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (true === isset($details['billogram_invoice_id'])) {
            return;
        }

        $invoice = new Invoice();
        $invoice = $invoice->withCurrency($details['amount']['currency']);
        $customer = new Customer();
        if (isset($details['billogram_customer_no']) === false) {
            $this->gateway->execute($customerDetails = new CreateCustomer($details));
            $details['billogram_customer_no'] = $customerDetails->getModel()['billogram_customer_no'];
        }
        $customer = $customer->withCustomerNo($details['billogram_customer_no']);
        $invoice = $invoice->withCustomer($customer);

        $items = [];
        foreach ($details['items'] as $itemDetails) {
            $itemOfInvoice = new Item();
            if (\array_key_exists('item_no', $itemDetails) === true) {
                $itemOfInvoice = $itemOfInvoice->withItemNo(\strval($itemDetails['item_no']));
            }
            $itemOfInvoice = $itemOfInvoice->withCount($itemDetails['count']);
            $itemOfInvoice = $itemOfInvoice->withTitle($itemDetails['title']);
            if (\array_key_exists('description', $itemDetails) === true) {
                $itemOfInvoice = $itemOfInvoice->withDescription($itemDetails['description']);
            }
            $itemOfInvoice = $itemOfInvoice->withUnit($itemDetails['unit']);
            $itemOfInvoice = $itemOfInvoice->withPrice($itemDetails['price']);
            $itemOfInvoice = $itemOfInvoice->withVat($itemDetails['vat']);
            $itemOfInvoice = $itemOfInvoice->withDiscount($itemDetails['discount']);
            $items[] = $itemOfInvoice;
        }
        $invoice = $invoice->withItems($items);

        $createdInvoice = $this->billogramClient->invoices()->create($invoice->toArray());

        $details['billogram_invoice_id'] = $createdInvoice->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
