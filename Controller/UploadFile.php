<?php

namespace Videni\Bundle\FileBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Videni\Bundle\FileBundle\Entity\File;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadFile
{
    private $entityManager;
    private $validator;
    private $serializer;

    public function __construct(
        ObjectManager $entityManager,
        ValidatorInterface  $validator,
        SerializerInterface  $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *     name="videni_upload_file",
     *     path="/file/upload",
     *     methods={"POST"},
     * )
     */
    public function __invoke(Request $request)
    {
        $constraints = new Assert\Collection([
            'file' => [
                 new Assert\File([
                    'maxSize' => '50M'
                 ]),
                new Assert\NotBlank(),
            ]
        ]);

        $response = new JsonResponse();

        $violations = $this->validator->validate($request->files->all(), $constraints);
        if (0 != count($violations)) {
            return $response
                ->setJson($this->serializer->serialize($violations, 'json'));
        }

        $uploadedFile = $request->files->get('file');

        $file = new File();
        $file->setFile($uploadedFile)
            ->setMineType($uploadedFile->getMimeType())
            ->setOriginalName($uploadedFile->getClientOriginalName())
        ;

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        $context = new  SerializationContext();

        return $response
            ->setJson($this->serializer->serialize($file, 'json', $context->setGroups(['Default'])));
    }
}
