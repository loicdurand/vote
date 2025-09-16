export default () => {

    if (document.getElementById('suggest-candidat-list') === null)
        return false;

    const input = document.getElementById('suggest-candidat') as HTMLInputElement;
    const suggestionsList = document.getElementById('suggest-candidat-list');
    const submit = document.getElementById('suggestion-submit');
    let data;

    input.addEventListener('input', async (e) => {
        const term = input.value.trim();
        if (term.length < 2) {
            suggestionsList.innerHTML = ''; // Vide les suggestions si saisie trop courte
            return;
        }

        try {
            const response = await fetch(`/eleksyon/autocomplete/candidat?term=${encodeURIComponent(term)}`);
            const suggestions = await response.json();

            // Vide la liste actuelle (hors choix dans la datalist)
            if (e instanceof InputEvent)
                suggestionsList.innerHTML = '';

            // Remplit la list avec les nouvelles suggestions
            suggestions.forEach(suggestion => {
                const listElt = document.createElement('li');
                listElt.dataset.value = suggestion.value; // Valeur insérée dans l'input
                listElt.textContent = suggestion.label; // Texte affiché (ex: "Doe (12345)")
                suggestionsList.appendChild(listElt);
            });

        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions:', error);
            suggestionsList.innerHTML = '';
        }
    });

    suggestionsList.addEventListener('click', (e) => {
        // actions déclenchées lors du choix dans la datalist
        const target = e.target as HTMLInputElement;
        // const [nigend] = target.dataset.value.split(/\s/);
        // const list = input.dataset.list;
        // const listEl = document.getElementById(list);
        // const listElts = listEl.childNodes;

        // for (var i = 0; i < listElts.length; i++) {

        //     if ((listElts[i] as HTMLElement).dataset.value.trim() === nigend.trim()) {
        const [nigend, displayname, mail] = target.textContent.split(' - ');
        data = { nigend, displayname, mail };
        input.value = target.textContent;
        suggestionsList.innerHTML = '';
        submit.classList.remove('fr-hidden');
        console.log('item selected: ' + nigend);
        // break;
        //     }
        // }
    });

    submit.addEventListener('click', async e => {

        const tbody = document.getElementById('candidats-tbody');
        const zero = document.getElementById('table-zero-candidat');
        const tr = document.createElement('tr');
        const [nigend, displayname] = input.value.split(' - ');
        const values = [nigend, displayname];

        // AJAX POST
        const // 
            [, , , , election_id] = location.pathname.split(/\//),
            url = '/eleksyon/create/candidat/' + election_id,
            body = JSON.stringify(data),
            options = {
                method: 'post',
                headers: {},
                body
            },
            response = await fetch(url, options);

        if (!response.ok) {
            const message = 'Error with Status Code: ' + response.status;
            throw new Error(message);
        }

        const // 
            result = await response.json(),
            alert = document.getElementById('error-insertion-candidat'),
            alert_title = document.getElementById('error-insertion-candidat__title');
        // FIN AJAX POST

        if (zero !== null)
            zero.outerHTML = '';

        if (result.success) {        // ajout des colonnes "nigend" et "displayname"

            alert.classList.add('fr-hidden');

            values.forEach(v => {
                const td = document.createElement('td');
                td.innerText = v;
                tr.appendChild(td);
            })

            // ajout d'un bouton de suppr. du candidat
            const td = document.createElement('td');
            const btn = document.createElement('button');
            btn.setAttribute('type', 'button');
            btn.innerText = 'Retirer de la liste';
            btn.setAttribute('title', 'Retirer de la liste');
            btn.dataset.nigend = nigend;

            ['fr-btn', 'fr-icon-delete-line', 'fr-btn--tertiary-no-outline'].forEach(cls => {
                btn.classList.add(cls);
            });

            td.appendChild(btn);
            tr.appendChild(td);

            tbody.appendChild(tr);
            input.value = "";
        } else {
            alert_title.innerText = result.error;
            alert.classList.remove('fr-hidden');
        }


        submit.classList.add('fr-hidden');
    });

}