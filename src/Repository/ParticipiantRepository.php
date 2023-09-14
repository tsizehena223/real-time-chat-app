<?php

namespace App\Repository;

use App\Entity\Participiant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participiant>
 *
 * @method Participiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participiant[]    findAll()
 * @method Participiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participiant::class);
    }

    public function findParticipantByConversationIdAndUserId(?int $conversationId, ?int $userId)
    {
        $qb = $this->createQueryBuilder("p");
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq("p.conversation", ":conversationId"),
                $qb->expr()->neq("p.user", ":userId")
            )
        )
            ->setParameters([
                "conversationId" => $conversationId,
                "userId" => $userId
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
