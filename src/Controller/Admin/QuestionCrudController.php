<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Answer;
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
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter; // Changed from TextFilter to ChoiceFilter for better UX if needed, or TextFilter
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use App\Repository\QuestionRepository;

class QuestionCrudController extends AbstractCrudController
{
    private QuestionRepository $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $question = new Question();
        
        // Initialiser avec 4 réponses vides
        for ($i = 0; $i < 4; $i++) {
            $question->addReponse(new Answer());
        }

        return $question;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplates([
                'crud/index' => 'admin/question/list_with_tabs.html.twig',
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('level')->setChoices([
                'Débutant' => 'Débutant',
                'Intermédiaire' => 'Intermédiaire',
                'Avancé' => 'Avancé',
            ]));
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            $view = $this->getContext()->getRequest()->query->get('view');
            if ($view === 'home') {
                 // Get sort params from query string
                 $request = $this->getContext()->getRequest();
                 $sortField = $request->query->get('sort_field', 'totalAttempts');
                 $sortOrder = $request->query->get('sort_order', 'DESC');
                 
                 $stats = $this->questionRepository->getQuestionStats($sortField, $sortOrder);
                 
                 $responseParameters->set('questionStats', $stats);
                 $responseParameters->set('currentSortField', $sortField);
                 $responseParameters->set('currentSortOrder', $sortOrder);
            }
        }
        return $responseParameters;
    }

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
            ->allowAdd(false)
            ->allowDelete(false)
            ->setEntryType(AnswerType::class)
            ->setFormTypeOption('by_reference', false)
            ->hideOnIndex();
    }

}
