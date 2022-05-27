<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function index(Request $request, UserPasswordHasherInterface $passEncoder, ValidatorInterface $validator, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['label' => 'E-Mail'])
            ->add('password', RepeatedType::class, [
                'type'           => PasswordType::class,
                'required'       => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Password Repeat']
            ])
            ->add('Register', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();

            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword(
                $passEncoder->hashPassword($user, $data['password'])
            );

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return $this->render('register/index.html.twig', [
                    'form'   => $form->createView(),
                    'errors' => $errors
                ]);
            }
            try {
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'The user has been created successfully');
            } catch (Exception $e) {
                $this->addFlash('success', $e->getMessage());
                return $this->redirect($this->generateUrl('app_register'));
            }


            return $this->redirect($this->generateUrl('app_index'));
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
