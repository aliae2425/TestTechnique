<?php

namespace App\Form;

use App\Entity\Invitation;
use App\Entity\QuizTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvitationLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quizTemplate', EntityType::class, [
                'class' => QuizTemplate::class,
                'choice_label' => 'titre',
                'label' => 'Quiz associé',
                'placeholder' => 'Sélectionner un quiz',
                'constraints' => [new NotBlank(message: 'Veuillez sélectionner un quiz.')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
        ]);
    }
}
