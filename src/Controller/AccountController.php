<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/account', name: 'app_', methods: ['GET'])]
class AccountController extends AbstractController
{
    #[Route('', name: 'account', methods: ['GET'])]
    /**
     * @IsGranted("ROLE_USER")
     */
    public function show(): Response
    {
        return $this->render('account/show.html.twig');
    }

    #[Route('/edit', name: 'account_edit', methods: ['GET', 'PATCH'])]
    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Account updated successfully!');

            $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig',
            ['form' => $form->createView()]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route('/change-password', name: 'account_change_password', methods: ['GET', 'PATCH'])]
    public function changePassword(
        Request                      $request,
        EntityManagerInterface       $em,
        UserPasswordEncoderInterface $passwordEncoder,
    ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'current_password_is_required' => true,
            'method' => 'PATCH',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form['plainPassword']->getData()));

            $em->flush();

            $this->addFlash('success', 'Password updated successfully!');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/change_password.html.twig',
            ['form' => $form->createView()]);
    }
}
