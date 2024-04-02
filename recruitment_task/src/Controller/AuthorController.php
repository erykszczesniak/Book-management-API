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

class AuthorController extends AbstractController
{

    #[Route('/authors/search', name: 'search_authors', methods: ['GET'])]
    public function searchAuthors(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $searchTerm = $request->query->get('term');

        if (!$searchTerm || strlen($searchTerm) < 3) {
            return $this->json(['message' => 'Search term must be at least 3 characters long'], Response::HTTP_BAD_REQUEST);
        }

        $authorsRepository = $entityManager->getRepository(Author::class);
        $authors = $authorsRepository->createQueryBuilder('a')
            ->where('a.name LIKE :term')
            ->setParameter('term', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();

        $authorsArray = array_map(function ($author) {
            return [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'country' => $author->getCountry(),
            ];
        }, $authors);

        return $this->json($authorsArray);
    }

    #[Route('/author/{id}/books', name: 'author_books', methods: ['GET'])]
    public function getAuthorBooks(int $id, AuthorRepository $authorRepository): JsonResponse
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            return $this->json(['message' => 'Author not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $books = $author->getBooks();
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
