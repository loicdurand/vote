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
    #[Route("/autocomplete/candidat", name: "autocomplete_candidat", methods: ["GET"])]
    public function autocompleteCandidat(Request $request): JsonResponse
    {
        $term = $request->query->get('term');

        if (empty($term) || strlen($term) < 2) {
            return new JsonResponse([]);
        }

        $nigend = null;
        $lastname = null;
        if (is_numeric($term)) {
            $nigend = $term;
        } else {
            $lastname = $term;
        }

        $results = $this->performLdapSearch($lastname, $nigend);

        if (isset($results['error'])) {
            return new JsonResponse(['error' => $results['error']]);
        }

        $suggestions = [];
        foreach ($results as $entry) {
            $suggestions[] = [
                'label' =>  $entry['nigend'][0] . ' - ' . $entry['displayname'][0] . ' - ' . $entry['mail'][0],
                'value' => $entry['nigend'][0]
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
            $ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldap://' . $_ENV['LDAP_HOST'] . ':' . $_ENV['LDAP_PORT']]);
            if ($_ENV['APP_ENV'] === 'dev')
                $ldap->bind('uid=' . $_ENV['LDAP_USER'] . ',ou=people,' . $_ENV['BASE_DN'], $_ENV['LDAP_PASSWORD']);
            else
                $ldap->bind(null, null);

            $filter = '(objectClass=person)';
            if ($lastname) {
                $filter = sprintf('(&(objectClass=person)(displayname=*%s*))', $ldap->escape($lastname, '', LDAP_ESCAPE_FILTER));
            } elseif ($nigend) {
                if ($_ENV['APP_ENV'] === 'dev')
                    $filter = sprintf('(&(objectClass=person)(nigend=%s))', $ldap->escape($nigend, '', LDAP_ESCAPE_FILTER));
                else
                    $filter = sprintf('(&(objectClass=person)(employeeNumber=%s))', $ldap->escape($nigend, '', LDAP_ESCAPE_FILTER));
            }

            $query = $ldap->query($_ENV['BASE_DN'], $filter);

            $results = $query->execute()->toArray();

            if (empty($results)) {
                return [];
            }

            // Convertit les Entry objects en tableau compatible avec le code existant
            $entries = [];
            foreach ($results as $entry) {

                $attributes = [];
                if ($_ENV['APP_ENV'] === 'dev') {
                    foreach (['displayname', 'nigend', 'mail', 'dptunite', 'employeetype', 'title', 'specialite'] as $attr) {
                        $attributes[$attr] = $entry->getAttribute($attr) ?? [];
                    }
                } else {
                    foreach (['displayName', 'employeeNumber', 'mail', 'postalCode', 'employeeType', 'title', 'specialite'] as $attr) {
                        if ($attr === "postalCode") {
                            $at = $entry->getAttribute($attr);
                            $postcode = $at[0];
                            $dptCode = str_starts_with($postcode, '97') ? substr($postcode, 0, 3) : substr($postcode, 0, 2);
                            $attributes[$attr] = [$dptCode];
                        } else if ($attr === "employeeNumber") {
                            $attributes["nigend"] = $entry->getAttribute($attr) ?? [];
                        } else {
                            $attributes[strtolower($attr)] = $entry->getAttribute($attr) ?? [];
                        }
                    }
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

    public static function countGroupMembers(string $groupDn): int
    {
        try {
            // Créer la connexion LDAP
            $ldap = Ldap::create('ext_ldap', [
                'connection_string' => 'ldap://' . $_ENV['LDAP_HOST'] . ':' . $_ENV['LDAP_PORT'],
            ]);
            if ($_ENV['APP_ENV'] === 'dev')
                // Bind avec l'utilisateur admin
                $ldap->bind('cn=' . $_ENV['LDAP_USER'] . ',ou=people,' . $_ENV['BASE_DN'], $_ENV['LDAP_PASSWORD']);
            else
                $ldap->bind(null, null);

            // Construire le filtre (ajusté pour LLDAP)
            $filter = '(&(objectClass=inetOrgPerson)(memberOf=cn=' . $groupDn . ',ou=groups,' . $_ENV['BASE_DN'] . '))';

            // Exécuter la requête
            $query = $ldap->query($_ENV['BASE_DN'], $filter, [
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
            throw new \Exception('Erreur de connexion LDAP : ' . $e->getMessage());
        } catch (LdapException $e) {
            // Erreur lors de la requête ou du bind
            throw new \Exception('Erreur LDAP : ' . $e->getMessage());
        } catch (\Exception $e) {
            // Autres erreurs
            throw new \Exception('Erreur générale : ' . $e->getMessage());
        }
    }
}
