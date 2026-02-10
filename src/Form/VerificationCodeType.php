<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class VerificationCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code de vérification',
                'attr' => [
                    'placeholder' => '123456',
                    'autocomplete' => 'off',
                    'class' => 'text-center text-2xl tracking-widest'
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez entrer le code reçu par email',
                    ),
                    new Length(
                        min: 6,
                        max: 6,
                        exactMessage: 'Le code doit contenir exactement {{ limit }} chiffres',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
