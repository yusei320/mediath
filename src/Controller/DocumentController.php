<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocumentController extends AbstractController
{
    #[Route('/documents', name: 'document_index')]
    public function index(DocumentRepository $documentRepository): Response
    {
        $documents = $documentRepository->findAll();

        return $this->render('document/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    #[Route('/document/nouveau', name: 'document_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Le document a été créé avec succès !');

            return $this->redirectToRoute('document_index');
        }

        return $this->render('document/new.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/document/{id}', name: 'document_show', requirements: ['id' => '\d+'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/document/{id}/modifier', name: 'document_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le document a été modifié avec succès !');

            return $this->redirectToRoute('document_index');
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/document/{id}/supprimer', name: 'document_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();

            $this->addFlash('success', 'Le document a été supprimé avec succès !');
        }

        return $this->redirectToRoute('document_index');
    }
}