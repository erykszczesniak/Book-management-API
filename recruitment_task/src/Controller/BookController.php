<?php

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{


    #[Route('/books', name: 'list_books', methods: ['GET'])]
    public function listBooks(EntityManagerInterface $entityManager): JsonResponse
    {
        $booksRepository = $entityManager->getRepository(Book::class);
        $books = $booksRepository->findAll();

        $booksArray = [];
        foreach ($books as $book) {
            $booksArray[] = [
                'id' => $book->getId(),
                'name' => $book->getName(),
                'publisher' => $book->getPublisher(),
                'pageCount' => $book->getPageCount(),
                'isPublic' => $book->isPublic(),
            ];
        }

        return $this->json($booksArray);
    }


    #[Route('/book', name: 'create_book', methods: ['POST'])]
    public function createBook(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = new Book();
        $book->setName($data['name']);
        $book->setPublisher($data['publisher']);
        $book->setPageCount($data['pageCount']);
        $book->setPublic($data['isPublic'] ?? false);

        $entityManager->persist($book);
        $entityManager->flush();

        return new JsonResponse(
            data: [
                'status' => 'Book created!',
                'bookId' => $book->getId(),
            ],
            status: JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/book/{id}', name: 'delete_book', methods: ['DELETE'])]
    public function deleteBook(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return $this->json(['message' => 'Book not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->json(['message' => 'Book deleted successfully']);
    }


    #[Route('/book/{id}', name: 'update_book', methods: ['PUT'])]
    public function updateBook(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return $this->json(['message' => 'Book not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $book->setName($data['name'] ?? $book->getName());
        $book->setPublisher($data['publisher'] ?? $book->getPublisher());
        $book->setPageCount($data['pageCount'] ?? $book->getPageCount());
        $book->setPublic($data['isPublic'] ?? $book->isPublic());

        $entityManager->flush();

        return $this->json(['message' => 'Book updated successfully']);
    }


    #[Route('/book/{id}/author', name: 'add_author_to_book', methods: ['POST'])]
    public function addAuthorToBook(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return $this->json(['message' => 'Book not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (count($book->getAuthors()) >= 3) {
            return $this->json(['message' => 'A book cannot have more than 3 authors'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $author = new Author();
        $author->setName($data['name']);
        $author->setCountry($data['country']);

        $book->addAuthor($author);

        $entityManager->persist($author);
        $entityManager->flush();

        return $this->json(['message' => 'Author added to book']);
    }


    #[Route('/book/{bookId}/author/{authorId}', name: 'remove_author_from_book', methods: ['DELETE'])]
    public function removeAuthorFromBook(int $bookId, int $authorId, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($bookId);
        $author = $entityManager->getRepository(Author::class)->find($authorId);

        if (!$book || !$author) {
            return $this->json(['message' => 'Book or author not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Assuming Book has a removeAuthor method
        if (!$book->getAuthors()->contains($author)) {
            return $this->json(['message' => 'Author is not assigned to this book'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $book->removeAuthor($author);
        $entityManager->flush();

        return $this->json(['message' => 'Author removed from book']);
    }


}
