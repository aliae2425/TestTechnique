<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Dom\Text;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\Form\Button;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
             ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        
        yield FormField::addTab('Utilisateur');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('primaryRole', 'Type de compte')
            ->setFormTypeOption('disabled', 'disabled')
            ->setLabel('Type de compte');
        yield EmailField::new('email', 'Email');
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Entreprise' => 'ROLE_ENTREPRISEz',
                'Administrateur' => 'ROLE_ADMIN',
            ])
            ->hideOnIndex()
            ->allowMultipleChoices();
        yield BooleanField::new('isVerified', 'Vérifié');

        yield FormField::addTab('Détails personnels');
        yield TextField::new('username', 'Nom d\'utilisateur');
        yield TextField::new('name', 'Prénom');
        yield TextField::new('lastName', 'Nom de famille');
        yield IntegerField::new('lvl', 'Niveau')
            ->setFormTypeOption('disabled', 'disabled');
        yield IntegerField::new('xp', 'XP');
        
        yield FormField::addTab('Resultats Quiz');
        yield CollectionField::new('quizSessions', 'Historique des Quiz')
            ->setTemplatePath('admin/user/quiz_history.html.twig')
            ->onlyOnDetail();
        yield FormField::addTab('formations');

        }
    
}
