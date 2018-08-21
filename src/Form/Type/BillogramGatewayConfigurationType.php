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

namespace Debricked\SyliusBillogramPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

final class BillogramGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'debricked_sylius_billogram_plugin.ui.username',
                    'constraints' =>
                        [
                            new NotBlank(
                                [
                                    'message' => 'debricked_sylius_billogram_plugin.username.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                        ],
                ]
            )
            ->add(
                'apiKey',
                TextType::class,
                [
                    'label' => 'debricked_sylius_billogram_plugin.ui.api_key',
                    'constraints' =>
                        [
                            new NotBlank(
                                [
                                    'message' => 'debricked_sylius_billogram_plugin.api_key.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                            new Regex(
                                [
                                    'message' => 'debricked_sylius_billogram_plugin.api_key.invalid',
                                    'groups' => ['sylius'],
                                    'pattern' => '/^[a-fA-F0-9]+$/',
                                ]
                            ),
                            new Length(
                                [
                                    'minMessage' => 'debricked_sylius_billogram_plugin.api_key.min_length',
                                    'groups' => ['sylius'],
                                    'min' => 32,
                                ]
                            ),
                        ],
                ]
            )
            ->add(
                'apiUrl',
                TextType::class,
                [
                    'label' => 'debricked_sylius_billogram_plugin.ui.api_url',
                    'constraints' =>
                        [
                            new NotBlank(
                                [
                                    'message' => 'debricked_sylius_billogram_plugin.api_url.not_blank',
                                    'groups' => ['sylius'],
                                ]
                            ),
                            new Url(),
                        ],
                ]
            );
    }
}
