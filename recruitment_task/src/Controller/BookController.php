<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookRepository         $bookRepository
    )
    {
    }


    #[Route('/books', name: 'list_books', methods: ['GET'])]
    public function listBooks(EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $this->bookRepository->findAll();

        $booksArray = [];
        foreach ($books as $book) {
            $authorDetails = [];
            foreach ($book->getAuthors() as $author) {
                $authorDetails[] = [
                    'id' => $author->getId(),
                    'name' => $author->getName(),
                    'country' => $author->getCountry(),
                ];
            }

            $booksArray[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'publisher' => $book->getPublisher(),
                'pageCount' => $book->getPageCount(),
                'isPublic' => $book->isPublic(),
                'authors' => $authorDetails,
            ];
        }

        return $this->json($booksArray);
    }

    #[Route('/book', name: 'create_book', methods: ['POST'])]
    public function createBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $book = new Book();
        $book->setName($data['name']);
        $book->setPublisher($data['publisher']);
        $book->setPageCount($data['pageCount']);
        $book->setIsPublic($data['isPublic'] ?? false);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'Book created!',
            'bookId' => $book->getId(),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/book/{id}', name: 'delete_book', methods: ['DELETE'])]
    public function deleteBook(Book $book = null): JsonResponse
    {
        if (!$book) {
            return $this->json(['message' => 'Book not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return $this->json(['message' => 'Book deleted successfully']);
    }

    #[Route('/book/{id}', name: 'update_book', methods: ['PUT'])]
    public function updateBook(Book $book = null, Request $request): JsonResponse
    {
        if (!$book) {
            return $this->json(['message' => 'Book not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $book->setName($data['name'] ?? $book->getName());
        $book->setPublisher($data['publisher'] ?? $book->getPublisher());
        $book->setPageCount($data['pageCount'] ?? $book->getPageCount());
        $book->setIsPublic($data['isPublic'] ?? $book->isPublic());

        $this->entityManager->flush();

        return $this->json(['message' => 'Book updated successfully']);
    }

    #[Route('/book/{id}/author', name: 'add_author_to_book', methods: ['POST'])]
    public function addAuthorToBook(Book $book = null, Request $request): JsonResponse
    {
        if (!$book || count($book->getAuthors()) >= 3) {
            $msg = !$book ? 'Book not found' : 'A book cannot have more than 3 authors';
            return $this->json(['message' => $msg], !$book ? JsonResponse::HTTP_NOT_FOUND : JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $author = new Author();
        $author->setName($data['name']);
        $author->setCountry($data['country']);

        $book->addAuthor($author);
        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $this->json(['message' => 'Author added to book']);
    }

    #[Route('/book/{bookId}/author/{authorId}', name: 'remove_author_from_book', methods: ['DELETE'])]
    public function removeAuthorFromBook(Book $book = null, Author $author = null): JsonResponse
    {
        if (!$book || !$author) {
            return $this->json(['message' => 'Book or author not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$book->getAuthors()->contains($author)) {
            return $this->json(['message' => 'Author is not assigned to this book'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $book->removeAuthor($author);
        $this->entityManager->flush();

        return $this->json(['message' => 'Author removed from book']);
    }


}
