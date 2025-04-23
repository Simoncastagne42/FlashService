<?php


namespace App\Controller\Professional;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReservationCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private EntityRepository $entityRepository
    ) {}

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
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

        $qb = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // On filtre les réservations liées aux services du professionnel connecté
        $qb->join('entity.service', 's')
           ->andWhere('s.professional = :professional')
           ->setParameter('professional', $professional);

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('service', 'Service')
                ->setFormTypeOption('choice_label', 'name'),

            AssociationField::new('client', 'Client')
                ->formatValue(function ($value, $entity) {
                    $client = $entity->getClient();
                    return $client ? $client->getFirstName() . ' ' . $client->getLastName() : 'Inconnu';
                }),

            DateField::new('timeSlot.date', 'Date'),
            TimeField::new('timeSlot.heureDebut', 'Heure de début')->setFormat('HH:mm'),
            TimeField::new('timeSlot.heureFin', 'Heure de fin')->setFormat('HH:mm'),

            ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'En attente' => Reservation::STATUS_PENDING,
                'Confirmée' => Reservation::STATUS_CONFIRMED,
                'Annulée' => Reservation::STATUS_CANCELLED,
            ])
            ->renderAsBadges([
                Reservation::STATUS_PENDING => 'warning',
                Reservation::STATUS_CONFIRMED => 'success',
                Reservation::STATUS_CANCELLED => 'danger',
            ])
        ];
    }

}