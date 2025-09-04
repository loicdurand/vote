<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserSearchController extends AbstractController
{
    private string $ldapHost = 'ldap://ldap:389'; // Ajuste si port spécifique, ex: ldap://ldap:389
    private string $baseDn = 'dc=exemple,dc=fr';

    /**
     * Route pour la recherche complète (par nom ou par NI).
     * Appelée via formulaire POST ou GET.
     *
     * @Route("/search/users", name="search_users", methods={"GET", "POST"})
     */
    public function searchUsers(Request $request): Response
    {
        $lastname = $request->query->get('lastname') ?? $request->request->get('lastname');
        $ni = $request->query->get('ni') ?? $request->request->get('ni');

        if (empty($lastname) && empty($ni)) {
            return $this->render('search/form.html.twig', ['results' => [], 'error' => 'Veuillez saisir un nom ou un NI.']);
        }

        $results = $this->performLdapSearch($lastname, $ni);

        if (isset($results['error'])) {
            return $this->render('search/form.html.twig', ['results' => [], 'error' => $results['error']]);
        }

        return $this->render('search/results.html.twig', ['results' => $results]);
    }

    /**
     * Route pour l'autocomplete (recherche partielle par nom, renvoie JSON pour JS).
     * Appelée via AJAX.
     *
     * @Route("/autocomplete/lastname", name="autocomplete_lastname", methods={"GET"})
     */
    public function autocompleteLastname(Request $request): JsonResponse
    {
        $term = $request->query->get('term'); // Le terme saisi par l'utilisateur

        if (empty($term) || strlen($term) < 2) { // Évite les recherches trop courtes
            return new JsonResponse([]);
        }

        $results = $this->performLdapSearch($term); // Recherche partielle par nom seulement

        if (isset($results['error'])) {
            return new JsonResponse(['error' => $results['error']]);
        }

        // Formatte pour autocomplete : tableau de suggestions (ex: nom + autres infos)
        $suggestions = [];
        foreach ($results as $entry) {
            $suggestions[] = [
                'label' => $entry['sn'][0] . ' (' . ($entry['ni'][0] ?? 'N/A') . ')', // Ex: "Doe (12345)"
                'value' => $entry['sn'][0], // Valeur à insérer dans l'input
                'ni' => $entry['ni'][0] ?? null, // Infos supplémentaires si besoin
            ];
        }

        return new JsonResponse($suggestions);
    }

    /**
     * Fonction privée pour effectuer la recherche LDAP.
     * @param string|null $lastname Recherche par sn (nom de famille), supporte wildcard * pour partiel.
     * @param string|null $ni Recherche exacte par ni.
     * @return array Résultats ou ['error' => message]
     */
    private function performLdapSearch(?string $lastname = null, ?string $ni = null): array
    {
        $ldapConn = ldap_connect($this->ldapHost);
        if (!$ldapConn) {
            return ['error' => 'Impossible de se connecter au serveur LDAP.'];
        }

        // Bind anonyme (ajoute ldap_bind($ldapConn, 'dn', 'password') si authentification requise)
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        $filter = '(objectClass=person)'; // Filtre de base (ajuste si tes entrées ont une classe spécifique)

        if ($lastname) {
            $filter = '(&(objectClass=person)(sn=*' . ldap_escape($lastname, '', LDAP_ESCAPE_FILTER) . '*))'; // Recherche partielle avec wildcard
        } elseif ($ni) {
            $filter = '(&(objectClass=person)(ni=' . ldap_escape($ni, '', LDAP_ESCAPE_FILTER) . '))'; // Recherche exacte
        }

        $search = ldap_search($ldapConn, $this->baseDn, $filter, ['sn', 'ni', 'cn', 'mail']); // Attributs à récupérer (ajoute d'autres si besoin)
        if (!$search) {
            ldap_unbind($ldapConn);
            return ['error' => 'Erreur lors de la recherche LDAP: ' . ldap_error($ldapConn)];
        }

        $entries = ldap_get_entries($ldapConn, $search);
        if ($entries['count'] === 0) {
            ldap_unbind($ldapConn);
            return [];
        }

        $results = [];
        for ($i = 0; $i < $entries['count']; $i++) {
            $results[] = $entries[$i]; // Entrée complète (sn, ni, etc.)
        }

        ldap_unbind($ldapConn);
        return $results;
    }
}
