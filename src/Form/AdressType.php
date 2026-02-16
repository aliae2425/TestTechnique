<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\Company;
use Dom\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Title', TextType::class, ['label' => 'Titre de l\'adresse'])
            ->add('number', TextType::class, ['label' => 'Numéro'])
            ->add('street', TextType::class, ['label' => 'Rue'])
            ->add('ZipCode', TextType::class, ['label' => 'Code Postal'])
            ->add('Country', ChoiceType::class, ['label' => 'Pays',
                            'choices' => [
                                'France' => 'FR',
                                'Belgique' => 'BE',
                                'Suisse' => 'CH',
                                'Canada' => 'CA',
                            ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adress::class,
        ]);
    }
}
