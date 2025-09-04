<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Routing\Annotation\Route;

class UserSearchController extends AbstractController
{
    private $ldapHost = 'lldap';
    private $ldapPort = 3890;
    private $baseDn = 'dc=gendarmerie,dc=defense,dc=gouv,dc=fr';
    /**
     * Route pour la recherche complète (par nom ou par NI).
     */
    #[Route("/search/users", name: "search_users", methods: ["GET", "POST"])]
    public function searchUsers(Request $request): Response
    {
        $lastname = $request->query->get('lastname') ?? $request->request->get('lastname');
        $nigend = $request->query->get('nigend') ?? $request->request->get('ni');

        if (empty($lastname) && empty($nigend)) {
            return $this->render('search/search.html.twig', ['results' => [], 'error' => 'Veuillez saisir un nom ou un NIGEND.']);
        }

        $results = $this->performLdapSearch($lastname, $nigend);

        if (isset($results['error'])) {
            return $this->render('search/search.html.twig', ['results' => [], 'error' => $results['error']]);
        }

        return $this->render('search/results.html.twig', ['results' => $results]);
    }

    /**
     * Route pour l'autocomplete (recherche partielle par nom, renvoie JSON pour JS).
     */
    #[Route("/autocomplete/lastname", name: "autocomplete_lastname", methods: ["GET"])]
    public function autocompleteLastname(Request $request): JsonResponse
    {
        $term = $request->query->get('term');

        if (empty($term) || strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $results = $this->performLdapSearch($term);

        if (isset($results['error'])) {
            return new JsonResponse(['error' => $results['error']]);
        }

        $suggestions = [];
        foreach ($results as $entry) {
            $suggestions[] = [
                'label' => $entry['sn'][0] . ' (' . ($entry['ni'][0] ?? 'N/A') . ')',
                'value' => $entry['sn'][0],
                'ni' => $entry['ni'][0] ?? null,
            ];
        }

        return new JsonResponse($suggestions);
    }

    /**
     * Effectue une recherche LDAP avec le composant Symfony LDAP.
     * @param string|null $lastname Recherche par sn (nom de famille), supporte wildcard *.
     * @param string|null $nigend Recherche exacte par ni.
     * @return array Résultats ou ['error' => message]
     */
    private function performLdapSearch(?string $lastname = null, ?string $nigend = null): array
    {
        try {
            // Crée une instance LDAP (sans config yaml pour simplicité)
            $ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldap://lldap:3890']);
            $ldap->bind('uid=admin,ou=people,dc=gendarmerie,dc=defense,dc=gouv,dc=fr', 'my_password');

            $filter = '(objectClass=person)';
            if ($lastname) {
                $filter = sprintf('(&(objectClass=person)(displayname=*%s*))', $ldap->escape($lastname, '', LDAP_ESCAPE_FILTER));
            } elseif ($nigend) {
                $filter = sprintf('(&(objectClass=person)(nigend=%s))', $ldap->escape($nigend, '', LDAP_ESCAPE_FILTER));
            }

            $query = $ldap->query($this->baseDn, $filter);

            $results = $query->execute()->toArray();

            if (empty($results)) {
                return [];
            }

            // Convertit les Entry objects en tableau compatible avec le code existant
            $entries = [];
            foreach ($results as $entry) {
                $attributes = [];
                foreach (['displayname', 'nigend', 'mail'] as $attr) {
                    $attributes[$attr] = $entry->getAttribute($attr) ?? [];
                }
                $entries[] = $attributes;
            }

            return $entries;
        } catch (ConnectionException $e) {
            return ['error' => 'Impossible de se connecter au serveur LDAP : ' . $e->getMessage()];
        } catch (LdapException $e) {
            return ['error' => 'Erreur lors de la recherche LDAP : ' . $e->getMessage()];
        }
    }
}
