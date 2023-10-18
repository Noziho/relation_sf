<?php

namespace App\Controller;

use App\Entity\Reader;
use App\Repository\ReaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ReaderController extends AbstractController
{
    #[Route('/api/reader/', name: 'app_reader', methods: ['GET'])]
    public function getAll(ReaderRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $readerList = $repository->findAll();
        $jsonReader = $serializer->serialize($readerList, 'json', ['groups'=>"getReaders"]);

        return new JsonResponse($jsonReader, Response::HTTP_OK, [], true);
    }

    #[Route('/api/reader', name: 'app_reader_create', methods: ['POST'])]
    public function create
    (
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Reader::class, 'json');

        $em->persist($book);
        $em->flush();
        return new JsonResponse('It works');
    }

    #[Route('/api/reader/{id}', name: 'app_reader_delete', methods: ['DELETE'])]
    public function reader(int $id, EntityManagerInterface $em, ReaderRepository $readerRepository): JsonResponse
    {
        $reader = $readerRepository->find($id);

        if ($reader) {
            $em->remove($reader);
            $em->flush();

            return new JsonResponse(["message" => "Reader successfully deleted"], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(["message" => "Reader not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/reader/{id}', name: 'app_books_get_id', methods: ['GET'])]
    public function getById(int $id, ReaderRepository $readerRepository, SerializerInterface $serializer): JsonResponse
    {
        $reader = $readerRepository->find($id);

        if ($reader){
            $jsonReader = $serializer->serialize($reader, 'json', [
                'groups' => 'getReaders'
            ]);
            return new JsonResponse($jsonReader, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Book not found"], Response::HTTP_NOT_FOUND, [], true);
    }

    #[Route('/api/reader/{id}', name: "updateBook", methods: ['PUT'])]
    public function updateBook
    (
        Request $request,
        SerializerInterface $serializer,
        Reader $currentReader,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        $updatedReader = $serializer->deserialize($request->getContent(),
            Reader::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentReader]
        );

        $em->persist($updatedReader);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
