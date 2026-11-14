<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=20261017" sizes="any">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261017">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yoga Reset | Casa Verde Cliff Resort &amp; Spa</title>
    @vite([
        'resources/css/header.css',
        'resources/css/pages.css',
        'resources/css/yoga.css',
        'resources/css/theme.css',
        'resources/css/footer.css','resources/css/rule.css',
        'resources/js/app.js'
    ])
    <style>
        /* Yoga Reset page: refined styling in the resort's own palette (forest green, gold, cream).
           Text, icons and layout are unchanged. Inline so it shows without a rebuild;
           can be moved into resources/css/yoga.css. */
        main.yg {
            --yg-dark: #06211b;
            --yg-dark-2: #0e3629;
            --yg-cream: #f7f2ea;
            --yg-sand: #efe6d6;
            --yg-gold: #dcb887;
            --yg-gold-deep: #a47a44;
            --yg-green: #17493a;
            --yg-ink: #12241f;
            --yg-muted: #4a5852;
            --yg-line: #e3d6c2;
            --yg-shadow: 0 24px 40px -28px rgba(6, 33, 27, .55);
            background: var(--yg-cream);
        }

        /* ---------- Type ---------- */
        main.yg h1, main.yg h2, main.yg h3 { font-weight: 600; letter-spacing: 0; line-height: 1.08; }
        main.yg h2 { font-size: clamp(2.3rem, 4.6vw, 3.6rem); }
        main.yg .yg-split__text p, main.yg .yg-more p, main.yg .yg-resort__text > p, main.yg .yg-teacher__text > p { font-size: 1.1rem; line-height: 1.8; max-width: 62ch; }
        main.yg .yg-label {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 18px;
            font-size: .74rem; font-weight: 500; letter-spacing: .28em;
            color: var(--yg-gold-deep);
        }
        main.yg .yg-label::before { content: ""; flex: none; width: 30px; height: 1px; background: currentColor; }
        main.yg .yg-label--gold { color: var(--yg-gold); }

        /* ---------- Buttons ---------- */
        main.yg .yg-btn {
            min-height: 52px; padding: 0 30px;
            font-size: .8rem; font-weight: 500; letter-spacing: .12em; text-transform: uppercase;
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease, color .25s ease, border-color .25s ease;
        }
        main.yg .yg-btn--sm { min-height: 42px; padding: 0 20px; font-size: .74rem; }
        main.yg .yg-btn--gold { background: linear-gradient(135deg, #e8c797, #cfa066); color: var(--yg-dark); box-shadow: 0 14px 24px -14px rgba(185, 139, 85, .95); }
        main.yg .yg-btn--gold:hover { background: linear-gradient(135deg, #f0d2a6, #d9ab72); box-shadow: 0 18px 28px -14px rgba(185, 139, 85, 1); }
        main.yg .yg-btn--outline { border-color: rgba(246, 232, 208, .55); color: #f6e8d0; }
        main.yg .yg-btn--outline:hover { background: rgba(246, 232, 208, .12); border-color: #f6e8d0; }
        main.yg .yg-btn--green { background: var(--yg-dark); color: #e6c28f; border-color: var(--yg-dark); }
        main.yg .yg-btn--green:hover { background: var(--yg-gold); border-color: var(--yg-gold); color: var(--yg-dark); }
        main.yg .yg-btn--outline-dark { border-color: var(--yg-gold-deep); color: var(--yg-dark); }
        main.yg .yg-btn--outline-dark:hover { background: var(--yg-dark); border-color: var(--yg-dark); color: #e6c28f; }
        main.yg .yg-btn--white { background: #fff8ec; color: var(--yg-dark); }
        main.yg .yg-btn--white:hover { background: var(--yg-gold); }

        /* ---------- Hero ---------- */
        main.yg .yg-hero { display: flex; align-items: center; min-height: clamp(520px, 78vh, 700px); padding: clamp(56px, 8vw, 110px) 0 0; }
        main.yg .yg-hero::before {
            background:
                linear-gradient(95deg, rgba(6, 33, 27, .96) 0%, rgba(6, 33, 27, .84) 46%, rgba(6, 33, 27, .42) 100%),
                linear-gradient(0deg, rgba(6, 33, 27, .75), transparent 40%);
        }
        main.yg .yg-hero .yg-wrap { position: relative; width: 100%; }
        main.yg .yg-hero__body { gap: 22px; max-width: 720px; padding-bottom: clamp(30px, 5vw, 56px); }
        main.yg .yg-eyebrow { display: inline-flex; align-items: center; gap: 14px; font-size: .76rem; font-weight: 500; letter-spacing: .3em; color: var(--yg-gold); }
        main.yg .yg-eyebrow::before { content: ""; width: 34px; height: 1px; background: var(--yg-gold); }
        main.yg .yg-hero h1 { font-size: clamp(3.4rem, 9.5vw, 6.6rem); font-weight: 500; line-height: .98; letter-spacing: -.005em; text-shadow: 0 4px 36px rgba(0, 0, 0, .4); }
        main.yg .yg-hero__lead { max-width: 540px; font-size: clamp(1.15rem, 2.2vw, 1.4rem); line-height: 1.6; color: #f3e9d8; }
        main.yg .yg-chip { padding: 8px 16px; font-size: .84rem; letter-spacing: .02em; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
        main.yg .yg-chip--light { background: rgba(220, 184, 135, .2); border-color: rgba(220, 184, 135, .55); color: #f8e8cc; }
        main.yg .yg-chip--light .yg-dot { background: var(--yg-gold); }
        main.yg .yg-facts { gap: 12px 34px; margin-top: 18px; padding-top: 26px; border-top-color: rgba(220, 184, 135, .35); }
        main.yg .yg-facts li { font-size: .95rem; color: #f3e9d8; }
        main.yg .yg-facts li::before { width: 7px; height: 7px; }

        /* ---------- Notice ---------- */
        main.yg .yg-section--tight { padding: clamp(40px, 5vw, 64px) 0 0; }
        main.yg .yg-notice {
            gap: 18px; padding: clamp(24px, 3.4vw, 40px);
            border: 1px solid var(--yg-line); border-left: 4px solid var(--yg-gold);
            border-radius: 24px; background: #fffaf2;
            box-shadow: 0 30px 50px -34px rgba(6, 33, 27, .6);
        }
        main.yg .yg-notice__label { min-height: 0; align-self: start; padding: 9px 18px; border: 1px solid rgba(201, 160, 106, .5); border-radius: 999px; background: #f6ead6; color: #6b4a1f; font-size: .76rem; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; }
        main.yg .yg-notice__label .yg-dot { background: var(--yg-gold-deep); }
        main.yg .yg-notice h2 { font-size: clamp(1.7rem, 3vw, 2.3rem); }
        main.yg .yg-notice p { font-size: 1.05rem; line-height: 1.75; }

        /* ---------- Sections ---------- */
        main.yg .yg-section { padding: clamp(64px, 8vw, 116px) 0; }
        main.yg .yg-section--sand { background: linear-gradient(180deg, var(--yg-sand), #f3ebdd); }
        main.yg .yg-more summary { padding: 9px 18px; border: 1px solid var(--yg-gold-deep); border-radius: 999px; font-size: .74rem; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: var(--yg-gold-deep); transition: background .25s, color .25s; }
        main.yg .yg-more summary:hover { background: var(--yg-dark); border-color: var(--yg-dark); color: #e6c28f; }
        main.yg .yg-more summary:focus-visible { outline: 3px solid var(--yg-gold); outline-offset: 3px; }

        main.yg .yg-card {
            position: relative; padding: 30px 26px 26px; border: 1px solid var(--yg-line); border-radius: 22px; background: #fffaf2;
            box-shadow: 0 18px 30px -26px rgba(6, 33, 27, .55);
            transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
        }
        main.yg .yg-card::before { content: ""; position: absolute; left: 26px; right: 26px; top: 0; height: 3px; border-radius: 0 0 3px 3px; background: linear-gradient(90deg, var(--yg-gold), transparent); }
        main.yg .yg-card:hover { transform: translateY(-4px); border-color: var(--yg-gold); box-shadow: var(--yg-shadow); }
        main.yg .yg-card h3 { font-size: 1.6rem; }
        main.yg .yg-card p { font-size: 1.02rem; line-height: 1.65; }
        main.yg .yg-card small { letter-spacing: .12em; }

        /* ---------- Programme ---------- */
        main.yg .yg-dark { position: relative; padding: clamp(64px, 8vw, 116px) 0; background: radial-gradient(ellipse 60% 50% at 85% 0%, rgba(220, 184, 135, .12), transparent 70%), var(--yg-dark); }
        main.yg .yg-numbers { margin-top: clamp(34px, 4vw, 56px); padding: clamp(20px, 3vw, 30px) 0; border-block: 1px solid rgba(220, 184, 135, .25); }
        main.yg .yg-numbers li { border-left-color: rgba(220, 184, 135, .3); padding: 4px 24px; }
        main.yg .yg-numbers strong { font-size: clamp(2.6rem, 5.4vw, 4rem); font-weight: 500; }
        main.yg .yg-numbers span { font-size: .92rem; color: rgba(246, 232, 208, .78); }
        main.yg .yg-flow__side h3 { font-size: clamp(1.8rem, 3.2vw, 2.4rem); }
        main.yg .yg-flow__side p { color: rgba(246, 232, 208, .8); }
        main.yg .yg-days button { min-height: 56px; border: 1px solid rgba(220, 184, 135, .22); border-radius: 16px; background: rgba(255, 255, 255, .04); }
        main.yg .yg-days button:hover { background: rgba(220, 184, 135, .14); border-color: rgba(220, 184, 135, .5); }
        main.yg .yg-days button.is-active { background: linear-gradient(135deg, #e8c797, #cfa066); border-color: transparent; box-shadow: 0 14px 24px -16px rgba(0, 0, 0, .8); }
        main.yg .yg-day > h3 { font-family: var(--cv-serif, serif); font-size: 1.7rem; font-weight: 600; }
        main.yg .yg-times li { grid-template-columns: 108px 1fr; gap: 0; padding: 0; }
        main.yg .yg-times li + li { border-top: 0; }
        main.yg .yg-times time { padding: 18px 20px 18px 0; font-size: 1.4rem; text-align: right; }
        main.yg .yg-times li > div { position: relative; padding: 18px 0 18px 28px; border-left: 1px solid rgba(220, 184, 135, .35); }
        main.yg .yg-times li > div::before { content: ""; position: absolute; left: -5px; top: 26px; width: 9px; height: 9px; border-radius: 50%; background: var(--yg-gold); box-shadow: 0 0 0 4px rgba(220, 184, 135, .18); }
        main.yg .yg-times strong { font-size: 1.12rem; }
        main.yg .yg-times p { font-size: .98rem; line-height: 1.6; color: rgba(246, 232, 208, .75); }
        main.yg .yg-care { border-color: rgba(220, 184, 135, .3); background: rgba(255, 255, 255, .03); border-radius: 26px; }
        main.yg .yg-care h3 { font-size: 1.7rem; }
        main.yg .yg-care li { font-size: 1rem; color: rgba(246, 232, 208, .86); }

        /* ---------- Classes ---------- */
        main.yg .yg-day-card { padding: 26px; border-color: var(--yg-line); border-radius: 24px; background: #fffaf2; box-shadow: 0 18px 30px -26px rgba(6, 33, 27, .55); }
        main.yg .yg-day-card h3 { font-size: 1.6rem; padding-bottom: 8px; border-bottom: 1px solid var(--yg-line); }
        main.yg .yg-session { padding: 16px 0; }
        main.yg .yg-session + .yg-session { border-top-color: var(--yg-line); }
        main.yg .yg-session strong { font-size: 1.05rem; }
        main.yg .yg-callout { border-left: 3px solid var(--yg-gold); border-radius: 14px; background: #fffaf2; }

        /* ---------- Resort, teacher, photos ---------- */
        main.yg .yg-collage figure, main.yg .yg-teacher__photo img, main.yg .yg-slide img { box-shadow: var(--yg-shadow); }
        main.yg .yg-collage figcaption { padding: 6px 13px; background: rgba(255, 250, 242, .94); font-size: .76rem; letter-spacing: .03em; }
        main.yg .yg-checks li::before { top: 1px; display: grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: #e6f0e8; font-size: .75rem; color: var(--yg-green); }
        main.yg .yg-checks li { padding-left: 36px; }
        main.yg .yg-spot { border-radius: 30px; background: var(--yg-dark); box-shadow: var(--yg-shadow); }
        main.yg .yg-spot p { color: rgba(246, 232, 208, .82); }
        main.yg .yg-badge { background: linear-gradient(135deg, #e8c797, #cfa066); font-weight: 600; letter-spacing: .14em; }

        main.yg .yg-teacher__photo { position: relative; isolation: isolate; }
        main.yg .yg-teacher__photo::before { content: ""; position: absolute; inset: 16px -16px -16px 16px; z-index: -1; border: 1px solid var(--yg-gold); border-radius: 30px; }
        main.yg .yg-teacher__photo img { border-radius: 28px; }
        main.yg .yg-teacher__text > p.yg-role { font-size: 1.5rem; }
        main.yg .yg-tags li { padding: 8px 16px; border: 1px solid var(--yg-line); background: #fffaf2; font-size: .86rem; }
        main.yg .yg-extra { background: radial-gradient(ellipse 70% 80% at 100% 0%, rgba(220, 184, 135, .16), transparent 70%), var(--yg-dark); box-shadow: var(--yg-shadow); }
        main.yg .yg-extra p { color: rgba(246, 232, 208, .85); }

        /* ---------- Gallery ---------- */
        main.yg .yg-slide img { border-radius: 24px; transition: transform .5s ease, box-shadow .5s ease; }
        main.yg .yg-slide:hover img { transform: translateY(-4px); }
        main.yg .yg-arrow { width: 48px; height: 48px; border-color: var(--yg-gold-deep); background: transparent; color: var(--yg-gold-deep); transition: background .25s, color .25s, opacity .25s; }
        main.yg .yg-arrow:hover:not(:disabled) { background: var(--yg-dark); border-color: var(--yg-dark); color: #e6c28f; }
        main.yg .yg-slide figcaption { font-size: .92rem; }

        /* ---------- Package ---------- */
        main.yg .yg-col { padding: 30px 28px; border-color: var(--yg-line); border-radius: 26px; background: #fffaf2; box-shadow: 0 18px 30px -26px rgba(6, 33, 27, .55); }
        main.yg .yg-col--extras { background: var(--yg-sand); box-shadow: none; }
        main.yg .yg-col h3, main.yg .yg-price h3 { font-size: 1.7rem; }
        main.yg .yg-col li { font-size: 1.04rem; padding-left: 32px; }
        main.yg .yg-col li::before { top: 1px; display: grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: #e6f0e8; font-size: .75rem; }
        main.yg .yg-col--extras li::before { background: #f3e2c4; }
        main.yg .yg-price { position: relative; padding: 30px 28px; background: radial-gradient(ellipse 80% 60% at 100% 0%, rgba(220, 184, 135, .2), transparent 70%), var(--yg-dark); box-shadow: var(--yg-shadow); border: 1px solid rgba(220, 184, 135, .4); }
        main.yg .yg-price .yg-price__big { font-size: clamp(2.6rem, 4.4vw, 3.6rem); font-weight: 500; }
        main.yg .yg-strip { border: 1px solid var(--yg-line); background: var(--yg-sand); }
        main.yg .yg-strip--white { background: #fffaf2; }
        main.yg .yg-strip h3 { font-size: 1.5rem; }

        /* ---------- FAQ ---------- */
        main.yg .yg-faq__item { border-color: var(--yg-line); border-radius: 18px; background: #fffaf2; transition: border-color .25s, box-shadow .25s; }
        main.yg .yg-faq__item:hover { border-color: var(--yg-gold); }
        main.yg .yg-faq__item[open] { border-color: var(--yg-gold); background: #fff; }
        main.yg .yg-faq__item summary { min-height: 64px; padding: 18px 24px; font-size: 1.4rem; }
        main.yg .yg-faq__item p { padding: 0 24px 24px; font-size: 1.05rem; max-width: 70ch; }

        /* ---------- Quote ---------- */
        main.yg .yg-quote p { font-size: clamp(1.6rem, 3.4vw, 2.5rem); }
        main.yg .yg-quote footer { margin-top: 26px; padding-top: 22px; position: relative; }
        main.yg .yg-quote footer::before { content: ""; position: absolute; top: 0; left: 50%; width: 54px; height: 1px; background: var(--yg-gold-deep); transform: translateX(-50%); }
        main.yg .yg-quote strong { color: var(--yg-green); letter-spacing: .22em; }

        /* ---------- Closing banner ---------- */
        main.yg .yg-banner { min-height: 420px; gap: 30px; }
        main.yg .yg-banner::before { background: linear-gradient(180deg, rgba(6, 33, 27, .72), rgba(6, 33, 27, .9)); }
        main.yg .yg-banner h2 { font-size: clamp(2.4rem, 5vw, 4rem); font-weight: 500; text-shadow: 0 4px 30px rgba(0, 0, 0, .4); }

        @media (max-width: 899px) {
            main.yg .yg-teacher__photo::before { inset: 12px -10px -12px 10px; }
            main.yg .yg-hero { min-height: 0; }
            main.yg .yg-times li { grid-template-columns: 84px 1fr; }
            main.yg .yg-times time { padding-right: 14px; font-size: 1.2rem; }
        }
        @media (max-width: 560px) {
            main.yg .yg-btn { min-height: 50px; }
            main.yg .yg-times li { grid-template-columns: 72px 1fr; }
            main.yg .yg-times li > div { padding-left: 20px; }
            main.yg .yg-teacher__photo::before { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            main.yg *, main.yg *::before, main.yg *::after { transition: none !important; }
        }
    </style>

    <style>
        /* Yoga Reset: gallery, teacher section and the full-screen photo viewer */

        /* ---------- Gallery: dark band so the photos stand out ---------- */
        main.yg #gallery {
            position: relative;
            padding: clamp(64px, 8vw, 116px) 0 clamp(44px, 6vw, 76px);
            background: radial-gradient(ellipse 60% 70% at 12% 0%, rgba(220, 184, 135, .14), transparent 70%), var(--yg-dark);
            color: #f6e8d0;
            overflow: hidden;
        }
        main.yg #gallery h2 { color: #fbf1de; }
        main.yg #gallery .yg-gallery__head p { color: rgba(246, 232, 208, .8); font-size: 1.1rem; max-width: 56ch; }
        main.yg #gallery .yg-label { color: var(--yg-gold); }
        main.yg #gallery .yg-arrow { width: 52px; height: 52px; border-color: rgba(220, 184, 135, .6); background: transparent; color: var(--yg-gold); font-size: 1.7rem; }
        main.yg #gallery .yg-arrow:hover:not(:disabled) { background: var(--yg-gold); border-color: var(--yg-gold); color: var(--yg-dark); }
        main.yg #gallery .yg-arrow:disabled { opacity: .28; }

        main.yg #gallery .yg-slider { gap: 22px; margin-top: 38px; padding-bottom: 6px; cursor: grab; }
        main.yg #gallery .yg-slider.is-dragging { cursor: grabbing; scroll-snap-type: none; scroll-behavior: auto; user-select: none; }
        main.yg #gallery .yg-slide {
            position: relative;
            flex: 0 0 min(78vw, 400px);
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(220, 184, 135, .3);
            box-shadow: 0 30px 44px -28px rgba(0, 0, 0, .85);
            background: #0e3629;
        }
        main.yg #gallery .yg-slide img { width: 100%; aspect-ratio: 4 / 5; border-radius: 0; box-shadow: none; transition: transform .7s cubic-bezier(.2, .6, .2, 1); cursor: zoom-in; }
        main.yg #gallery .yg-slide:hover img { transform: scale(1.05); }
        main.yg #gallery .yg-slide figcaption {
            position: absolute; left: 0; right: 0; bottom: 0;
            margin: 0; padding: 64px 22px 20px;
            background: linear-gradient(0deg, rgba(3, 16, 13, .88), rgba(3, 16, 13, 0));
            font-family: var(--cv-serif, serif); font-size: 1.25rem; font-weight: 500; line-height: 1.25;
            color: #fbf1de; pointer-events: none;
        }
        main.yg #gallery .yg-slide::after {
            content: "";
            position: absolute; top: 14px; right: 14px;
            width: 42px; height: 42px; border-radius: 50%;
            border: 1px solid rgba(220, 184, 135, .55);
            background: rgba(6, 33, 27, .78) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23dcb887' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7'/%3E%3C/svg%3E") center / 18px no-repeat;
            opacity: 0; transform: scale(.88);
            transition: opacity .3s ease, transform .3s ease;
            pointer-events: none;
        }
        main.yg #gallery .yg-slide:hover::after, main.yg #gallery .yg-slide:focus-within::after { opacity: 1; transform: none; }
        @media (hover: none) { main.yg #gallery .yg-slide::after { opacity: .9; transform: none; } }

        main.yg .yg-progress { height: 2px; margin: 26px 0 14px; border-radius: 2px; background: rgba(220, 184, 135, .22); overflow: hidden; }
        main.yg .yg-progress span { display: block; height: 100%; border-radius: 2px; background: var(--yg-gold); transition: margin-left .15s linear, width .15s linear; }
        main.yg #gallery .yg-count { margin-top: 0; font-size: .82rem; letter-spacing: .16em; color: rgba(246, 232, 208, .75); }

        /* ---------- Teacher ---------- */
        main.yg #teacher {
            padding: clamp(72px, 9vw, 130px) 0;
            background: radial-gradient(ellipse 55% 60% at 0% 40%, rgba(220, 184, 135, .2), transparent 70%), linear-gradient(180deg, var(--yg-cream), #f3ebdd);
        }
        main.yg .yg-teacher { gap: clamp(34px, 5vw, 64px); }
        @media (min-width: 900px) {
            main.yg .yg-teacher { grid-template-columns: 400px 1fr; }
            main.yg .yg-teacher__photo { position: sticky; top: calc(var(--cv-header-h, 76px) + 28px); }
        }
        main.yg .yg-teacher__photo { position: relative; isolation: isolate; }
        main.yg .yg-teacher__photo::before { inset: 18px -18px -18px 18px; border-color: rgba(164, 122, 68, .7); border-radius: 32px; }
        main.yg .yg-teacher__photo img { aspect-ratio: 4 / 5; border-radius: 28px; box-shadow: 0 34px 50px -32px rgba(6, 33, 27, .75); }
        main.yg .yg-teacher__photo figcaption {
            position: absolute; left: 16px; bottom: 16px; margin: 0;
            padding: 8px 16px; border-radius: 999px;
            background: rgba(6, 33, 27, .86); border: 1px solid rgba(220, 184, 135, .5);
            font-size: .72rem; font-weight: 500; letter-spacing: .16em; color: #e6c28f;
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        }
        main.yg .yg-teacher__text { gap: 14px; }
        main.yg .yg-teacher__text h2 { font-size: clamp(2.6rem, 5.2vw, 4.2rem); line-height: 1; }
        main.yg .yg-teacher__text > p.yg-role {
            display: flex; align-items: center; gap: 14px;
            font-size: clamp(1.4rem, 2.4vw, 1.8rem); color: var(--yg-gold-deep);
        }
        main.yg .yg-teacher__text > p.yg-role::before { content: ""; flex: none; width: 34px; height: 1px; background: var(--yg-gold-deep); }
        main.yg .yg-teacher__text > p { font-size: 1.12rem; }
        main.yg .yg-teacher__text h3 {
            display: flex; align-items: center; gap: 16px; width: 100%;
            margin-top: 22px; font-family: var(--cv-sans, sans-serif);
            font-size: .76rem; font-weight: 500; letter-spacing: .24em; text-transform: uppercase; color: var(--yg-green);
        }
        main.yg .yg-teacher__text h3::after { content: ""; flex: 1; height: 1px; background: var(--yg-line); }
        main.yg .yg-tags { gap: 10px; }
        main.yg .yg-tags li {
            display: inline-flex; align-items: center; gap: 9px;
            padding: 9px 18px; border: 1px solid var(--yg-line); border-radius: 999px;
            background: #fffaf2; font-size: .9rem; font-weight: 500; color: var(--yg-ink);
            transition: border-color .25s, background-color .25s, transform .25s;
        }
        main.yg .yg-tags li::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--yg-gold-deep); }
        main.yg .yg-tags li:hover { border-color: var(--yg-gold); background: #fff; transform: translateY(-2px); }
        main.yg .yg-extra {
            position: relative; margin-top: 30px; padding: clamp(26px, 3.4vw, 40px); gap: 14px;
            border: 1px solid rgba(220, 184, 135, .4); border-left: 4px solid var(--yg-gold);
            border-radius: 26px;
        }
        main.yg .yg-extra h3 { font-family: var(--cv-serif, serif); font-size: clamp(1.7rem, 3vw, 2.2rem); letter-spacing: 0; text-transform: none; display: block; margin: 0; color: #fbf1de; }
        main.yg .yg-extra h3::after { content: none; }
        main.yg .yg-extra p { font-size: 1.05rem; }

        /* ---------- Full-screen photo viewer ---------- */
        .cv-photo-viewer {
            padding: 88px 108px 104px;
            background: rgba(3, 16, 13, .985);
            -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
            overflow: hidden;
            animation: cvv-in .25s ease;
        }
        @keyframes cvv-in { from { opacity: 0; } to { opacity: 1; } }
        .cv-photo-viewer .cv-photo-viewer__image {
            width: 100% !important; height: 100% !important;
            max-width: none !important; max-height: none !important;
            object-fit: contain; border-radius: 12px;
            box-shadow: 0 40px 90px -30px rgba(0, 0, 0, .9);
            cursor: zoom-in;
            transition: transform .35s ease, opacity .3s ease;
        }
        .cv-photo-viewer .cv-photo-viewer__image.is-loading { opacity: 0; }
        .cv-photo-viewer .cv-photo-viewer__image.is-zoomed { transform: scale(1.9); cursor: zoom-out; }

        .cv-photo-viewer__toolbar {
            top: 0; left: 0; right: 0; z-index: 3;
            padding: 18px 24px 40px;
            background: linear-gradient(180deg, rgba(3, 16, 13, .95), rgba(3, 16, 13, 0));
            pointer-events: none;
        }
        .cv-photo-viewer__toolbar > * { pointer-events: auto; }
        .cv-photo-viewer__count {
            padding: 9px 18px; border: 1px solid rgba(220, 184, 135, .4); border-radius: 999px;
            background: rgba(220, 184, 135, .1);
            font: 500 .82rem var(--cv-sans, system-ui, sans-serif); letter-spacing: .16em; color: #e6c28f;
        }
        .cv-photo-viewer button {
            min-width: 48px; min-height: 48px;
            border: 1px solid rgba(220, 184, 135, .55); border-radius: 999px;
            background: rgba(6, 33, 27, .88); color: #f3e3c4;
            font-family: var(--cv-sans, system-ui, sans-serif);
            transition: background-color .25s, color .25s, border-color .25s, transform .25s;
        }
        .cv-photo-viewer button:hover, .cv-photo-viewer button:focus-visible { background: linear-gradient(135deg, #e8c797, #cfa066); border-color: transparent; color: #06211b; }
        .cv-photo-viewer button:focus-visible { outline: 2px solid #fbf1de; outline-offset: 3px; }
        .cv-photo-viewer [data-cv-zoom] { padding: 0 22px; font-size: .74rem; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; }
        .cv-photo-viewer [data-cv-close] { font-size: 30px; }
        .cv-photo-viewer__prev, .cv-photo-viewer__next {
            z-index: 3; width: 58px; height: 58px; padding: 0 0 4px;
            font-size: 32px !important; line-height: 1;
            box-shadow: 0 14px 30px -12px rgba(0, 0, 0, .8);
        }
        .cv-photo-viewer__prev { left: 28px; }
        .cv-photo-viewer__next { right: 28px; }
        .cv-photo-viewer__prev:hover { transform: translateY(-50%) translateX(-3px); }
        .cv-photo-viewer__next:hover { transform: translateY(-50%) translateX(3px); }
        .cv-photo-viewer__caption {
            position: absolute; left: 50%; bottom: 50px; z-index: 2;
            width: max-content; max-width: min(84vw, 760px); margin: 0; transform: translateX(-50%);
            font: 500 1.3rem/1.35 var(--cv-serif, Georgia, serif); text-align: center; color: #fbf1de;
            text-shadow: 0 2px 16px rgba(0, 0, 0, .7);
        }
        .cv-photo-viewer__caption[hidden] { display: none; }
        .cv-photo-viewer__hint {
            position: absolute; left: 50%; bottom: 20px; z-index: 2; margin: 0; transform: translateX(-50%);
            font: 400 .7rem var(--cv-sans, system-ui, sans-serif); letter-spacing: .18em; text-transform: uppercase; white-space: nowrap;
            color: rgba(246, 232, 208, .5);
        }
        @media (hover: none) { .cv-photo-viewer__hint { display: none; } }

        @media (max-width: 899px) {
            main.yg .yg-teacher__photo { max-width: 460px; }
            main.yg .yg-teacher__photo::before { inset: 12px -10px -12px 10px; }
        }
        @media (max-width: 600px) {
            .cv-photo-viewer { padding: 78px 10px 150px; }
            .cv-photo-viewer__prev, .cv-photo-viewer__next { top: auto; bottom: 24px; width: 54px; height: 54px; transform: none; }
            .cv-photo-viewer__prev { left: calc(50% - 74px); right: auto; }
            .cv-photo-viewer__next { right: calc(50% - 74px); left: auto; }
            .cv-photo-viewer__prev:hover, .cv-photo-viewer__next:hover { transform: none; }
            .cv-photo-viewer__caption { bottom: 96px; font-size: 1.1rem; }
            .cv-photo-viewer__toolbar { padding: 14px 14px 36px; }
            main.yg .yg-teacher__photo::before { display: none; }
            main.yg #gallery .yg-slide { flex-basis: 78vw; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cv-photo-viewer, .cv-photo-viewer * { animation: none !important; transition: none !important; }
            main.yg #gallery .yg-slide img, main.yg #gallery .yg-slide::after { transition: none !important; }
        }
    </style>
</head>
<body>
    @include('layouts.header')
    @php
        $img = fn (?string $path, string $fallback = 'images/main.jpg') => asset($path && file_exists(public_path($path)) ? $path : $fallback);
        $href = fn (?string $url) => \Illuminate\Support\Str::startsWith($url ?? '', ['http', '#', 'mailto:', 'tel:']) ? $url : url($url ?: '/#contact');
        $logo = \App\Models\HomeContent::siteLogo();
    @endphp

    <main class="cv-page yg">

        {{-- Top banner --}}
        <section class="yg-hero" style="background-image:url('{{ $img($page['hero']['image']) }}')">
            <div class="yg-wrap">
                <div class="yg-hero__body">
                    <span class="yg-eyebrow">{{ $page['hero']['eyebrow'] }}</span>

                    @if(count($page['chips']))
                        <ul class="yg-chips" role="list">
                            @foreach($page['chips'] as $chip)
                                <li class="yg-chip yg-chip--{{ $chip['style'] }}"><span class="yg-dot" aria-hidden="true"></span>{{ $chip['text'] }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <h1>{{ $page['hero']['title'] }}</h1>
                    <p class="yg-hero__lead">{{ $page['hero']['text'] }}</p>

                    <div class="yg-actions">
                        @if($page['hero']['button_text'])
                            <a href="{{ $href($page['hero']['button_url']) }}" class="yg-btn yg-btn--gold">{{ $page['hero']['button_text'] }} <span aria-hidden="true">→</span></a>
                        @endif
                        @if($page['hero_link'])
                            <a href="{{ $href($page['hero_link']['url']) }}" class="yg-btn yg-btn--outline">{{ $page['hero_link']['text'] }}</a>
                        @endif
                    </div>

                    @if(count($page['facts']))
                        <ul class="yg-facts" role="list">
                            @foreach($page['facts'] as $fact)
                                <li>{{ $fact }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        {{-- The blocks below follow the order and visibility set in the admin (Yoga Reset > Page layout) --}}
        @foreach($page['layout']['flow'] as $block)
            @include('yoga.blocks.'.$block)
        @endforeach
    </main>

    <script>
        // Day tabs and the photo slider. Without JavaScript every day is listed and the photos scroll by hand.
        (function () {
            var root = document.querySelector('.yg');
            if (!root) { return; }
            root.classList.add('yg-js');

            // Day tabs
            root.querySelectorAll('[data-yg-tabs]').forEach(function (box) {
                var tabs = [].slice.call(box.querySelectorAll('[data-yg-tab]'));
                var panels = [].slice.call(box.querySelectorAll('[data-yg-panel]'));
                if (!tabs.length || tabs.length !== panels.length) { return; }

                function show(index, focus) {
                    tabs.forEach(function (tab, i) {
                        var on = i === index;
                        tab.classList.toggle('is-active', on);
                        tab.setAttribute('aria-selected', on ? 'true' : 'false');
                        tab.tabIndex = on ? 0 : -1;
                        panels[i].hidden = !on;
                        if (on && focus) { tab.focus(); }
                    });
                }

                tabs.forEach(function (tab, i) {
                    tab.addEventListener('click', function () { show(i, false); });
                    tab.addEventListener('keydown', function (e) {
                        var next = null;
                        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { next = (i + 1) % tabs.length; }
                        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { next = (i - 1 + tabs.length) % tabs.length; }
                        if (e.key === 'Home') { next = 0; }
                        if (e.key === 'End') { next = tabs.length - 1; }
                        if (next !== null) { e.preventDefault(); show(next, true); }
                    });
                });

                box.querySelector('[data-yg-tablist]').hidden = false;
                show(0, false);
            });

            // Photo slider
            root.querySelectorAll('[data-yg-slider]').forEach(function (slider) {
                var slides = [].slice.call(slider.children);
                var wrap = slider.closest('section');
                var count = wrap.querySelector('[data-yg-count]');
                var prev = wrap.querySelector('[data-yg-prev]');
                var next = wrap.querySelector('[data-yg-next]');

                function step() { return slides[0] ? slides[0].getBoundingClientRect().width + 16 : 300; }
                function current() { return Math.min(slides.length, Math.round(slider.scrollLeft / step()) + 1); }
                function update() {
                    if (count) { count.textContent = current() + ' / ' + slides.length; }
                    if (prev) { prev.disabled = slider.scrollLeft <= 4; }
                    if (next) { next.disabled = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 4; }
                }

                if (prev) { prev.addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); }); }
                if (next) { next.addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); }); }
                slider.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
                window.addEventListener('resize', update);
                update();
            });
        })();
    </script>

    <script>
        // Gallery: drag with the mouse, progress bar. Photo viewer: caption, hint, smooth fade, move-to-pan when zoomed.
        (function () {
            document.querySelectorAll('[data-yg-slider]').forEach(function (slider) {
                var wrap = slider.closest('section');
                var count = wrap && wrap.querySelector('[data-yg-count]');

                if (count) {
                    var bar = document.createElement('div');
                    bar.className = 'yg-progress';
                    bar.setAttribute('aria-hidden', 'true');
                    bar.innerHTML = '<span></span>';
                    count.parentNode.insertBefore(bar, count);
                    var fill = bar.firstChild;
                    var update = function () {
                        var max = slider.scrollWidth - slider.clientWidth;
                        var share = slider.scrollWidth ? slider.clientWidth / slider.scrollWidth : 1;
                        var pos = max > 0 ? slider.scrollLeft / max : 0;
                        fill.style.width = (share * 100) + '%';
                        fill.style.marginLeft = (pos * (1 - share) * 100) + '%';
                    };
                    slider.addEventListener('scroll', function () { window.requestAnimationFrame(update); }, { passive: true });
                    window.addEventListener('resize', update);
                    update();
                }

                var down = false, moved = false, startX = 0, startLeft = 0;
                slider.addEventListener('pointerdown', function (e) {
                    if (e.pointerType !== 'mouse' || e.button !== 0) { return; }
                    down = true; moved = false; startX = e.clientX; startLeft = slider.scrollLeft;
                });
                window.addEventListener('pointermove', function (e) {
                    if (!down) { return; }
                    var dx = e.clientX - startX;
                    if (Math.abs(dx) > 5) { moved = true; slider.classList.add('is-dragging'); }
                    if (moved) { slider.scrollLeft = startLeft - dx; }
                });
                window.addEventListener('pointerup', function () {
                    if (!down) { return; }
                    down = false;
                    slider.classList.remove('is-dragging');
                });
                slider.addEventListener('click', function (e) {
                    if (moved) { e.stopPropagation(); e.preventDefault(); moved = false; }
                }, true);
                slider.addEventListener('dragstart', function (e) { e.preventDefault(); });
            });

            function enhance(viewer) {
                if (viewer.__cvv) { return; }
                viewer.__cvv = true;
                var img = viewer.querySelector('.cv-photo-viewer__image');
                if (!img) { return; }

                var caption = document.createElement('p');
                caption.className = 'cv-photo-viewer__caption';
                caption.hidden = true;
                var hint = document.createElement('p');
                hint.className = 'cv-photo-viewer__hint';
                hint.setAttribute('aria-hidden', 'true');
                hint.textContent = 'Use the arrow keys to browse · Esc to close';
                viewer.appendChild(caption);
                viewer.appendChild(hint);

                var last = null;
                var done = function () { img.classList.remove('is-loading'); };
                img.addEventListener('load', done);
                img.addEventListener('error', done);

                new MutationObserver(function () {
                    var src = img.getAttribute('src');
                    if (src && src !== last) {
                        img.classList.add('is-loading');
                        window.setTimeout(done, 700);
                    }
                    last = src;
                    var alt = (img.getAttribute('alt') || '').trim();
                    caption.textContent = /^resort photo$/i.test(alt) ? '' : alt;
                    caption.hidden = !caption.textContent;
                }).observe(img, { attributes: true, attributeFilter: ['src', 'alt'] });

                viewer.addEventListener('mousemove', function (e) {
                    if (!img.classList.contains('is-zoomed')) { return; }
                    var box = viewer.getBoundingClientRect();
                    img.style.transformOrigin = ((e.clientX - box.left) / box.width * 100) + '% ' + ((e.clientY - box.top) / box.height * 100) + '%';
                });
            }

            function find() {
                var viewer = document.querySelector('.cv-photo-viewer');
                if (viewer) { enhance(viewer); }
            }
            find();
            new MutationObserver(find).observe(document.body, { childList: true });
            document.addEventListener('DOMContentLoaded', find);
        })();
    </script>

    @include('layouts.footer')
</body>
</html>
