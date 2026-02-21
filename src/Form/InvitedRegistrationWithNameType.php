<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitedRegistrationWithNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: "L'email est obligatoire."),
                    new Email(message: "L'adresse email n'est pas valide."),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'mapped' => false,
                'constraints' => [new NotBlank(message: 'Le prénom est obligatoire.')],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'mapped' => false,
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'toggle' => true,
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir un mot de passe.'),
                    new Length(
                        min: 6,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        max: 4096,
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
