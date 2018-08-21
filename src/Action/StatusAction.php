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
use Payum\Core\Request\GetStatusInterface;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $details = $payment->getDetails();

        if (!isset($details['billogram_invoice_id'])) {
            $request->markNew();

            return;
        }

        $paymentData = $this->billogramClient->invoices()->fetch($details['billogram_invoice_id']);

        switch ($paymentData->getState()) {

            case BillogramGatewayFactoryInterface::BILLOGRAM_STATE_UNATTESTED:
                $request->markPending();

                break;
            case BillogramGatewayFactoryInterface::BILLOGRAM_STATE_UNPAID:
                $request->markCaptured();

                break;
            case BillogramGatewayFactoryInterface::BILLOGRAM_STATE_DELETED:
                $request->markCanceled();

                break;
            case BillogramGatewayFactoryInterface::BILLOGRAM_STATE_SENDING:
                $request->markPending();

                break;
//            case PaymentStatus::STATUS_FAILED:
//                $request->markFailed();
//
//                break;
//            case PaymentStatus::STATUS_EXPIRED:
//                $request->markExpired();
//
//                break;
            default:
                $request->markUnknown();

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof PaymentInterface;
    }
}
