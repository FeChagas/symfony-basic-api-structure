<?php

namespace App\Controller\V1\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Serializer\UserSerializer;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admins", name="admins_")
 * 
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    private $request;
    private $em;
    private $userDao;
    private $userSerializer;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userDao, UserSerializer $userSerializer)
    {
        $this->request = Request::createFromGlobals(); 
        $this->em = $entityManager;
        $this->userDao = $userDao;
        $this->userSerializer = $userSerializer;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        $response = [ 'message' => null, 'records' => [], 'errors' => [] ];
        $status = 200;

        $users = $this->userDao->findAll();

        foreach ($users as $key => $user) 
        {
            if (false === array_search('ROLE_ADMIN', $user->getRoles())) {
                unset($users[$key]);
            }
        }

        $response['records'] = $users;
        $response = $this->userSerializer->serialize($response);

        return JsonResponse::fromJsonString($response, $status);
    }

    /**
     * @Route("/", name="new", methods={"POST"})
     */
    public function new(UserPasswordEncoderInterface $encoder)
    {
        $response = [ 'message' => null, 'records' => [], 'errors' => [] ];
        $status = 201;

        $payload = json_decode(
            $this->request->getContent(),
            true
        );

        if (!isset($payload['name']) || !isset($payload['username']) || !isset($payload['password']) || !isset($payload['confirmPassword'])) 
        {
            $response['message'] = 'Alguns campos obrigatórios não foram preenchidos.';
            $status = 400;
            (!isset($payload['name'])) ? $response['errors'][] = 'parameter name is required' : null;
            (!isset($payload['username'])) ? $response['errors'][] = 'parameter username is required' : null;
            (!isset($payload['password'])) ? $response['errors'][] = 'parameter password is required' : null;
            (!isset($payload['confirmPassword'])) ? $response['errors'][] = 'parameter confirmPassword is required' : null;
            
        }
        elseif ($payload['password'] !== $payload['confirmPassword']) 
        {
            $response['message'] = 'As senhas são diferentes.';
            $status = 400;
            $response['errors'][] = 'the password and confirmPassword parameters must match';
        }
        else
        {
            $user = $this->userDao->findByUsername($payload['username']);
            if ($user) 
            {
                $response['message'] = 'Esse e-mail já está cadastrado.';
                $status = 400;
                $response['errors'][] = 'an user with e-mail ' . $payload['username'] . ' already exists';
            }
            else
            {
                $user = new User();

                $user->setName($payload['name']);
                $user->setUsername($payload['username']);
                $user->setRoles(['ROLE_ADMIN']);
                $user->setPassword($encoder->encodePassword($user, $payload['password']));

                $this->em->persist($user);
                $this->em->flush();

                $response['records'] = $user;
                $response['message'] = 'Administrador cadastrado com sucesso.';
            }
        }

        $response = $this->userSerializer->serialize($response);

        return JsonResponse::fromJsonString($response, $status);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show($id)
    {
        $response = [ 'message' => null, 'records' => [], 'errors' => [] ];
        $status = 200;

        $user = $this->userDao->find($id);

        if (!$user) 
        {
            $response['message'] = 'Administrador não encontrato.';
        }
        else
        {
            $response['records'] = $user;
        }


        $response = $this->userSerializer->serialize($response);
        return JsonResponse::fromJsonString($response, $status);       
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete($id)
    {
        $response = [ 'message' => null, 'records' => [], 'errors' => [] ];
        $status = 200;

        $user = $this->userDao->find($id);

        if (!$user) 
        {
            $response['message'] = 'Administrador não encontrado.';
        }
        else
        {
            $users = $this->userDao->findAll();
            if (count($users) <= 1) 
            {
                $response['message'] = 'Não é possivel apagar o ultimo Administrador do sistema.';
                $response['errors'][] = 'must exist at least one active admin';
            }
            else
            {
                $this->em->remove($user);
                $this->em->flush();

                $response['message'] = 'Administrador deletado com sucesso.';                
            }
        }


        $response = $this->userSerializer->serialize($response);
        return JsonResponse::fromJsonString($response, $status);
    }
}
