<?php

/*
 *  This file is part of Mathstreams.
 *
 *  Copyright (c) 2015  Sohani Academy <developer@sohaniacademy.com>
 *
 *  Mathstreams is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Magnets\MathgymBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Magnets\MathgymBundle\Entity\User;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class SecurityController extends Controller
{

    public function loginAction(Request $request)
    {
        //if user is already logged in..
        $secAuthChecker = $this->container->get('security.authorization_checker');
        if ($secAuthChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $this->addFlash('info', 'You have already logged in!');
            return $this->redirectToRoute('user');
        }
        return $this->render('MagnetsMathgymBundle:Security:login.body.html.twig');
    }

    public function loginFormAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
//        //if the user exists, then enforce the token limit on login attempts..
//        if ($lastUsername) {
//            $user = $this->get('db_h')->getRep('User')->findOneByEmail($lastUsername);
//            if ($user) {
//                //todo: deduct the tokens etc..
//                //but the issue is: even if we detect the tokens have been exceeded,
//                //still the security layer will intercept the login! So then how can we deny..
//                //temporary solution: after logging in, will detect if the limit was previously violated
//                //and will force a logout.
//            }
//        }
        if ($error) {
            $this->addFlash('danger', $error->getMessage());
        }
        $this->get('security.csrf.token_manager')->refreshToken('authenticate');
        return $this->render('MagnetsMathgymBundle:Security:loginForm.frag.html.twig', ['last_username' => $lastUsername]);
    }

    public function registerAction(Request $request)
    {
        //if user is already logged in..
        $secAuthChecker = $this->container->get('security.authorization_checker');
        if ($secAuthChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $this->addFlash('info', 'No need to register, you already have an account!');
            return $this->redirectToRoute('user');
        }


        $form = $this->createFormBuilder()
            ->add('fullname', TextType::class,
                ['label' => 'Full name (letters and numbers only)',
                    'constraints' => [new NotBlank(),
                        new Length(['max' => 255, 'maxMessage' => 'Full name cannot be longer than {{ limit }} characters']),
                        new Regex(['pattern' => '/^[a-zA-Z0-9\s]+$/', 'message' => 'Only alphanumeric characters allowed']),
                    ]])
            ->add('email', EmailType::class,
                ['label' => 'Email address',
                    'constraints' => [new NotBlank(),
                        new Email()]])
            ->add('password', RepeatedType::class,
                ['type' => PasswordType::class,
                    'invalid_message' => 'Entered passwords don\'t match',
                    'required' => true,
                    'first_options' => ['label' => 'Password (minimum 7 characters)'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'constraints' => [new NotBlank(),
                        new Length(['min' => 7, 'max' => 63,
                            'minMessage' => 'Password must be at least {{ limit }} characters long',
                            'maxMessage' => 'Password must be at most {{ limit }} characters long',
                        ])]
                ])
            ////            ->add('recaptcha', 'ewz_recaptcha', array('mapped' => false))
            ->add('save', SubmitType::class, ['label' => 'Register'])
            ->getForm();

//
//        $form = $this->createFormBuilder($user)
//            ->add('fullname', 'text', array('label' => 'Full name (letters and numbers only)'))
//            ->add('email', 'email', array('label' => 'Email address'))
//            ->add('password', 'repeated', array(
//                'type' => 'password',
//                'invalid_message' => 'Entered passwords don\'t match',
//                'required' => true,
//                'first_options' => array('label' => 'Password (minimum 7 characters)'),
//                'second_options' => array('label' => 'Repeat Password'),))
////            ->add('recaptcha', 'ewz_recaptcha', array('mapped' => false))
//            ->add('save', 'submit', array('label' => 'Register'))
//            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $userRep = $this->get('db_h')->getRep('User');

            $formData = $form->getData();

            $user = $userRep->create($formData['email'], '', $formData['fullname']);

            if (!$user) {
                $this->addFlash('danger', 'This username already exists! Please sign in..');
                return $this->redirectToRoute('login');
            }

            $encoder = $this->container->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $formData['password']);

            $user->setPassword($encoded);

            $userRep->persist($user);
            $this->get('db_h')->flush();//may not be needed, we'll see..

            $this->get('rate_control')->refreshTokens($user);

            $this->addFlash('success', 'Registration complete! Please sign in..');
            return $this->redirectToRoute('login');
        }

        return $this->render('MagnetsMathgymBundle:Security:register.body.html.twig', ['form' => $form->createView()]);
    }

    public function securityCheckAction()
    {
        // The security layer will intercept this request
    }

    public function logoutAction()
    {
        // The security layer will intercept this request
    }

    public function forgotpassAction(Request $request)
    {
        //check if signed in.. 
        //if user is already logged in..
        $secAuthChecker = $this->container->get('security.authorization_checker');
        if ($secAuthChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $this->addFlash('info', 'You have already logged in!');
            return $this->redirectToRoute('user');
        }

        $form = $this->createFormBuilder()
            ->add('email', 'email', array('mapped' => false))
//            ->add('recaptcha', 'ewz_recaptcha')
            ->add('submit', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            //check if user exists.. 
            $userRep = $this->get('db_h')->getRep('User');

            $dbUser = $userRep->findOneBy(['email' => $form['email']->getData()]);
            if ($dbUser == null) {
                $this->addFlash('warning', 'This user does not exist.. please register!');
                return $this->redirectToRoute('register');
            }
            $this->addFlash('success', 'Please check your email now..');

            $tokenRep = $this->get('db_h')->getRep('Token');

            $token = $tokenRep->addToken($dbUser, 'PASS_RESET');
            $mailer = $this->get('mailer');
            $message = $mailer->createMessage()
                ->setSubject('Mathstreams password reset')
                ->setFrom('noreply@mathstreams.com')
                ->setTo($dbUser->getEmail())
                ->setBody($this->renderView('MagnetsMathgymBundle:UI:email_forgotpass.mail.html.twig', ['token_key' => $token->getTkey()]));
            $mailer->send($message);
            return $this->redirectToRoute('index');
        } else {
            return $this->render('MagnetsMathgymBundle:Security:forgotpass.body.html.twig', ['form' => $form->createView()]);
        }
    }

    public function resetpassAction(Request $request, $key)
    {
        //check in the database...
        $tokenRep = $this->get('db_h')->getRep('Token');
        $token = $tokenRep->findOneBy(['tkey' => $key]);
        if ($token != null) {
            //load password reset form for this user..
            //TODO: check expiry also

            $user = $token->getUser();

            $form = $this->createFormBuilder($user)
                ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'Entered passwords don\'t match',
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),))
//                ->add('recaptcha', 'ewz_recaptcha', array('mapped' => false))
                ->add('submit', 'submit')
                ->getForm();

            $form->handleRequest($request);

            if ($form->isValid()) {
                //update the password..
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($encoded);

                $this->addFlash('success', 'Password changed! Please sign in..');

                //delete token!
                $tokenRep->remove($token);
                return $this->redirectToRoute('index');
            } else {
                return $this->render('MagnetsMathgymBundle:Security:resetpass.body.html.twig', ['form' => $form->createView()]);
            }
        } else {
            $this->addFlash('danger', 'Invalid path!');
            return $this->redirectToRoute('index');
        }
    }
}