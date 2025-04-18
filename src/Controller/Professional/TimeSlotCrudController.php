<?php

namespace App\Controller\Professional;

use App\Entity\TimeSlot;
use App\Entity\Professionnal;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;

class TimeSlotCrudController extends AbstractCrudController
{
   

    public function __construct(private Security $security, private EntityRepository $entityRepository)
    {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return TimeSlot::class;
    }

     // Filtrer les services pour n'afficher que ceux du professionnel connecté
     public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder
     {
         /** @var User */
         $user = $this->security->getUser();
         $professional = $user->getProfessional();
     
         $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
         $response->andWhere('entity.professional = :professional')->setParameter('professional', $professional);
     
         return $response;
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

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::EDIT, 'TIMESLOT_EDIT')
            ->setPermission(Action::DELETE, 'TIMESLOTDELETE')
            ->setPermission(Action::DETAIL, 'TIMESLOT_VIEW');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof TimeSlot) return;

        /** @var Professionnal */
        $user = $this->security->getUser();
        $professional = $user->getProfessional();

        if ($professional) {
            $entityInstance->setProfessional($professional);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}


