(() => {

    if (document.querySelector('.fr-tile') === null)
        return false;

    const tiles = document.querySelectorAll('.fr-tile');
    tiles.forEach(tile => {
        const opener = tile.querySelector('.custom-tile-menu--opener');
        const menu = tile.querySelector('.custom-tile-menu');
        const closer = tile.querySelector('.custom-tile-menu--closer');

        opener.addEventListener('click', e => {
            menu.classList.add('active');
        });

        closer.addEventListener('click', e => {
            menu.classList.remove('active');
        });

    });

})()