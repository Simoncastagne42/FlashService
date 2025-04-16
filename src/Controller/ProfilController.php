<?php


namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProfilClientType;
use App\Form\ProfilProType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Professional;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;




final class ProfilController extends AbstractController

{
    #[Route('/my-account', name: 'app_my_account')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myAccount(): Response
    {
        return $this->render('account/base_my_account.html.twig');
    }


    #[Route('/my-account/profil-infos', name: 'app_profil_infos')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {

        /**
         * @var User $user
         */


        $user = $this->getUser();

        if (in_array('ROLE_PROFESSIONNEL', $user->getRoles())) {


            $pro = $user->getProfessional();
            if (!$pro) {
                $pro = new Professional();
                $pro->setUser($user);
                $entityManager->persist($pro);
            }
            $isPro = true;
            $isClient = false;
            $form = $this->createForm(ProfilProType::class, $pro);
        } else {
            $client = $user->getClient();
            if (!$client) {
                $client = new Client();
                $client->setUser($user);
                $entityManager->persist($client);
            }
            $isClient = true;
            $isPro = false;
            $form = $this->createForm(ProfilClientType::class, $client);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profil_infos');
        }

        return $this->render('account/profil/profil_infos.html.twig', [
            'form' => $form->createView(),
            'isPro' => $isPro,
            'isClient' => $isClient,
        ]);
    }

    #[Route('/my-account/reservations', name: 'app_profil_reservations')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function reservations(): Response
    {
        return $this->render('account/profil/reservations.html.twig');
        
    }
}
