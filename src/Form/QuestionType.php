<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titled')
            ->add('image')
            ->add('Description')
            ->add('Reponses', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $question = $event->getData();
            if (!$question) {
                $event->setData(new Question());
            }
        });
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
            'empty_data' => function (FormInterface $form) {
                return new Question();
            },
        ]);
    }
}

