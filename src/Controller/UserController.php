<?php
namespace Deozza\PhilarmonyUserBundle\Controller;

use Deozza\PhilarmonyUserBundle\Entity\Registration;
use Deozza\PhilarmonyUserBundle\Entity\User;
use Deozza\PhilarmonyUserBundle\Form\PatchCurrentUserType;
use Deozza\PhilarmonyUserBundle\Form\PatchUserType;
use Deozza\PhilarmonyUserBundle\Form\RegistrationType;
use Deozza\PhilarmonyUserBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterface;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Deozza\ResponseMakerBundle\Service\FormErrorSerializer;

/**
 * User controller.
 *
 * @Route("api/")
 */
class UserController extends AbstractController
{

    public function __construct(ResponseMaker $responseMaker, EntityManagerInterface $entityManager, PaginatorInterface $paginator, FormErrorSerializer $serializer)
    {
        $this->em = $entityManager;
        $this->paginator = $paginator;
        $this->response = $responseMaker;
        $this->serializer = $serializer;
    }

    /**
     * @Route("users", name="get_users", methods={"GET"})
     */
    public function getUsersAction(Request $request, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');

        $filters = $request->query->get("filterBy", []);

        $usersQuery = $userRepository->findAllFiltered($filters);

        $users = $this->paginator->paginate(
            $usersQuery,
            $request->query->getInt("page", 1),
            $request->query->getInt("limit", 10)
        );

        return $this->response->okPaginated($users, ['user_basic','user_advanced', 'user_id']);
    }

    /**
     * @Route("user/current", name="get_current_user", methods={"GET"})
     */
    public function getCurrentUserAction()
    {
        return $this->response->ok($this->getUser(), ['user_basic']);
    }

    /**
     * @Route("user/{id}", name="get_specific_user", methods={"GET"})
     */
    public function getSpecificUserAction(UserRepository $userRepository, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');

        $user = $userRepository->find($id);
        if(empty($user))
        {
            return $this->response->notFound("User with id %s not found", $id);
        }

        return $this->response->ok($user, ['user_basic','user_advanced']);
    }

    /**
     * @Route("users", name="post_users", methods={"POST"})
     */
    public function postUsersAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $registration = new Registration();
        $form = $this->createForm(RegistrationType::class, $registration);
        $postedRegistration = json_decode($request->getContent(), true);

        $form->submit($postedRegistration);
        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }

        $userAlreadyExist = $this->em->getRepository(User::class)->findByUsernameOrEmail($registration->getLogin(), $registration->getEmail());

        if ($userAlreadyExist) {
            return $this->response->badRequest("User already exists. Chose another email and another login");
        }

        $user = new User();
        $user->setUsername($registration->getLogin());
        $user->setEmail($registration->getEmail());

        $password = $encoder->encodePassword($user, $registration->getPassword());
        $user->setPassword($password);
        $user->setRegisterDate(new \DateTime('now'));

        $this->em->persist($user);

        return $this->response->created($user, ['user_basic']);
    }

    /**
     * @Route("user/current", name="patch_current_user", methods={"PATCH"})
     */
    public function patchCurrentAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $patchType = new \ReflectionClass(PatchCurrentUserType::class);
        $patchedUser = $this->processForm->process($request, $patchType->getName(), $user);
        if(!is_a($patchedUser, User::class))
        {
            return $patchedUser;
        }

        $passwordIsValid = $encoder->isPasswordValid($patchedUser, $patchedUser->getPlainPassword());
        if(!$passwordIsValid)
        {
            return $this->response->badRequest("Your password is invalid");
        }

        if($patchedUser->getNewPassword() && $patchedUser->getNewPassword() != $patchedUser->getPlainPassword())
        {
            $patchedUser->setPassword($encoder->encodePassword($patchedUser, $patchedUser->getNewPassword()));
        }

        $this->em->persist($patchedUser);
        $this->em->flush();
        return $this->response->ok($patchedUser, ['user_basic']);
    }

    /**
     * @Route("user/{id}", name="patch_specific_user", methods={"PATCH"})
     */
    public function patchSpecificUserAction(Request $request, UserPasswordEncoderInterface $encoder, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');
        $user = $this->em->getRepository(User::class)->find($id);
        $patchType = new \ReflectionClass(PatchUserType::class);
        $patchedUser = $this->processForm->process($request, $patchType->getName(), $user);

        if(!is_a($patchedUser, User::class))
        {
            return $patchedUser;
        };


        if($patchedUser->getNewPassword() && $patchedUser->getNewPassword() != $patchedUser->getPlainPassword())
        {
            $patchedUser->setPassword($encoder->encodePassword($patchedUser, $patchedUser->getNewPassword()));
        }

        $this->em->persist($patchedUser);
        $this->em->flush();
        return $this->response->ok($patchedUser, ['user_basic','user_advanced']);
    }
}