<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Question;
use App\Entity\QuizTemplate;
use App\Repository\QuestionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
        $company = $options['company'];

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
            ->add('Questions', EntityType::class, [
                'class' => Question::class,
                'choice_label' => fn(Question $q) => sprintf('[%s] %s', $q->getLevel() ?? '?', $q->getTitled()),
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'label' => 'Questions à inclure',
                'query_builder' => function (QuestionRepository $repo) use ($company) {
                    return $repo->createAvailableForCompanyQB($company);
                },
                'group_by' => fn(Question $q) => $q->getCompany() ? '⭐ Vos questions' : 'Questions plateforme',
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
            'company' => null,
        ]);
        $resolver->setAllowedTypes('company', ['null', Company::class]);
    }
}
