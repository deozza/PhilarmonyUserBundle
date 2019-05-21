<?php
namespace Deozza\PhilarmonyUserBundle\Controller;

use Deozza\PhilarmonyUserBundle\Entity\ApiTokenKind;
use Deozza\PhilarmonyUserBundle\Form\CredentialsType;
use Deozza\PhilarmonyUserBundle\Entity\ApiToken;
use Deozza\PhilarmonyUserBundle\Entity\Credentials;
use Deozza\PhilarmonyUserBundle\Entity\User;
use Deozza\PhilarmonyUserBundle\Repository\ApiTokenKindRepository;
use Deozza\PhilarmonyUserBundle\Repository\ApiTokenRepository;
use Deozza\PhilarmonyUserBundle\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Deozza\PhilarmonyBundle\Service\FormManager\FormErrorSerializer;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;

/**
 * User controller.
 *
 * @Route("api/auth-token")
 */
class AuthController extends AbstractController
{
    public function __construct(ResponseMaker $responseMaker, EntityManagerInterface $em ,FormErrorSerializer $formErrorSerializer)
    {
        $this->em = $em;
        $this->response = $responseMaker;
        $this->formErrorSerializer = $formErrorSerializer;
    }

    /**
     * @Route("s", name="post_auth_token", methods={"POST"})
     */
    public function postTokenAction(Request $request, UserRepository $userRepository, ApiTokenKindRepository $tokenKindRepository, UserPasswordEncoderInterface $encoder)
    {
        $credentials = new Credentials();
        $credentialsType = new \ReflectionClass(CredentialsType::class);
        $postedCredentials = $this->processForm->process($request, $credentialsType->getName() , $credentials);

        if(!is_a($postedCredentials, Credentials::class))
        {
            return $postedCredentials;
        }

        $user = $userRepository->findByUsernameOrEmail($postedCredentials->getLogin(), $postedCredentials->getLogin());

        if(!$user || $user->getActive() == false)
        {
            return $this->response->badRequest("Invalid credentials");
        }

        $isPasswordValid= $encoder->isPasswordValid($user, $postedCredentials->getPassword());

        if(!$isPasswordValid)
        {
            $user->setLastFailedLogin(new \DateTime('now'));
            $this->em->persist($user);
            $this->em->flush();
            return $this->response->badRequest("Invalid credentials");
        }

        $env = new Dotenv();
        $env->load(__DIR__."/../../../.env");
        $secret = getenv("APP_SECRET");
        $token = ["username" => $user->getUsername(), "exp"=> date_create("+1 day")->format('U')];
        $tokenKind = $tokenKindRepository->findOneByKind(ApiTokenKind::AUTH);

        $authToken = new ApiToken($user, JWT::encode($token, $secret), $tokenKind);
        $this->em->persist($authToken);

        $user->setLastLogin(new \DateTime('now'));
        $this->em->persist($user);

        $this->em->flush();
        return $this->response->created($authToken);
    }

    /**
     * @Route("/{id}", name="delete_auth_token", methods={"DELETE"})
     */
    public function deleteCurrentTokenAction(Request $request, ApiTokenRepository $tokenRepository, $id)
    {
        $authToken = $tokenRepository->findOneById($id);
        $currentToken = $this->getUser()->getId();


        if(!$authToken || $authToken->getUser()->getId() != $currentToken)
        {
            return $this->response->badRequest('Invalid credentials');
        }
        $this->em->remove($authToken);
        $this->em->flush();

        return $this->response->empty();
    }

}
