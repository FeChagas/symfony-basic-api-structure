<?php
//src/Serializer/UserSerializer.php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * 
 */
class UserSerializer
{
	public function serialize($users)
    {
        $encoder = new JsonEncoder();
        
        $maxDepthHandler = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject->id;
        };
        $dateCallback = function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
            return $innerObject instanceof \DateTime ? $innerObject->format('Y-m-d H:i:sP') : '';
        };

        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::CALLBACKS => [
                'createdAt' => $dateCallback,
                'updatedAt' => $dateCallback,
            ],
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER => $maxDepthHandler,
        ];
        
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter(), null, null, null, null, $defaultContext);

        $serializer = new Serializer([$normalizer], [$encoder]);
        
        return $serializer->serialize($users, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password', 'salt', 'roles']]);
    }
}