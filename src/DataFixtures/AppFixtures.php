<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Groupe;
use App\Entity\Unite;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {

        // $restos = ['SCL', 'DUG'];
        // foreach ($restos as $name) {
        //     $unite = new Unite();
        //     $unite->setName($name);
        //     $manager->persist($unite);
        // }

        $groups = [
            [
                'OG',
                'Officier'
            ],
            [
                'SOG',
                'Sous-Officier'
            ],
            [
                'GAV',
                'Volontaire'
            ],
            [
                'CIV',
                'Civil'
            ]
        ];
        foreach ($groups as $grp) {
            $groupe = new Groupe();
            $groupe->setShortName($grp[0]);
            $groupe->setName($grp[1]);
            $manager->persist($groupe);
        }

        $manager->flush();
    }

    // private static function getAllGroups(): int
    // {
    //     try {
    //         // Créer la connexion LDAP
    //         $ldap = Ldap::create('ext_ldap', [
    //             'connection_string' => 'ldap://' . $_ENV['LDAP_HOST'] . ':' . $_ENV['LDAP_PORT'],
    //         ]);

    //         // Bind avec l'utilisateur admin
    //         $ldap->bind('cn=' . $_ENV['LDAP_USER'] . ',ou=people,' . $_ENV['BASE_DN'], $_ENV['LDAP_PASSWORD']);

    //         // Construire le filtre (ajusté pour LLDAP)
    //         $filter = '(&(objectClass=inetOrgPerson)(memberOf=cn=' . $groupDn . ',ou=groups,' . $_ENV['BASE_DN'] . '))';

    //         // Exécuter la requête
    //         $query = $ldap->query($_ENV['BASE_DN'], $filter, [
    //             'filter' => ['dn'], // Ne récupérer que le DN pour compter
    //             'scope' => 'sub',
    //         ]);

    //         $results = $query->execute()->toArray();

    //         // Débogage : afficher les résultats bruts
    //         //dd(count($results)); // Décommente pour inspecter les entrées

    //         // Compter les résultats
    //         return count($results);
    //     } catch (ConnectionException $e) {
    //         // Erreur de connexion au serveur LDAP
    //         throw new \Exception('Erreur de connexion LDAP : ' . $e->getMessage());
    //     } catch (LdapException $e) {
    //         // Erreur lors de la requête ou du bind
    //         throw new \Exception('Erreur LDAP : ' . $e->getMessage());
    //     } catch (\Exception $e) {
    //         // Autres erreurs
    //         throw new \Exception('Erreur générale : ' . $e->getMessage());
    //     }
    // }
}
