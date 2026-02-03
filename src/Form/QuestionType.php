<?php

namespace App\Form;

use App\Entity\Question;
use phpDocumentor\Reflection\PseudoTypes\False_;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titled')
            ->add('level')
            ->add('type')
            ->add('image')
            ->add('Description')
            ->add('Reponses', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => AnswerType::class,
                'allow_add' => False,
                'allow_delete' => False,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
