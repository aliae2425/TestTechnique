<?php

namespace App\Controller\Admin;

use App\Entity\QuizSession;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpParser\Node\Expr\Yield_;

class QuizSessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizSession::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Informations générales');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('quizTemplate.titre', 'Quiz');
        yield ChoiceField::new('quizTemplate.type', 'Format du quiz')
            ->setChoices([
                'Entrainement' => 'training',
                'Examen' => 'Exam',
            ])
            ->hideOnForm();
        yield TextField::new('participantName', 'Utilisateur / Invitation');
        yield DateTimeField::new('startAt', 'Début');
        yield DateTimeField::new('endAt', 'Fin')->hideOnIndex();
        yield TextField::new('durationString', 'Durée')->hideOnForm();
        yield NumberField::new('finalScore', 'Score Final');

        if ($pageName === Crud::PAGE_DETAIL) {
            yield FormField::addTab('Détails des Réponses');
            yield CollectionField::new('userReponses', '')
                ->setTemplatePath('admin/fields/user_responses_collection.html.twig');
        }
    }
}
