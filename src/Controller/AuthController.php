<?php
namespace Deozza\PhilarmonyUserBundle\Controller;

use Deozza\ResponseMakerBundle\Service\FormErrorSerializer;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Deozza\PhilarmonyUserBundle\Form\CredentialsType;
use Deozza\PhilarmonyUserBundle\Entity\ApiToken;
use Deozza\PhilarmonyUserBundle\Entity\Credentials;
use Deozza\PhilarmonyUserBundle\Repository\ApiTokenRepository;
use Deozza\PhilarmonyUserBundle\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * User controller.
 *
 * @Route("api/")
 */
class AuthController extends AbstractController
{
    public function __construct(EntityManagerInterface $em, ResponseMaker $responseMaker, FormErrorSerializer $serializer)
    {
        $this->em = $em;
        $this->response = $responseMaker;
        $this->serializer = $serializer;
    }

    /**
     * @Route("auth-tokens", name="post_auth_token", methods={"POST"})
     */
    public function postTokenAction(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);
        $postedCredentials = json_decode($request->getContent(), true);
        $form->submit($postedCredentials);
        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }

        $user = $userRepository->findByUsernameOrEmail($credentials->getLogin(), $credentials->getLogin());

        if(empty($user) || $user->getActive() == false)
        {
            return $this->response->badRequest("Invalid credentials");
        }

        $isPasswordValid= $encoder->isPasswordValid($user, $credentials->getPassword());

        if(!$isPasswordValid)
        {
            $user->setLastFailedLogin(new \DateTime('now'));
            $this->em->persist($user);
            $this->em->flush();
            return $this->response->badRequest("Invalid credentials");
        }

        $env = new Dotenv();
        $env->load($this->getParameter("kernel.project_dir")."/.env");
        $secret = getenv("APP_SECRET");
        $token = ["username" => $user->getUsername(), "exp"=> date_create("+1 day")->format('U')];

        $authToken = new ApiToken($user, JWT::encode($token, $secret));
        $this->em->persist($authToken);

        $user->setLastLogin(new \DateTime('now'));
        $this->em->persist($user);

        $this->em->flush();
        return $this->response->created($authToken);
    }

    /**
     * @Route("auth-token/{id}", name="delete_auth_token", methods={"DELETE"})
     */
    public function deleteCurrentTokenAction(ApiTokenRepository $tokenRepository, $id)
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
