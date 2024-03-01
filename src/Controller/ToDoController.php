<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Note;
use App\Entity\User;
use App\Form\NotesType;
use App\Form\EditNoteType;
use App\Form\DoneNoteType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class ToDoController extends AbstractController
{
    private $em;
    private $security;
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
    #[Route('/', name: 'app_to_do')]
    public function index(Request $req): Response
    {
        $note = new Note();
        $currentUser = $this->security->getUser();
        if ($currentUser) {
            $note->setUser($currentUser);
            $user = $note->getUser();
            $notes = $this->em->getRepository(Note::class)->findBy(["user" => $user]);
        } else {
            $notes = $this->em->getRepository(Note::class)->findBy(['user' => null]);
        }
        $form = $this->createForm(NotesType::class, $note);
        $editform = $this->createForm(EditNoteType::class, $note);
        $doneform = $this->createForm(DoneNoteType::class, $note);
        
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $note->setDone(false);
            $this->em->persist($note);
            $this->em->flush();
            $this->addFlash('message', 'Inserted Successfully');
            return $this->redirectToRoute('app_to_do');
        }
        return $this->render('index.html.twig', [
            "doneform" => $doneform->createView(),
            "editform" => $editform->createView(),
            'form' => $form->createView(),
            'formData' => $notes,
        ]);
    }
    #[Route('/update-note/{id}', name: 'update_note_api', methods: ['PUT'])]
    public function updateNoteApi(Request $request, $id): JsonResponse
    {
        $currentUser = $this->security->getUser();

        if ($currentUser) {
            $note = $this->em->getRepository(Note::class)->findOneBy(['user' => $currentUser, 'id' => $id]);
        } else {
            $note = $this->em->getRepository(Note::class)->find($id);
        }
        $data = json_decode($request->getContent(), true);

        $done = $data['done'];
        $id = $data['id'];

        $note->setDone(true);
        $this->em->flush();
        return new JsonResponse(["message" => "Updated note"]);
    }
    #[Route('/update-page', name: "update-page")]
    public function donePageApi(): JsonResponse
    {
        $currentUser = $this->security->getUser();
        if ($currentUser) {
            $notes = $this->em->getRepository(Note::class)->findBy(['done' => true, "user" => $currentUser]);
        } else {
            $notes = $this->em->getRepository(Note::class)->findBy(['done' => true, "user" => null]);
        }
        $responseData = [];
        foreach ($notes as $note) {
            $responseData[] = [
                'title' => $note->getTitle(),
                'description' => $note->getDescription(),
            ];
        }
        return new JsonResponse(['notes' => $responseData]);
    }
    #[Route("/done-note", name: "done-note", methods: ['POST'])]
    public function doneNoteApi(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $currentUser = $this->getUser();
        $note = new Note();
        $note->setDone(true);
        $note->setTitle($data['title']);
        $note->setDescription($data['desc']);
    
        if ($currentUser) {
            $note->setUser($currentUser);
        }
    
        $this->em->persist($note);
        $this->em->flush();
    
        return new JsonResponse(["message" => "Submitted Note successfully"]);
    }
    #[Route("/get-note-for-modal/{id}", name: "get_note_for_modal", methods: ['GET'])]
    public function getNoteApi($id): JsonResponse
    {
        $currentUser = $this->security->getUser();

        if ($currentUser) {
            $notes = $this->em->getRepository(Note::class)->findBy(["id" => $id, 'done' => false, "user" => $currentUser]);
        } else {
            $notes = $this->em->getRepository(Note::class)->findBy(["id" => $id, 'done' => false, "user" => null]);
        }

        $responseData = [];
        foreach ($notes as $note) {
            $responseData[] = [
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'description' => $note->getDescription(),
            ];
        }

        return new JsonResponse(["notes" => $responseData]);
    }

    #[Route('/edit-note/{id}', name: 'app_edit_note', methods: ['PUT'])]
    public function editPost($id, Request $request, SerializerInterface $serializer): JsonResponse
    {

        $currentUser = $this->getUser();
        $note = $this->em->getRepository(Note::class)->findOneBy(["user" => $currentUser, "done" => false]);
        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) {
            $note->setTitle($data['title']);
        }

        if (isset($data['desc'])) {
            $note->setDescription($data['desc']);
        }

        $this->em->flush();

        return $this->json(['message' => 'Note updated successfully']);
    }
    #[Route("/delete-note/{id}", name: "delete_note")]
    function deleteNote($id)
    {
        $currentUser = $this->security->getUser();

        if ($currentUser) {
            $note = $this->em->getRepository(Note::class)->findOneBy(["id" => $id, 'done' => false, "user" => $currentUser]);
        } else {
            $note = $this->em->getRepository(Note::class)->findOneBy(["id" => $id, 'done' => false, "user" => null]);
        }
        $this->em->remove($note);
        $this->em->flush();
        return new JsonResponse(["message" => "The note has been deleted successfully"]);
    }
}
