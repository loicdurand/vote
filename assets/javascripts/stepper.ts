(() => {

    if (document.querySelector('.fr-stepper') === null)
        return false;

    const nb_steps = 3;
    const prev = document.getElementById('prev-btn');
    const next = document.getElementById('next-btn');
    const submit = document.getElementById('submit');
    const step_contents = [];
    let current_step = 0;

    for (let i = current_step; i < 3; i++) {
        const step_content = document.getElementById(`step-${i}`);
        step_contents.push(step_content);
    }

    prev.addEventListener('click', e => {
        current_step -= 1;
        next.classList.remove('fr-hidden');
        submit.classList.add('fr-hidden');
        if (current_step > 0) {
            prev.classList.remove('fr-hidden');
        } else {
            prev.classList.add('fr-hidden');
        }

        displayStep(current_step);

    })

    next.addEventListener('click', e => {
        current_step += 1;
        prev.classList.remove('fr-hidden');
        if (current_step < nb_steps - 1) {
            next.classList.remove('fr-hidden');
            submit.classList.add('fr-hidden');
        } else {
            next.classList.add('fr-hidden');
            submit.classList.remove('fr-hidden');
        }
        displayStep(current_step);

    })

    function displayStep(current_step) {
        step_contents.forEach((content, index) => {
            if (index !== current_step)
                content.classList.add('fr-hidden');
            else
                content.classList.remove('fr-hidden');
        });
    }

})();