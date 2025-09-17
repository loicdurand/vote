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
                'Off.',
                'Officier',
                'OG'
                
            ],
            [
                'S-Off.',
                'Sous-Officier',
                'SOG'
            ],
            [
                'Volont.',
                'Volontaire',
                'GAV'
            ],
            [
                'Civil',
                'Civil',
                'CIV'
            ]
        ];
        foreach ($groups as $grp) {
            $groupe = new Groupe();
            $groupe->setShortName($grp[0]);
            $groupe->setName($grp[1]);
            $groupe->setNickname($grp[2]);
            $manager->persist($groupe);
        }

        $manager->flush();

        $ldap_unites = $this->getAllGroups(); //dd($ldap_unites);

        foreach ($ldap_unites as $unite) {
            $unt = new Unite();
            $unt->setCodeunite($unite->getAttribute('codeUnite')[0]);
            $unt->setName($unite->getAttribute('businessOu')[0]);
            $unt->setDepartement(971);
            $manager->persist($unt);
        }

        $manager->flush();
    }

    private static function getAllGroups(): array
    {
        try {
            // Créer la connexion LDAP
            $ldap = Ldap::create('ext_ldap', [
                'connection_string' => 'ldap://' . $_ENV['LDAP_HOST'] . ':' . $_ENV['LDAP_PORT'],
            ]);

            // Bind avec l'utilisateur admin
            if ($_ENV['APP_ENV'] === 'dev')
                $ldap->bind('cn=' . $_ENV['LDAP_USER'] . ',ou=people,' . $_ENV['BASE_DN'], $_ENV['LDAP_PASSWORD']);
            else
                $ldap->bind(null, null);

            // Construire le filtre (ajusté pour LLDAP)
            $filter = "(&(objectclass=organizationalUnit)(memberOf=cn=g_tu-fo_12609,dmdName=Groupes,dc=gendarmerie,dc=defense,dc=gouv,dc=fr))"; //departmentNumber=GEND/COMGENDGP))";

            // Exécution de la requête
            $query = $ldap->query($_ENV['BASE_DN'], $filter, [
                'filter' => ['codeUnite', 'businessOu'], // Ne récupérer que le DN pour compter
                //'scope' => 'sub',
            ]);

            $results = $query->execute()->toArray();


            return $results;
        } catch (ConnectionException $e) {
            //         // Erreur de connexion au serveur LDAP
            throw new \Exception('Erreur de connexion LDAP : ' . $e->getMessage());
        } catch (LdapException $e) {
            //         // Erreur lors de la requête ou du bind
            throw new \Exception('Erreur LDAP : ' . $e->getMessage());
        } catch (\Exception $e) {
            //         // Autres erreurs
            throw new \Exception('Erreur générale : ' . $e->getMessage());
        }
    }
}
