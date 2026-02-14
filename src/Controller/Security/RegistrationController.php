<?php

namespace App\Controller\Security;

use App\Entity\Company;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\VerificationCodeType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_dashboard');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('noreply@418.archi', 'Mail teabot'))
            //         ->to((string) $user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );
    
             $this->emailVerifier->sendEmailConfirmationCode($user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@418.archi', 'Mail teabot'))
                    ->to((string) $user->getEmail())
                    ->subject('Votre code de vérification')
                    ->htmlTemplate('registration/code_email.html.twig')
            );

            // do anything else you need here, like send an email
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_verify_code');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/register/entreprise', name: 'app_register_entreprise')]
    public function registerEntreprise(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_dashboard');
        }
        $user = new User();
        $company = new Company();
        $company->setName('Entreprise'); // Valeur par défaut, à remplacer par un champ de formulaire si besoin
        $user->setCompany($company); // Associer l'entreprise au nouvel utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_ENTREPRISE']);

            $entityManager->persist($user);
            $entityManager->persist($company);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmationCode($user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@418.archi', 'Mail teabot'))
                    ->to((string) $user->getEmail())
                    ->subject('Votre code de vérification')
                    ->htmlTemplate('registration/code_email.html.twig')
            );

            // do anything else you need here, like send an email
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_verify_code');
        }

        return $this->render('registration/registerEntreprise.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/register/verify/code', name: 'app_verify_code')]
    public function verifyCode(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isVerified()) {
            return $this->redirectToRoute('app_user_dashboard');
        }

        $form = $this->createForm(VerificationCodeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['code'] === $user->getVerificationCode()) {
                $user->setIsVerified(true);
                $user->setVerificationCode(null);
                $entityManager->flush();

                $this->addFlash('success', 'Votre email a été vérifié avec succès.');
                return $this->redirectToRoute('app_user_dashboard');
            } else {
                $this->addFlash('verify_code_error', 'Code incorrect.');
            }
        }

        return $this->render('registration/verify_code.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
