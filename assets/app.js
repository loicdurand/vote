import '@gouvfr/dsfr/dist/dsfr.css';
import "@gouvfr/dsfr/dist/utility/icons/icons.main.min.css";
import './styles/app.scss';

// JAVASCRIPTS
import "@gouvfr/dsfr/dist/dsfr/dsfr.module";
import './bootstrap.ts';

import stepper_init from './javascripts/stepper.ts';
import tile_menu_init from './javascripts/tile-menu.ts';

document.addEventListener('DOMContentLoaded', () => {
    stepper_init();
    tile_menu_init();
});


