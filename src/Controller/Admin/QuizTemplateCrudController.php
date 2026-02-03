<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\QuizTemplate;
use App\Form\QuestionType;
use App\Form\QuizRuleType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class QuizTemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizTemplate::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addTab("Généralités");
        yield TextField::new('Titre', 'Titre du Quiz');
        yield ChoiceField::new('mode', 'Type de quiz')
            ->setChoices([
                'Aléatoire' => 'Random',
                'Équilibré' => 'Balanced',
                'Fixe' => 'Fixed',
            ]);
        yield ChoiceField::new('type', 'Format du quiz')
            ->setChoices([
                'Entrainement' => 'training',
                'Examen' => 'Exam',
            ]);
        yield IntegerField::new('timeLimit', 'Durée (secondes)')
            ->setHelp('Laisser vide pour illimité');
            
        yield FormField::addTab("Règles");
        yield CollectionField::new('Rules', 'Règles')
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryType(QuizRuleType::class)
            ->setFormTypeOption('by_reference', false)
            ->hideOnIndex();

        yield FormField::addTab("Questions");
        yield CollectionField::new('Questions', 'Questions du Quiz')
             ->allowAdd(true)
             ->allowDelete(true)
             ->setEntryType(QuestionType::class)
             ->setFormTypeOption('by_reference', false)
             ->hideOnIndex();
    }
}
