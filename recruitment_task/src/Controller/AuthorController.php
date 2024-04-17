<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuthorRepository       $authorRepository
    )
    {
    }

    #[Route('/authors/{id}', name: 'delete_author', methods: ['DELETE'])]
    public function deleteAuthor(int $id): Response
    {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            return new Response('Author not found', Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($author);
        $this->entityManager->flush();

        return new Response('Author deleted successfully', Response::HTTP_NO_CONTENT);
    }

    #[Route('/authors/search', name: 'search_authors', methods: ['GET'])]
    public function searchAuthors(Request $request): Response
    {
        $query = $request->query->get('query');

        if (!$query || strlen($query) < 3) {
            return new Response('Query must be at least 3 characters long', Response::HTTP_BAD_REQUEST);
        }

        $authors = $this->authorRepository
            ->createQueryBuilder('a')
            ->where('a.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        if (empty($authors)) {
            return new Response('No authors found', Response::HTTP_NOT_FOUND);
        }

        $formattedAuthors = [];
        foreach ($authors as $author) {
            $formattedAuthors[] = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'country' => $author->getCountry(),
            ];
        }

        return $this->json($formattedAuthors);
    }

    #[Route('/author/{id}/books', name: 'author_books', methods: ['GET'])]
    public function getAuthorBooks(int $id): JsonResponse
    {
        $authorRepository = $this->authorRepository->find($id);

        if (!$authorRepository) {
            return $this->json(['message' => 'Author not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $books = $authorRepository->getBooks();
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
}
