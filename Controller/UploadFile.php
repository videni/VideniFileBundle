<?php

namespace App\Bundle\FileBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Bundle\FileBundle\Entity\File;
use App\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class UploadFile
{
    private $entityManager;
    private $tokenAccessor;
    private $validator;

    public function __construct(
        ObjectManager $entityManager,
        TokenAccessorInterface  $tokenAccessor,
        ValidatorInterface  $validator
    ) {
        $this->entityManager = $entityManager;
        $this->tokenAccessor = $tokenAccessor;
        $this->validator = $validator;
    }

    /**
     * @Route(
     *     name="api_upload_file",
     *     path="/file/upload",
     *     methods={"POST"},
     *     defaults={
     *         "_api_receive"=false,
     *         "_api_resource_class"=File::class,
     *         "_api_item_operation_name"="upload_file",
     *     }
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

        $violations = $this->validator->validate($request->files->all(), $constraints);
        if (0 != count($violations)) {
            return $violations;
        }

        $uploadedFile = $request->files->get('file');

        $file = new File();
        $file->setFile($uploadedFile)
            ->setMineType($uploadedFile->getMimeType())
        ;
        if ($this->tokenAccessor->hasUser()) {
            $file->setOwner($this->tokenAccessor->getUser());
        }

        $this->entityManager->persist($file);
        $this->entityManager->flush();

        return $file;
    }
}
