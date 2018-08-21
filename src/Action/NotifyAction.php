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

use Billogram\Exception\Domain\NotFoundException;
use Debricked\SyliusBillogramPlugin\Action\Api\BaseApiAwareAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

final class NotifyAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var GetHttpRequest
     */
    private $getHttpRequest;

    /**
     * @param GetHttpRequest $getHttpRequest
     */
    public function __construct(GetHttpRequest $getHttpRequest)
    {
        $this->getHttpRequest = $getHttpRequest;
    }

    /**
     * {@inheritdoc}
     *
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($this->getHttpRequest);

        if (true === isset($details['billogram_invoice_id'])) {
            try {
                $this->billogramClient->invoices()->fetch($details['billogram_invoice_id']);
                throw new HttpResponse('OK', 200);
            } catch (NotFoundException $exception) {

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
