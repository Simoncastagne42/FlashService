<?php

namespace App\Controller\Professional;

use App\Entity\Reservation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\Choice;

class ReservationCrudController extends AbstractCrudController
{

    public function __construct(private Security $security, private EntityRepository $entityRepository) 
    {
        $this->security = $security; 
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('service', 'Service'),
            AssociationField::new('client', 'Client')
                ->formatValue(function ($value, $entity) {
                    return $entity->getClient()?->getFirstName() . ' ' . $entity->getClient()?->getLastName();
                }),
            DateTimeField::new('startTime', 'Début'),
            DateTimeField::new('endTime', 'Fin'),
            ChoiceField::new('status', 'Statut')
            ->formatValue(function ($value) {
                $color = match ($value) {
                    'confirmée' => 'success', // vert
                    'en attente' => 'warning', // jaune
                    'annulée' => 'danger', // rouge
                    default => 'secondary' // gris
                };
        
                return sprintf('<span class="badge badge-%s">%s</span>', $color, ucfirst($value));
            })
            ->setTemplateName('crud/field/choice')
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $professional = $user?->getProfessional();
    
        if (!$professional) {
            throw new \LogicException('Seuls les professionnels peuvent accéder à cette page.');
        }
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->join('entity.service', 's')
            ->andWhere('s.professional = :professional')
            ->setParameter('professional', $professional);
    
        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW) // Les pros ne créent pas directement les réservations
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }









}
