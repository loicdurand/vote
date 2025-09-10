<?php

namespace App\Repository;

use App\Entity\Registre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Election;

/**
 * @extends ServiceEntityRepository<Registre>
 */
class RegistreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registre::class);
    }

    public function getDailyParticipation(Election $election)
    {
        // Nombre de jours entre ouverture et fermeture du vote
        $start = $election->getStartDate();
        $end = $election->getEndDate();
        $days = ($start)->diff($end)->days + 1;

        // Participation par heure
        $daylyParticipation = [];
        for ($i = 0; $i < $days; $i++) {
            $date = (clone $start)->modify("+$i days");
            $daylyParticipation[] = [
                "date" => $date->format('Y-m-d'),
                "votes" => 0
            ];
        }

        $votesByDay = $this->createQueryBuilder('r')
            ->select('DATE(r.votedAt) AS day, COUNT(r.id) AS count')
            ->where('r.election = :election')
            ->setParameter('election', $election)
            ->groupBy('day')
            ->getQuery()
            ->getResult();

        foreach ($votesByDay as $data) {
            $key = array_search($data['day'], array_column($daylyParticipation, 'date'));
            $daylyParticipation[$key] = [
                'date' => $data['day'],
                "votes" => (int)$data['count']
            ];
        }

        return $daylyParticipation;
    }

    //    /**
    //     * @return Registre[] Returns an array of Registre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Registre
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
