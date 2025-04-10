<?php

namespace App\Controller\Professional;

use App\Entity\TimeSlot;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class TimeSlotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TimeSlot::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Gestion des Créneaux')  // Page de liste
            ->setPageTitle('new', 'Ajouter un Créneau')    // Page de création
            ->setPageTitle('edit', 'Modifier un Créneau')  // Page d'édition
            ->setPageTitle('detail', 'Détails du Créneau') // Page de détail
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        $fields = [
            DateField::new('date')
                ->setFormat('dd/MM/yyyy'),

            TimeField::new('heureDebut')
                -> setLabel('Heure de début')
                -> setFormat('HH:mm'),

            TimeField::new('heureFin')
                -> setLabel('Heure de fin')
                -> setFormat('HH:mm'),

        ];

        return $fields;
    }
}
