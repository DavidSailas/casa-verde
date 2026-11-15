// A simple editor for journal articles. The article text is saved in the same light format the site
// already understands (## heading, ### smaller heading, - bullet list, **bold**), so nothing about
// existing articles changes: this only adds a toolbar, a live preview and a word count.

const escapeHtml = (text) => text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const bold = (text) => escapeHtml(text).replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>');
const withBreaks = (text) => text.replace(/\r?\n/g, '<br>');

// The same rules as resources/views/journal/show.blade.php.
const render = (text) => {
    const blocks = text.split(/(?:\r?\n){2,}/).filter((block) => block.trim() !== '');

    return blocks.map((raw) => {
        const block = raw.trim();
        const lines = block.split(/\r?\n/);
        const rest = lines.slice(1).join('\n').trim();
        const first = lines[0].replace(/^#+\s*/, '');
        const tail = rest ? `<p>${withBreaks(bold(rest))}</p>` : '';

        if (block.startsWith('####')) return `<h4>${escapeHtml(first)}</h4>${tail}`;
        if (block.startsWith('###')) return `<h3>${escapeHtml(first)}</h3>${tail}`;
        if (block.startsWith('##')) return `<h2>${escapeHtml(first)}</h2>${tail}`;

        if (lines.every((line) => /^\s*[-*]\s+/.test(line))) {
            return `<ul>${lines.map((line) => `<li>${bold(line.replace(/^\s*[-*]\s+/, ''))}</li>`).join('')}</ul>`;
        }

        return `<p>${withBreaks(bold(block))}</p>`;
    }).join('');
};

const setup = (textarea) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'overflow-hidden rounded-lg border border-sand-300 bg-white';

    const toolbar = document.createElement('div');
    toolbar.className = 'flex flex-wrap items-center gap-1 border-b border-sand-200 bg-sand-50 px-2 py-1.5';

    const preview = document.createElement('div');
    preview.className = 'article-preview hidden min-h-[16rem] space-y-4 px-4 py-4 text-ink [&_h2]:font-display [&_h2]:text-3xl [&_h2]:font-semibold [&_h3]:font-display [&_h3]:text-2xl [&_h3]:font-semibold [&_h4]:font-semibold [&_ul]:list-disc [&_ul]:pl-6';

    const footer = document.createElement('div');
    footer.className = 'border-t border-sand-200 bg-sand-50 px-3 py-1.5 text-xs text-ink-soft';

    textarea.before(wrapper);
    wrapper.append(toolbar, textarea, preview, footer);
    textarea.classList.add('!rounded-none', '!border-0', '!shadow-none', 'focus:!ring-0');

    // Typing through execCommand keeps the browser's undo (Ctrl+Z) working.
    const insert = (text) => {
        textarea.focus();

        if (!document.execCommand('insertText', false, text)) {
            textarea.setRangeText(text, textarea.selectionStart, textarea.selectionEnd, 'end');
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    const wrapSelection = (mark) => {
        const { selectionStart: start, selectionEnd: end, value } = textarea;
        const selected = value.slice(start, end);

        if (selected.startsWith(mark) && selected.endsWith(mark) && selected.length >= mark.length * 2) {
            insert(selected.slice(mark.length, -mark.length));
            return;
        }

        insert(`${mark}${selected || 'text'}${mark}`);

        if (!selected) {
            textarea.setSelectionRange(start + mark.length, start + mark.length + 4);
        }
    };

    // Applies to every line touched by the selection.
    const prefixLines = (prefix) => {
        const { value } = textarea;
        const from = value.lastIndexOf('\n', textarea.selectionStart - 1) + 1;
        const newline = value.indexOf('\n', textarea.selectionEnd);
        const to = newline === -1 ? value.length : newline;
        const lines = value.slice(from, to).split('\n');

        const strip = (line) => line.replace(/^(#{2,4}\s+|[-*]\s+)/, '');
        const allHave = lines.every((line) => line.startsWith(prefix));

        textarea.setSelectionRange(from, to);
        insert(lines.map((line) => (allHave ? strip(line) : prefix + strip(line))).join('\n'));
    };

    const buttons = [
        { label: 'Heading', title: 'Heading (##)', html: 'H2', run: () => prefixLines('## ') },
        { label: 'Smaller heading', title: 'Smaller heading (###)', html: 'H3', run: () => prefixLines('### ') },
        { label: 'Bold', title: 'Bold (Ctrl+B)', html: '<strong>B</strong>', run: () => wrapSelection('**') },
        { label: 'Bullet list', title: 'Bullet list', html: '&bull; List', run: () => prefixLines('- ') },
    ];

    buttons.forEach(({ label, title, html, run }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.title = title;
        button.setAttribute('aria-label', label);
        button.innerHTML = html;
        button.className = 'rounded-md px-2.5 py-1 text-sm text-forest-900 hover:bg-sand-200 focus-visible:ring-4 focus-visible:ring-gold-400/40';
        button.addEventListener('click', run);
        toolbar.appendChild(button);
    });

    const spacer = document.createElement('span');
    spacer.className = 'flex-1';
    toolbar.appendChild(spacer);

    const tabs = {};

    ['Write', 'Preview'].forEach((name) => {
        const tab = document.createElement('button');
        tab.type = 'button';
        tab.textContent = name;
        tab.className = 'rounded-md px-3 py-1 text-sm';
        tab.addEventListener('click', () => show(name === 'Preview'));
        toolbar.appendChild(tab);
        tabs[name] = tab;
    });

    const show = (previewing) => {
        textarea.classList.toggle('hidden', previewing);
        preview.classList.toggle('hidden', !previewing);

        if (previewing) {
            preview.innerHTML = textarea.value.trim() === ''
                ? '<p class="text-ink-soft">Nothing to preview yet.</p>'
                : render(textarea.value);
        }

        buttons.forEach((_, i) => { toolbar.children[i].disabled = previewing; toolbar.children[i].classList.toggle('opacity-40', previewing); });

        Object.entries(tabs).forEach(([name, tab]) => {
            const active = (name === 'Preview') === previewing;
            tab.classList.toggle('bg-forest-900', active);
            tab.classList.toggle('text-sand-50', active);
            tab.classList.toggle('text-forest-900', !active);
            tab.setAttribute('aria-pressed', String(active));
        });
    };

    const count = () => {
        const words = (textarea.value.match(/\S+/g) || []).length;
        const minutes = Math.max(1, Math.round(words / 200));

        footer.textContent = words === 0 ? 'No text yet.' : `${words} ${words === 1 ? 'word' : 'words'} · about ${minutes} min read`;
    };

    textarea.addEventListener('input', count);

    textarea.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
            event.preventDefault();
            wrapSelection('**');
        }
    });

    show(false);
    count();
};

document.querySelectorAll('textarea[data-article-editor]').forEach(setup);
