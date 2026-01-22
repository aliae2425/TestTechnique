<?php

namespace App\Form;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', null, [
                'label' => 'Texte de la réponse',
                'row_attr' => ['class' => 'col-md-10'],
            ])
            ->add('is_correct', null, [
                'label' => 'Bonne réponse ?',
                'row_attr' => ['class' => 'col-md-2'],
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('feedback', null, [
                'label' => 'Explication (Feedback)',
                'row_attr' => ['class' => 'col-md-12'],
                'attr' => ['placeholder' => 'Pourquoi c\'est juste/faux ?'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
        ]);
    }
}
