<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Form\AnswerType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    // public function configureCrud(Crud $crud): Crud
    // {
    //     return $crud
    //         ->overrideTemplates([
    //             'crud/index' => 'admin/question/index.html.twig',
    //         ]);
    // }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield FormField::addColumn(7);
        yield FormField::addFieldset('Détails de la question');
        yield TextField::new('titled', 'Titre');
        yield TextEditorField::new('Description', 'Description')
            ->hideOnIndex();

        yield FormField::addColumn(5);
        yield FormField::addFieldset('Classification');
        yield ChoiceField::new('level', 'Niveau')
            ->setChoices([
                'Facile' => 'Facile',
                'Moyen' => 'Moyen',
                'Difficile' => 'Difficile',
            ]);
        yield ChoiceField::new('type', 'Type')
            ->setChoices([
                'Modélisation' => 'Modélisation',
                'Documentation' => 'Documentation',
                'Collaboration' => 'Collaboration',
                'Interfaces' => 'Interfaces',
                'Analyse' => 'Analyse',
            ]);
        yield ImageField::new('image', 'Image')->hideOnIndex()
            ->setUploadDir('public/uploads/images/questions/')
            ->setBasePath('/uploads/images/questions/')
            ->setUploadedFileNamePattern(
                fn (UploadedFile $file): string => sprintf('upload_%d_%s.%s', random_int(1, 999), $file->getClientOriginalName(), $file->guessExtension())
            );
        
        yield FormField::addRow();
        yield FormField::addColumn(12);
        yield FormField::addFieldset('Réponses');
        yield CollectionField::new('Reponses', 'Réponses')
            ->setEntryType(AnswerType::class)
            ->hideOnIndex();
    }

}
