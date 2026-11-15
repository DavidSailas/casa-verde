// "Display size" box of the menu entries: shows the effect of the chosen size in the preview straight away.
// The choices are saved with the form like any other field; this only drives the preview.

document.querySelectorAll('[data-size-box]').forEach((box) => {
    const preview = box.querySelector('[data-size-preview]');
    const base = parseFloat(box.dataset.base || '1') || 1;

    const apply = () => {
        box.querySelectorAll('input[type="radio"]:checked').forEach((radio) => {
            const name = radio.dataset.sizeVar;
            const factor = parseFloat(radio.dataset.sizeValue);

            if (!name || Number.isNaN(factor)) return;

            // A dish's own size is relative to the size chosen for the whole menu.
            preview.style.setProperty(name, String(name === '--p' ? base * factor : factor));
        });
    };

    box.addEventListener('change', apply);

    box.querySelector('[data-size-reset]')?.addEventListener('click', () => {
        box.querySelectorAll('input[type="radio"][data-default]').forEach((radio) => { radio.checked = true; });
        apply();
    });

    apply();
});
