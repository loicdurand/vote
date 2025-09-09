<?php

namespace App\Service;

use Exception;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;

class LdapService
{
    private $ldapHost = 'lldap';
    private $ldapPort = 3890;
    private $ldapUser = "admin";
    private $ldapPassword = "my_password";
    private $baseDn = 'dc=gendarmerie,dc=defense,dc=gouv,dc=fr';

    public function countGroupMembers(string $groupDn): int
    {
        try {
            // Créer la connexion LDAP
            $ldap = Ldap::create('ext_ldap', [
                'connection_string' => 'ldap://' . $this->ldapHost . ':' . $this->ldapPort,
            ]);

            // Bind avec l'utilisateur admin
            $ldap->bind('cn=' . $this->ldapUser . ',ou=people,' . $this->baseDn, $this->ldapPassword);

            // Construire le filtre (ajusté pour LLDAP)
            $filter = '(&(objectClass=inetOrgPerson)(memberOf=cn=' . $groupDn . ',ou=groups,' . $this->baseDn . '))';

            // Exécuter la requête
            $query = $ldap->query($this->baseDn, $filter, [
                'filter' => ['dn'], // Ne récupérer que le DN pour compter
                'scope' => 'sub',
            ]);

            $results = $query->execute()->toArray();

            // Débogage : afficher les résultats bruts
            //dd(count($results)); // Décommente pour inspecter les entrées

            // Compter les résultats
            return count($results);
        } catch (ConnectionException $e) {
            // Erreur de connexion au serveur LDAP
            throw new Exception('Erreur de connexion LDAP : ' . $e->getMessage());
        } catch (LdapException $e) {
            // Erreur lors de la requête ou du bind
            throw new Exception('Erreur LDAP : ' . $e->getMessage());
        } catch (Exception $e) {
            // Autres erreurs
            throw new Exception('Erreur générale : ' . $e->getMessage());
        }
    }
}
