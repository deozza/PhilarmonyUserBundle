<?php
namespace Deozza\PhilarmonyUserBundle\Controller;

use Deozza\PhilarmonyUserBundle\Entity\Registration;
use Deozza\PhilarmonyUserBundle\Form\PatchCurrentUserType;
use Deozza\PhilarmonyUserBundle\Form\PatchUserType;
use Deozza\PhilarmonyUserBundle\Form\RegistrationType;
use Deozza\PhilarmonyUserBundle\Service\UserSchemaLoader;
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

    public function __construct(ResponseMaker $responseMaker, EntityManagerInterface $entityManager, PaginatorInterface $paginator, FormErrorSerializer $serializer, UserSchemaLoader $userSchemaLoader)
    {
        $this->em = $entityManager;
        $this->paginator = $paginator;
        $this->response = $responseMaker;
        $this->serializer = $serializer;
        $this->userEntity = $userSchemaLoader->loadUserEntityClass();
        $this->userRepository = $userSchemaLoader->loadUserRepositoryClass();

    }

    /**
     * @Route("users", name="get_users", methods={"GET"})
     */
    public function getUsersAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');

        $filters = $request->query->get("filterBy", []);
        $repository = new $this->userRepository;

        $usersQuery = $repository->findAllFiltered($filters);

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
    public function getSpecificUserAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');

        $repository = new $this->userRepository;
        $user = $repository->find($id);
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

        $repository = new $this->userRepository;
        $userAlreadyExist = $this->em->getRepository($repository)->findByUsernameOrEmail($registration->getLogin(), $registration->getEmail());

        if ($userAlreadyExist) {
            return $this->response->badRequest("User already exists. Chose another email and another login");
        }

        $user = new $this->userEntity;
        $user->setUsername($registration->getLogin());
        $user->setEmail($registration->getEmail());

        $password = $encoder->encodePassword($user, $registration->getPassword());
        $user->setPassword($password);
        $user->setRegisterDate(new \DateTime('now'));

        $this->em->persist($user);
        $this->em->flush();

        return $this->response->created($user, ['user_basic']);
    }

    /**
     * @Route("user/current", name="patch_current_user", methods={"PATCH"})
     */
    public function patchCurrentAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(PatchCurrentUserType::class, $user);

        $patchedContent = json_decode($request->getContent(), true);

        $form->submit($patchedContent);
        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }

        $passwordIsValid = $encoder->isPasswordValid($user, $user->getPlainPassword());
        if(!$passwordIsValid)
        {
            return $this->response->badRequest("Your password is invalid");
        }

        if($user->getNewPassword() && $user->getNewPassword() != $user->getPlainPassword())
        {
            $user->setPassword($encoder->encodePassword($user, $user->getNewPassword()));
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->response->ok($user, ['user_basic']);
    }

    /**
     * @Route("user/{id}", name="patch_specific_user", methods={"PATCH"})
     */
    public function patchSpecificUserAction(Request $request, UserPasswordEncoderInterface $encoder, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Forbidden access');
        $repository = new $this->userRepository;
        $user = $repository->find($id);

        $form = $this->createForm(PatchUserType::class, $user);

        $patchedContent = json_decode($request->getContent(), true);

        $form->submit($patchedContent);
        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }

        if($user->getNewPassword() && $user->getNewPassword() != $user->getPlainPassword())
        {
            $user->setPassword($encoder->encodePassword($user, $user->getNewPassword()));
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->response->ok($user, ['user_basic','user_advanced']);
    }
}