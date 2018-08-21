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

namespace Tests\Debricked\SyliusBillogramPlugin\Behat\Page\External;

use Behat\Mink\Session;
use Payum\Core\Security\TokenInterface;
use Sylius\Behat\Page\Page;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\BrowserKit\Client;

final class PaymentPage extends Page implements PaymentPageInterface
{
    /**
     * @var RepositoryInterface
     */
    private $securityTokenRepository;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Session $session
     * @param array $parameters
     * @param RepositoryInterface $securityTokenRepository
     * @param Client $client
     */
    public function __construct(
        Session $session,
        array $parameters,
        RepositoryInterface $securityTokenRepository,
        Client $client
    ) {
        parent::__construct($session, $parameters);

        $this->securityTokenRepository = $securityTokenRepository;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function capture(): void
    {
        $captureToken = $this->findToken();

        $this->getDriver()->visit($captureToken->getTargetUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function notify(array $postData): void
    {
        $notifyToken = $this->findToken('notify');

        $this->client->request('POST', $notifyToken->getTargetUrl(), $postData);
    }

    /**
     * @param array $urlParameters
     *
     * @return string
     */
    protected function getUrl(array $urlParameters = [])
    {
        return 'https://api.billogram.nl';
    }

    /**
     * @param string $type
     *
     * @return TokenInterface
     */
    private function findToken(string $type = 'capture'): TokenInterface
    {
        $tokens = [];

        /** @var TokenInterface $token */
        foreach ($this->securityTokenRepository->findAll() as $token) {
            if (strpos($token->getTargetUrl(), $type)) {
                $tokens[] = $token;
            }
        }

        if (count($tokens) > 0) {
            return end($tokens);
        }

        throw new \RuntimeException('Cannot find capture token, check if you are after proper checkout steps');
    }
}
