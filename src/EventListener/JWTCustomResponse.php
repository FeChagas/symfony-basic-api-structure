<?php
// src/EventListener/JWTCustomResponse.php

namespace App\EventListener;

use App\Serializer\UserSerializer;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class JWTCustomResponse
{
    private $userRepository;
    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'status' => '401 Unauthorized',
            'message' => 'Verifique seu login e senha e tente novamente.',
        ];

        $response = new JWTAuthenticationFailureResponse($data);

        $event->setResponse($response);
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JWTAuthenticationFailureResponse('Sua sessão não é valida, por favor faça login novamente.', 403);

        $event->setResponse($response);
    }

    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $data = [
            'status' => '403 Forbidden',
            'message' => 'Sessão invalida, faça login.',
        ];

        $response = new JsonResponse($data, 403);

        $event->setResponse($response);
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $expiration = new \DateTime('+1 day');
        $expiration->setTime(2, 0, 0);

        $payload = $event->getData();
        $payload['exp'] = $expiration->getTimestamp();

        $event->setData($payload);
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof \App\Entity\User) {
            return;
        }

        $data['user'] = new \StdClass;

        $data['user']->id = $user->getId();
        $data['user']->name = $user->getName();
        $data['user']->username = $user->getUsername();
        $data['user']->roles = $user->getRoles();
        $data['user']->created_at = $user->getCreatedAt()->format('Y-m-d H:i:sP');
        $data['user']->updated_at = $user->getUpdatedAt()->format('Y-m-d H:i:sP');
        $event->setData($data);
    }
}
