<?php

namespace App\Form;

use App\Entity\QuizRule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme',ChoiceType::class, [
                'label' => 'Thème',
                'choices' => [
                    'Modélisation' => 'Modélisation',
                    'Documentation' => 'Documentation',
                    'Collaboration' => 'Collaboration',
                    'Coordination' => 'Coordination',
                    'Plugins' => 'Plugins',
                ]
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ]
            ])
            ->add('Quantity', IntegerType::class, [
                'label' => 'Nombre de questions'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizRule::class,
        ]);
    }
}
