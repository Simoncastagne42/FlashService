<?php
namespace App\Controller\Professional;

use App\Entity\TimeSlot;
use App\Entity\User;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;

class TimeSlotCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private EntityRepository $entityRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return TimeSlot::class;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        CollectionFilterCollection $filters
    ): QueryBuilder {
        /** @var User $user */
        $user = $this->security->getUser();
        $professional = $user?->getProfessional();

        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.professional = :professional')
                 ->setParameter('professional', $professional);

        return $response;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $professional = $user?->getProfessional();

        return [
            AssociationField::new('service', 'Service associé')
                ->setFormTypeOption('choice_label', 'name')
                ->setFormTypeOption('query_builder', function (ServiceRepository $repo) use ($professional) {
                    return $repo->createQueryBuilder('s')
                        ->where('s.professional = :pro')
                        ->setParameter('pro', $professional);
                }),
            DateField::new('date', 'Date du créneau'),
            TimeField::new('heureDebut', 'Heure de début')->setFormat('HH:mm'),
            TimeField::new('heureFin', 'Heure de fin')->setFormat('HH:mm'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof TimeSlot) return;

        /** @var User $user */
        $user = $this->security->getUser();
        $professional = $user?->getProfessional();

        if ($professional) {
            $entityInstance->setProfessional($professional);
        }

        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'Le créneau a bien été ajouté !');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'Le créneau a bien été supprimé !');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof TimeSlot) return;

        parent::updateEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'Le créneau a bien été modifié !');
    }
}
