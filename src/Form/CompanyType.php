<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, ['label' => 'Nom de l\'entreprise'])
            ->add('Contry', TextType::class, ['label' => 'Pays'])
            ->add('ActivitySector',ChoiceType::class,
                     ['label' => 'Secteur d\'activité'
                     , 'choices' => [
                        'Architecte' => 'Architecte',
                        'Bureau d\'étude' => 'Bureau d\'étude',
                        'Construction' => 'Construction',
                        'Autre' => 'Autre',
                     ]])
            ->add('Size', ChoiceType::class, 
            ['label' => 'Taille de l\'entreprise',
             'choices' => [
                '1-10' => '1-10',
                '11-50' => '11-50',
                '51-200' => '51-200',
                '201+' => '201+',
             ]])
            ->add('logo', TextType::class, [
                'label' => 'Logo (URL)',
                'required' => false,
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('adresses', CollectionType::class, [
                'entry_type' => AdressType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
