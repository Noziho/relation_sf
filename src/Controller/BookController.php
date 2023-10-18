<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reader;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\EditorRepository;
use App\Repository\ReaderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'app_books', methods: ['GET'])]
    public function getAll(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $books = $bookRepository->findAll();

        if ($books){
            $jsonBooks = $serializer->serialize($books, 'json', [
                'groups' => 'getBooks'
            ]);
            return new JsonResponse($jsonBooks, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Books not found"], Response::HTTP_NOT_FOUND, [], true);
    }

    #[Route('/api/book/{id}', name: 'app_books_get_id', methods: ['GET'])]
    public function getById(int $id, BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
        $book = $bookRepository->find($id);

        if ($book){
            $jsonBook = $serializer->serialize($book, 'json', [
                'groups' => 'getBooks'
            ]);
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Book not found"], Response::HTTP_NOT_FOUND, [], true);
    }

    #[Route('/api/book/delete/{id}', name: 'app_book_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->find($id);

        if ($book) {
            $em->remove($book);
            $em->flush();

            return new JsonResponse(["message" => "Book successfully deleted"], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(["message" => "Book not found"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/book/create', name: 'app_book_create', methods: ['POST'])]
    public function create
    (Request $request,
     EntityManagerInterface $em,
     SerializerInterface $serializer,
     AuthorRepository $authorRepository,
    EditorRepository $editorRepository
    ): JsonResponse
    {
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $content = $request->toArray();
        $author = $authorRepository->find($content['author_id']);
        $editor = $editorRepository->find($content['editor_id']);
        $book->setAuthor($author);
        $book->setEditor($editor);
        $em->persist($book);
        $em->flush();
        return new JsonResponse('It works');
    }

    #[Route('/api/book/{id}', name: "updateBook", methods: ['PUT'])]
    public function updateBook
    (
        Request $request,
        SerializerInterface $serializer,
        Book $currentBook,
        EntityManagerInterface $em,
    ): JsonResponse
    {
        // Ici on deserialize notre objet qui est au format JSON
        // On récupère les data de la request
        // Avec le normalizer on indique de prendre l'instance passer en param et de la modifier via OBJET_TO_POPULATE
        // et on lui passe l'instance actuel qui est $currentAuthor (param URL)
        // OBJECT_TO_POPULATE permet de ne pas réinstancier un new author d'ou la modification
        $updatedBook = $serializer->deserialize($request->getContent(),
            Book::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]
        );

        $em->persist($updatedBook);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/book/searchYear/{year}', name: 'app_book_search_year', methods: ['GET'])]
    public function getBookByYear
    (
        int $year,
        BookRepository $bookRepository,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $books = $bookRepository->findAllGreaterThanYear($year);

        $jsonBooks = $serializer->serialize($books, 'json', ['groups' => 'getBooks']);

        return new JsonResponse($jsonBooks, Response::HTTP_OK, [], true);
    }

    #[Route('/api/book/searchLowerYear/{year}', name: 'app_book_search_lower_year', methods: ['GET'])]
    public function getBookByLowerYear
    (
        int $year,
        BookRepository $bookRepository,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $books = $bookRepository->findAllLowerThanYear($year);

        $jsonBooks = $serializer->serialize($books, 'json', ['groups' => 'getBooks']);

        return new JsonResponse($jsonBooks, Response::HTTP_OK, [], true);
    }

    #[Route('/api/bookR/{id}/{idBook}', name: 'app_book_search_lower_y', methods: ['POST'])]
    public function addReader
    (
        Reader $reader,
        int $idBook,
        ReaderRepository $readerRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $reader = $readerRepository->find($reader->getId());
        $book = $bookRepository->find($idBook);

        if ($reader && $book) {
            $book->addReader($reader);

            $em->persist($book);

            $em->flush();

            return new JsonResponse(["message" => "Reader added"], Response::HTTP_OK);
        }
        return new JsonResponse(["message" => "ReaderNotFound"], Response::HTTP_NOT_FOUND);


    }
}
