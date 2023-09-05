<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participiant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: "/conversations", name: "conversations.")]
class ConversationController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private ConversationRepository $conversationRepository
    ) {
    }

    #[Route('/', name: 'newConversation', methods: ["POST"])]
    public function index(Request $request): JsonResponse
    {
        $otherUser = $request->get(key: "otherUser");
        $otherUser = $this->userRepository->find($otherUser);

        if (is_null($otherUser)) {
            throw new \Exception("User not found");
        }

        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("You cannot send message to yourself");
        }

        // Check if the conversation exists already
        $conversation = $this->conversationRepository->findConversationByParticipants($this->getUser()->getId(), $otherUser->getId());
        if (count($conversation)) {
            throw new \Exception("Conversation exists already");
        }

        $conversation = new Conversation();

        $participant = new Participiant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participiant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($conversation);
            $this->em->persist($participant);
            $this->em->persist($otherParticipant);

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        return $this->json(["id" => $conversation->getId()], Response::HTTP_CREATED, [], []);
    }

    #[Route("/", name: "getConversations", methods: ["GET"])]
    public function getConversations(): JsonResponse
    {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        return new JsonResponse($conversations);
    }
}
