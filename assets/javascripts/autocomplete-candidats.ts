export default () => {

    if (document.getElementById('suggest-candidat-list') === null)
        return false;

    type Data = {
        nigend, displayname, mail
    };

    const input = document.getElementById('suggest-candidat');
    const suggestionsList = document.getElementById('suggest-candidat-list');
    const submit = document.getElementById('suggestion-submit');
    let data: Data;

    input.addEventListener('input', async (e) => {
        const term = (input as HTMLInputElement).value.trim();
        if (term.length < 2) {
            suggestionsList.innerHTML = ''; // Vide les suggestions si saisie trop courte
            return;
        }

        try {
            const response = await fetch(`/autocomplete/candidat?term=${encodeURIComponent(term)}`);
            const suggestions = await response.json();

            // Vide la liste actuelle (hors choix dans la datalist)
            if (e instanceof InputEvent)
                suggestionsList.innerHTML = '';

            // Remplit la datalist avec les nouvelles suggestions
            suggestions.forEach(suggestion => {
                const option = document.createElement('option');
                option.dataset.value = suggestion.value; // Valeur insérée dans l'input
                option.textContent = suggestion.label; // Texte affiché (ex: "Doe (12345)")
                suggestionsList.appendChild(option);
            });

            // actions déclenchées lors du choix dans la datalist
            const input = e.target as HTMLInputElement;
            const [nigend] = input.value.split(/\s/);
            const list = input.getAttribute('list');
            const listEl = document.getElementById(list);
            const options = listEl.childNodes;

            for (var i = 0; i < options.length; i++) {

                if ((options[i] as HTMLElement).dataset.value.trim() === nigend.trim()) {
                    const [nigend, displayname, mail] = options[i].textContent.split(' - ');
                    data = { nigend, displayname, mail };
                    submit.classList.remove('fr-hidden');
                    console.log('item selected: ' + nigend);
                    break;
                }
            }

        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions:', error);
            suggestionsList.innerHTML = '';
        }
    });

    submit.addEventListener('click', e => {
        const tbody = document.getElementById('candidats-tbody');
        const zero = document.getElementById('table-zero-candidat');
        const tr = document.createElement('tr');
        const { nigend, displayname, mail } = data;
        const values = [nigend, displayname, mail, '--'];

        if (zero !== null)
            zero.outerHTML = '';
        values.forEach(v => {
            const td = document.createElement('td');
            td.innerText = v;
            tr.appendChild(td);
        })

        tbody.appendChild(tr);


        submit.classList.add('fr-hidden');
    });

}