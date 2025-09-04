export default () => {

    if (document.getElementById('suggest-candidat-list') === null)
        return false;

    const input = document.getElementById('suggest-candidat');
    const suggestionsList = document.getElementById('suggest-candidat-list');

    input.addEventListener('keyup', async () => {
        console.log(input);
        const term = (input as HTMLInputElement).value.trim();
        if (term.length < 2) {
            suggestionsList.innerHTML = ''; // Vide les suggestions si saisie trop courte
            return;
        }

        try {
            const response = await fetch(`/autocomplete/candidat?term=${encodeURIComponent(term)
                }`);
            const suggestions = await response.json();

            // Vide la liste actuelle
            suggestionsList.innerHTML = '';

            // Remplit la datalist avec les nouvelles suggestions
            suggestions.forEach(suggestion => {
                const option = document.createElement('option');
                option.value = suggestion.value; // Valeur insérée dans l'input
                option.textContent = suggestion.label; // Texte affiché (ex: "Doe (12345)")
                suggestionsList.appendChild(option);
            });
        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions:', error);
            suggestionsList.innerHTML = '';
        }
    });
}