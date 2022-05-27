<?php

namespace App\Security\Controller;

use App\Security\Entity\User;
use App\Security\Repository\UserRepository;
use App\Security\Form\AdminUserType;
use App\Security\Form\AdminUserPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AdminUsersController  - Admin User Manager
 * @package App\Security\Controller
 * @IsGranted("ROLE_ADMIN",statusCode=401, message="No access! Get out!")
 * @Route("/admin/user")
 */
class AdminUsersController extends AbstractController
{
    
    /** @var UserPasswordHasherInterface */
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * User List Index
     * 
     * @param UserRepository $userRepository
     * @Route("/", name="admin_user_index", methods={"GET"})
     * @return Response
     * @return RedirectResponse
     */
    public function index(UserRepository $userRepository): Response
    {
        try {
            $user = new User();
            $form = $this->createForm(AdminUserType::class, $user, [
                'mode' => 'create',
                'action' => $this->generateUrl('admin_user_create')
            ]);
            return $this->render('@security-admin/user/admin/user-index.html.twig', [
                'users' => $userRepository->findAll(),
                'form' => $form->createView()
            ]);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_dashboard'));
        }
    }

    /**
     * Create User
     * 
     * @param UserRepository $userRepository
     * @param Request $request
     * @Route("/new", name="admin_user_create", methods={"GET", "POST"})
     * @return Response
     * @return RedirectResponse
     */
    public function create(UserRepository $userRepository, Request $request): Response
    {
        try {
            $user = new User();
            $form = $this->createForm(AdminUserType::class, $user, [
                'mode' => 'create'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $plaintextPassword = $form->get('password')->getData();
                // hash the password (based on the security.yaml config for the $user class)
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);
                $userRepository->add($user);

                $this->addFlash(
                    'info',
                    'Saved new user with id '.$user->getId()
                );
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('@security-admin/user/admin/user-create.html.twig', [
                'form' => $form->createView()
            ]);
        }  catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_user_index'));
        }
    }

    /**
     * Edit User
     * 
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @Route("/{id}/edit", name="admin_user_edit", methods={"GET", "POST"})
     * @return Response
     * @return RedirectResponse
     */
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        try {
            $form = $this->createForm(AdminUserType::class, $user, [
                'mode' => 'edit'
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $userRepository->add($user);
                $this->addFlash(
                    'info',
                    'Updated new user with id '.$user->getId()
                );
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('@security-admin/user/admin/user-edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_user_index'));
        }
    }

    /**
     * Edit User Password
     * 
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @Route("/{id}/edit-password", name="admin_user_password_edit", methods={"GET", "POST"})
     * @return Response
     * @return RedirectResponse
     */
    public function editPassword(Request $request, User $user, UserRepository $userRepository): Response
    {
        try {
            $form = $this->createForm(AdminUserPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $plaintextPassword = $form->get('password')->getData();
                // hash the password (based on the security.yaml config for the $user class)
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);
                $userRepository->add($user);
                
                $this->addFlash(
                    'info',
                    'Updated new user with id '.$user->getId()
                );
                return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('@security-admin/user/admin/user-edit-password.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_user_index'));
        }
    }

    /**
     * Show User (Admin View)
     * 
     * @param User $user
     * @Route("/{id}", name="admin_user_show", methods={"GET"})
     * @return Response
     * @return RedirectResponse
     */
    public function show(User $user): Response
    {
        try {
            return $this->render('@security-admin/user/admin/user-show.html.twig', [
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_user_index'));
        }
    }

    /**
     * Delete User
     * 
     * @param Request $request
     * @param User $user
     * @param UserRepository $userRepository
     * @Route("/{id}", name="admin_user_delete", methods={"POST"})
     * @return RedirectResponse
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): RedirectResponse
    {
        try {
            if ($this->isCsrfTokenValid('delete-user', $request->request->get('token'))) {
                $userRepository->remove($user);
                $this->addFlash(
                    'info',
                    'User have been deleted'
                );
            } else {
                $this->addFlash(
                    'warning',
                    'ERROR : User have not been deleted'
                ); 
            }
            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                $e->getMessage()
            );
            return $this->redirect($this->generateUrl('admin_user_index'));
        }
    }
}
