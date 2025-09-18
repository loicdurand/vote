import '/node_modules/@gouvfr/dsfr/dist/dsfr.css';
import "/node_modules/@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "/node_modules/@gouvfr/dsfr/dist/dsfr/dsfr.module";
import './bootstrap.ts';

import * as utils from './javascripts/utils.ts';
import stepper_init from './javascripts/stepper.ts';
import autcomplete_candidats_init from './javascripts/autocomplete-candidats.ts';
import stats_init from './javascripts/stats.ts';

document.addEventListener('click', async ({ target }) => {
    // Initialisation du menu dans les tuiles
    if (target.matches('.custom-tile-menu--opener')) {
        const tile = utils.getParent(target, '.custom-card-container');
        const menu = tile.querySelector('.custom-tile-menu');
        menu.classList.add('active');
    } else if (target.matches('.custom-tile-menu--closer')) {
        const tile = utils.getParent(target, '.custom-card-container');
        const menu = tile.querySelector('.custom-tile-menu');
        menu.classList.remove('active');
        // ^ fin menu dans les tuiles 
    } else if (target.matches('#candidats-tbody .fr-icon-delete-line')) {
        // Suppr. d'un candidat
        const // 
            nigend = target.dataset.nigend,
            [, , , , election_id] = location.pathname.split(/\//),
            url = '/eleksyon/remove/candidat/' + election_id,
            body = JSON.stringify({ nigend }),
            options = {
                method: 'post',
                headers: {},
                body
            },
            response = await fetch(url, options);

        if (!response.ok) {
            const message = 'Error with Status Code: ' + response.status;
            throw new Error(message);
        } else {
            target.parentElement.parentElement.outerHTML = "";
        }
    } else if (target.matches('#secret-reveal--btn')) {
        const input = target.previousElementSibling;
        input.type = input.type === 'password' ? 'text' : 'password';
        target.classList.toggle('fr-icon-eye-line');
        target.classList.toggle('fr-icon-eye-off-line');
    } else if (target.matches('#secret-reveal--submit')) {
        const // 
            input = document.getElementById('secret-reveal'),
            message = document.getElementById('secret-reveal-messages'),
            secret = input.value,
            url = '/eleksyon/index/retrieve-data',
            body = JSON.stringify({ secret }),
            options = {
                method: 'post',
                headers: {},
                body
            };

        if (!secret) {
            message.innerText = "Vous devez saisir une clé pour continuer!";
            return false;
        }

        message.innerText = "";
        const response = await fetch(url, options);

        if (!response.ok) {
            const message = 'Error with Status Code: ' + response.status;
            throw new Error(message);
        } else {
            const tables_ctnr = document.getElementById('tables-ctnr');
            tables_ctnr.innerHTML = await response.text();
        }

    } else if (target.matches('#copy-link-to-clipboard *')) {
        const btn = utils.getParent(target, '#copy-link-to-clipboard');
        const link = btn.dataset.link;
        utils.copyTextToClipboard(link);
    }

});

document.addEventListener('change', async ({ target }) => {
    if (target.matches('#check-candidatures-libres')) {
        // active ou désactive les candidatures libres
        const // 
            [, , , , election_id] = location.pathname.split(/\//),
            url = '/eleksyon/setcandidaturesspontanees/' + election_id,
            body = JSON.stringify({ value: target.checked }),
            options = {
                method: 'post',
                headers: {},
                body
            },
            response = await fetch(url, options);

        if (!response.ok) {
            const message = 'Error with Status Code: ' + response.status;
            throw new Error(message);
        } else {
            const value = await response.json();
            if (value)
                target.setAttribute('checked', 'checked');
            else
                target.removeAttribute('checked');
        }
    } else if (target.matches('#check-candidature-spontanee')) {
        // soumission d'une candidature libre
        const { checked } = target;
        if (checked) {
            const // 
                data = { ...target.dataset },
                [, , , , , election_id] = location.pathname.split(/\//),
                url = '/eleksyon/create/candidat/' + election_id,
                body = JSON.stringify(data),
                options = {
                    method: 'post',
                    headers: {},
                    body
                },
                response = await fetch(url, options),
                result = await response.json();

            if (result.success)
                location.reload();
        } else {
            const // 
                nigend = target.dataset.nigend,
                [, , , , , election_id] = location.pathname.split(/\//),
                url = '/eleksyon/remove/candidat/' + election_id,
                body = JSON.stringify({ nigend }),
                options = {
                    method: 'post',
                    headers: {},
                    body
                },
                response = await fetch(url, options),
                result = await response.json();
            if (result.success)
                location.reload();
        }
    } else if (target.matches('#toggle-print-all')) {
        // affiche toutes les élections, même si celles qui ne me concernent pas
        const main = document.getElementById('main-elections_en_cours');
        main.classList.toggle('print-all');
    }
});

stepper_init();
autcomplete_candidats_init();
stats_init();

