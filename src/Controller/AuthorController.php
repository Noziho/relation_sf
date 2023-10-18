<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AuthorController extends AbstractController
{
    #[Route('/api/author', name: 'app_author', methods: ['GET'])]
    public function getAll(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorList = $authorRepository->findAll();
        $jsonAuthorList = $serializer->serialize($authorList, 'json', ["groups" => "getBookFromAuthor"]);
        return new JsonResponse($jsonAuthorList, Response::HTTP_OK, ['Access-Control-Allow-Origin' => 'http://localhost:8080'], true);
    }

    #[Route('/api/author/{id}', name: 'app_author_get_id', methods: ['GET'])]
    public function getById(int $id, AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $author = $authorRepository->find($id);
        if ($author) {
            $jsonAuthor = $serializer->serialize($author, 'json', ["groups" => "getBookFromAuthor"]);
            return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Author not found"], Response::HTTP_NOT_FOUND, [], true);

    }

    #[Route('/api/author/create', name: 'app_author_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');

        $em->persist($author);
        $em->flush();

        $jsonEditor = $serializer->serialize($author, 'json');

        return new JsonResponse($jsonEditor, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/author/delete/{id}', name: 'app_author_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        $author = $authorRepository->find($id);

        if ($author) {
            $em->remove($author);
            $em->flush();

            return new JsonResponse(["message" => "Author successfully deleted"], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(["message" => "Author already deleted"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/author/{id}', name: "updateAuthor", methods: ['PUT'])]
    public function updateAuthor
    (
     Request $request,
     SerializerInterface $serializer,
     Author $currentAuthor,
     EntityManagerInterface $em,
    ): JsonResponse
    {
        // Ici on deserialize notre objet qui est au format JSON
        // On récupère les data de la request
        // Avec le normalizer on indique de prendre l'instance passer en param et de la modifier via OBJET_TO_POPULATE
        // et on lui passe l'instance actuel qui est $currentAuthor (param URL)
        // OBJECT_TO_POPULATE permet de ne pas réinstancier un new author d'ou la modification
        $updatedAuthor = $serializer->deserialize($request->getContent(),
            Author::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]
        );

        $em->persist($updatedAuthor);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}