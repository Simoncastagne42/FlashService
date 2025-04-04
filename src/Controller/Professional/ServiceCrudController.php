<?php

namespace App\Controller\Professional;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            Field::new('name', 'Nom du service')
                ->setFormTypeOptions([
                    'label' => 'Nom du service'
                ]),
            Field::new('description', 'Description')
                ->setFormTypeOptions([
                    'label' => 'Description du service'
                ]),
            Field::new('price', 'Prix')
                ->setFormTypeOptions([
                    'label' => 'Prix du service'
                ]),
        ];

        return $fields;
    }
}
