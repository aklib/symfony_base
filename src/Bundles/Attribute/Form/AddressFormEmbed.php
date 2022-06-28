<?php
/**
 * Class AddressEmbeddedForm
 * @package App\Bundles\Attribute\Type
 *
 * since: 26.06.2022
 * author: alexej@kisselev.de
 */

namespace App\Bundles\Attribute\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressFormEmbed extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('street', TextType::class, [
                'attr'        => ['placeholder' => 'street name and number'],
                'row_attr'    => [
                    'class' => 'col-md-12 col-xxl-8',
                ],
                'label'       => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a street name',
                    ]),
                ],
            ])
            ->add('zip_code', TextType::class, [
                'attr'        => ['placeholder' => 'zip code'],
                'row_attr'    => [
                    'class' => 'col-md-3 col-xxl-4',
                ],
                'label'       => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter zip code',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'attr'        => ['placeholder' => 'city'],
                'row_attr'    => [
                    'class' => 'col-md-3 col-xxl-4',
                ],
                'label'       => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter city',
                    ]),
                ],
            ])
            ->add('country', CountryType::class, [
                'attr'        => ['placeholder' => 'country'],
                'label'       => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter country',
                    ]),
                ],
            ]);
    }
}