import '@gouvfr/dsfr/dist/dsfr.css';
import "@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "@gouvfr/dsfr/dist/dsfr/dsfr.module";
import './bootstrap.ts';

import * as utils from './javascripts/utils.ts';
import stepper_init from './javascripts/stepper.ts';
import autcomplete_candidats_init from './javascripts/autocomplete-candidats.ts';

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
            [, , , election_id] = location.pathname.split(/\//),
            url = '/remove/candidat/' + election_id,
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
    }

});

stepper_init();
autcomplete_candidats_init();

