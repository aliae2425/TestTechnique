<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BusinessQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titled', TextType::class, [
                'label' => 'Intitulé de la question',
                'constraints' => [new NotBlank()],
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description / contexte',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Thème',
                'choices' => [
                    'Modélisation' => 'Modélisation',
                    'Documentation' => 'Documentation',
                    'Collaboration' => 'Collaboration',
                    'Coordination' => 'Coordination',
                    'Plugins' => 'Plugins',
                ],
            ])
            ->add('Reponses', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'prototype_name' => '__answer_idx__',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
