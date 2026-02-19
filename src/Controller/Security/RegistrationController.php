<?php

namespace App\Controller\Security;

use App\Entity\Company;
use App\Entity\User;
use App\Form\InvitedRegistrationFormType;
use App\Form\RegistrationFormType;
use App\Repository\InvitationRepository;
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
            //         ->htmlTemplate('security/registration/confirmation_email.html.twig')
            // );
    
             $this->emailVerifier->sendEmailConfirmationCode($user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@418.archi', 'Mail teabot'))
                    ->to((string) $user->getEmail())
                    ->subject('Votre code de vérification')
                    ->htmlTemplate('security/registration/code_email.html.twig')
            );

            // do anything else you need here, like send an email
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_verify_code');
        }

        return $this->render('security/registration/register.html.twig', [
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
                    ->htmlTemplate('security/registration/code_email.html.twig')
            );

            // do anything else you need here, like send an email
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_verify_code');
        }

        return $this->render('security/registration/registerEntreprise.html.twig', [
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

        return $this->render('security/registration/verify_code.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/register/invitation/{token}', name: 'app_register_invitation')]
    public function registerFromInvitation(
        string $token,
        Request $request,
        InvitationRepository $invitationRepo,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_dashboard');
        }

        $invitation = $invitationRepo->findOneBy(['token' => $token]);

        if (!$invitation || $invitation->isExpired()) {
            $this->addFlash('error', "Ce lien d'invitation est invalide ou a expiré.");
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(InvitedRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setEmail((string) $invitation->getEmail());
            $user->setName($invitation->getPrenom());
            $user->setLastName($invitation->getNom());
            $user->setRoles(['ROLE_ENTREPRISE', 'ROLE_ENTREPRISE_USER']);
            $user->setIsVerified(true);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            if ($invitation->getCompany()) {
                $user->setCompany($invitation->getCompany());
            }

            // Invalide le token en le vidant
            $invitation->setToken('used_' . $invitation->getToken());
            $invitation->setExpiresAt(new \DateTimeImmutable('-1 second'));

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user, 'form_login', 'main');

            $this->addFlash('success', 'Votre compte a été créé avec succès. Bienvenue !');
            return $this->redirectToRoute('app_user_dashboard');
        }

        return $this->render('security/registration/register_invited.html.twig', [
            'form' => $form,
            'invitation' => $invitation,
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
