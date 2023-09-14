<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\ParticipiantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route("/messages", name: "messages.")]
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALIZE = ["id", "content", "createdAt", "mine"];

    public function __construct(
        private MessageRepository $messageRepository,
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private ParticipiantRepository $participiantRepository,
        private HubInterface $hubInterface
    ) {
    }

    #[Route('/{id}', name: 'getMessages', methods: ["GET"])]
    public function index(Conversation $conversation): Response
    {
        // To deny who can view the conversation
        $this->denyAccessUnlessGranted("view", $conversation);

        $messages = $this->messageRepository->findMessageByConversationId($conversation->getId());

        array_map(
            function (Message $message) {
                $isMine = ($message->getUser()->getId() === $this->getUser()->getId()) ? true : false;
                $message->setMine($isMine);
            },
            $messages
        );

        return $this->json($messages, 200, [], ['attributes' => self::ATTRIBUTES_TO_SERIALIZE]);
    }

    #[Route("/{id}", name: 'newMessage', methods: ["POST"])]
    public function newMessage(Request $request, Conversation $conversation, SerializerInterface $serializerInterface): Response
    {
        $user = $this->getUser();
        $content = $request->get("content");

        $recipient = $this->participiantRepository->findParticipantByConversationIdAndUserId($conversation->getId(), $user->getId());

        $message = new Message();
        $message->setUser($user);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTime());

        $conversation->addMessage($message);
        $conversation->setLastMessage($message);

        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->persist($message);
            $this->em->persist($conversation);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }

        $message->setMine(false);

        $messageSerialized = $serializerInterface->serialize($message, "json", [
            "attributes" => ["id", "content", "createdAt", "mine", "conversation" => ["id"]]
        ]);

        $update = new Update(
            [
                sprintf("/conversations/%s", $conversation->getId()),
                sprintf("/conversations/%s", $recipient->getUser()->getName())
            ],
            $messageSerialized
        );

        $this->hubInterface->publish($update);

        $message->setMine(true);

        return $this->json($message, 200, [], ["attributes" => self::ATTRIBUTES_TO_SERIALIZE]);
    }
}
