<?php

namespace App\Form;

use App\Entity\QuizTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuizTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Titre', TextType::class, [
                'label' => 'Titre du quiz',
                'constraints' => [new NotBlank()],
            ])
            ->add('Type', TextType::class, [
                'label' => 'Type / catégorie',
            ])
            ->add('Mode', ChoiceType::class, [
                'label' => 'Mode',
                'choices' => [
                    'Fixed (sélection manuelle de questions)' => 'Fixed',
                    'Balanced (règles par thème/niveau)' => 'Balanced',
                ],
            ])
            ->add('timeLimit', IntegerType::class, [
                'label' => 'Limite de temps (secondes, laisser vide = sans limite)',
                'required' => false,
            ])
            ->add('Questions', CollectionType::class, [
                'entry_type' => BusinessQuestionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'label' => false,
                'prototype_name' => '__question_idx__',
            ])
            ->add('Rules', CollectionType::class, [
                'entry_type' => QuizRuleType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizTemplate::class,
        ]);
    }
}
