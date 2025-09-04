import '@gouvfr/dsfr/dist/dsfr.css';
import "@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "@gouvfr/dsfr/dist/dsfr/dsfr.module";
import './bootstrap.ts';

import * as utils from './javascripts/utils.ts';
import stepper_init from './javascripts/stepper.ts';

document.addEventListener('click', ({ target }) => {

    // Initialisation du menu dans les tuiles
    if (target.matches('.custom-tile-menu--opener')) {
        const tile = utils.getParent(target, '.custom-card-container');
        const menu = tile.querySelector('.custom-tile-menu');
        menu.classList.add('active');
    } else if (target.matches('.custom-tile-menu--closer')) {
        const tile = utils.getParent(target, '.custom-card-container');
        const menu = tile.querySelector('.custom-tile-menu');
        menu.classList.remove('active');
    }

});

stepper_init();

