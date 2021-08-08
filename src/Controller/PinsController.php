<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(PinRepository $pinRepository): Response
    {
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);
        return $this->render('pins/index.html.twig',
            compact('pins'));
    }

    //NOTE (previous code): @Security("is_granted('ROLE_USER') && user.isVerified()")
    #[Route('/pins/create', name: 'app_pins_create', methods: ['GET', 'POST'])]
    /**
     * @IsGranted("PIN_CREATE")
     */
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
    ): Response
    {
        $pin = new Pin();
        $form = $this->createForm(PinType::class, $pin); // ici $pin optionnel b/c PinType::configureOptions

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created!');

            return $this->redirectToRoute('app_home');
        }


        return $this->render('pins/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_show', methods: ['GET'])]
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig',
            compact('pin'));
    }

    //NOTE (previous code): @Security("is_granted('ROLE_USER') && user.isVerified() && user == pin.getUser()")
    //NOTE2 (other syntax): @IsGranted("PIN_MANAGE", subject="pin")
    #[Route('/pins/{id<[0-9]+>}/edit', name: 'app_pins_edit', methods: ['GET', 'PUT'])]
    /**
     * @Security("is_granted('PIN_MANAGE', pin)")
     */
    public function edit(
        Pin $pin,
        Request $request,
        EntityManagerInterface $em): Response
    {
        $form_ = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form_->handleRequest($request);

        if ($form_->isSubmitted() && $form_->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated!');

            return $this->redirectToRoute('app_home');
        }

        $form = $form_->createView();
        return $this->render('pins/edit.html.twig',
            compact('form', 'pin'));
    }

    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_delete', methods: ['DELETE'])]
    /**
     * @Security("is_granted('PIN_MANAGE', pin)")
     */
    public function delete(
        Pin $pin,
        Request $request,
        EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token')))
        {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted!');
        }

        return $this->redirectToRoute('app_home');
    }
}
