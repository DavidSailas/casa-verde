// Drag-and-drop ordering for the lists in Site content. Works with a mouse, a finger or a pen,
// and with the keyboard (focus the handle, then press the up or down arrow).
// The new order is saved as soon as the item is dropped.

const toast = (() => {
    let node = null;
    let timer = null;

    return (message, isError = false) => {
        if (!node) {
            node = document.createElement('div');
            node.setAttribute('role', 'status');
            node.className = 'fixed bottom-5 right-5 z-50 rounded-lg px-4 py-2 text-sm shadow-lg transition-opacity';
            document.body.appendChild(node);
        }

        node.textContent = message;
        node.classList.toggle('bg-forest-900', !isError);
        node.classList.toggle('text-sand-50', !isError);
        node.classList.toggle('bg-danger-700', isError);
        node.classList.toggle('text-white', isError);
        node.style.opacity = '1';

        clearTimeout(timer);
        timer = setTimeout(() => { node.style.opacity = '0'; }, 2500);
    };
})();

const setupList = (list) => {
    const url = list.dataset.reorderUrl;
    const token = list.dataset.csrf;
    const rows = () => [...list.querySelectorAll(':scope > li[data-id]')];
    const order = () => rows().map((li) => li.dataset.id);

    let saved = order();
    let dragging = null;
    let saveTimer = null;

    const save = async () => {
        const ids = order();

        if (ids.join(',') === saved.join(',')) return;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ ids }),
            });

            if (!response.ok) throw new Error(String(response.status));

            saved = ids;
            toast('Order saved.');
        } catch {
            // Put the list back the way the server still has it.
            const byId = new Map(rows().map((li) => [li.dataset.id, li]));
            saved.forEach((id) => list.appendChild(byId.get(id)));
            toast('The order could not be saved. Please try again.', true);
        }
    };

    list.addEventListener('pointerdown', (event) => {
        const handle = event.target.closest('[data-drag-handle]');

        if (!handle || (event.pointerType === 'mouse' && event.button !== 0)) return;

        dragging = handle.closest('li[data-id]');
        dragging.classList.add('is-dragging');
        handle.setPointerCapture(event.pointerId);
        event.preventDefault();
    });

    list.addEventListener('pointermove', (event) => {
        if (!dragging) return;

        // Scroll the page when the item is dragged close to the top or bottom edge.
        if (event.clientY < 80) window.scrollBy(0, -14);
        if (event.clientY > window.innerHeight - 80) window.scrollBy(0, 14);

        const over = document.elementFromPoint(event.clientX, event.clientY)?.closest('li[data-id]');

        if (!over || over === dragging || over.parentElement !== list) return;

        const box = over.getBoundingClientRect();
        const below = event.clientY > box.top + box.height / 2;

        list.insertBefore(dragging, below ? over.nextSibling : over);
    });

    const drop = () => {
        if (!dragging) return;

        dragging.classList.remove('is-dragging');
        dragging = null;
        save();
    };

    list.addEventListener('pointerup', drop);
    list.addEventListener('pointercancel', drop);

    list.addEventListener('keydown', (event) => {
        const handle = event.target.closest('[data-drag-handle]');

        if (!handle || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;

        event.preventDefault();

        const item = handle.closest('li[data-id]');
        const sibling = event.key === 'ArrowUp' ? item.previousElementSibling : item.nextElementSibling;

        if (!sibling) return;

        list.insertBefore(item, event.key === 'ArrowUp' ? sibling : sibling.nextSibling);
        handle.focus();

        // Several quick key presses are saved once.
        clearTimeout(saveTimer);
        saveTimer = setTimeout(save, 600);
    });
};

document.querySelectorAll('[data-reorder-url]').forEach(setupList);
