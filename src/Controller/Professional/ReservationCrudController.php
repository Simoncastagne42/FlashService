<?php


namespace App\Controller\Professional;

use App\Entity\Reservation;
use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class ReservationCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private EntityRepository $entityRepository,
        private MailService $mailService
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
        yield AssociationField::new('service', 'Service')
            ->setFormTypeOption('choice_label', 'name')
            ->setDisabled(true);

        yield AssociationField::new('client', 'Client')
            ->formatValue(function ($value, $entity) {
                $client = $entity->getClient();
                return $client ? $client->getFirstName() . ' ' . $client->getLastName() : 'Inconnu';
            })
            ->setDisabled(true);

        yield TextField::new('clientEmail', 'Email du client')
            ->setDisabled(true);

        yield TextField::new('clientPhone', 'Téléphone du client')
            ->setDisabled(true)
            ->formatValue(function ($value, $entity) {
                $phone = $entity->getClient()?->getPhone();
                return $phone ? trim(chunk_split(preg_replace('/\D/', '', $phone), 2, ' ')) : null;
            });
        yield TextField::new('clientAdress', 'Adresse du client')
            ->setDisabled(true);
        yield TextField::new('clientZipCode', 'Code postal du client')
            ->setDisabled(true);
        yield TextField::new('clientCity', 'Ville du client')
            ->setDisabled(true);
        yield DateField::new('timeSlot.date', 'Date');

        yield TimeField::new('timeSlot.heureDebut', 'Heure de début')
            ->setFormat('HH:mm');

        yield TimeField::new('timeSlot.heureFin', 'Heure de fin')
            ->setFormat('HH:mm');

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'En attente' => Reservation::STATUS_PENDING,
                'Confirmée' => Reservation::STATUS_CONFIRMED,
                'Annulée' => Reservation::STATUS_CANCELLED,
            ])
            ->renderAsBadges([
                Reservation::STATUS_PENDING => 'warning',
                Reservation::STATUS_CONFIRMED => 'success',
                Reservation::STATUS_CANCELLED => 'danger',
            ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Reservation) return;

        parent::persistEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'La réservation a bien été ajoutée !');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::deleteEntity($entityManager, $entityInstance);
        $this->addFlash('success', 'La réservation a bien été supprimée !');
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Reservation) return;

        parent::updateEntity($entityManager, $entityInstance);

        if ($entityInstance->getStatut() === Reservation::STATUS_CONFIRMED) {
            $this->addFlash('success', 'La réservation a bien été confirmée !');

            // Envoi du mail de confirmation au client
            $this->mailService->sendReservationConfirmed(
                $entityInstance->getService()->getName(),
                $entityInstance->getClient()->getFullName(),
                $entityInstance->getClient()->getUser()->getEmail(),
                $entityInstance->getService()->getProfessional()->getCompagnyName(),
                $entityInstance->getService()->getProfessional()->getCityCompagny(),
                $entityInstance->getTimeSlot()->getDate(),
                $entityInstance->getHeureDebut()?->format('H:i'),
                $entityInstance->getService()->getPrice()
            );
        } elseif ($entityInstance->getStatut() === Reservation::STATUS_CANCELLED) {
            $this->addFlash('success', 'La réservation a bien été annulée !');
            $this->mailService->sendReservationCancelled(
                $entityInstance->getService()->getName(),
                $entityInstance->getClient()->getFullName(),
                $entityInstance->getClient()->getUser()->getEmail(),
                $entityInstance->getService()->getProfessional()->getCompagnyName(),
                $entityInstance->getService()->getProfessional()->getCityCompagny(),
                $entityInstance->getTimeSlot()->getDate(),
                $entityInstance->getHeureDebut()?->format('H:i'),
                $entityInstance->getService()->getPrice()
            );
    
        } else {
            $this->addFlash('success', 'La réservation a bien été modifiée !');
            $this->mailService->sendReservationUpdated(
                $entityInstance->getService()->getName(),
                $entityInstance->getClient()->getFullName(),
                $entityInstance->getClient()->getUser()->getEmail(),
                $entityInstance->getService()->getProfessional()->getCompagnyName(),
                $entityInstance->getService()->getProfessional()->getCityCompagny(),
                $entityInstance->getTimeSlot()->getDate(),
                $entityInstance->getHeureDebut()?->format('H:i'),
                $entityInstance->getService()->getPrice()
            );
        }
    }
}
