document.addEventListener(`DOMContentLoaded`,()=>{let e=document.querySelectorAll(`[data-video-open]`);if(!e.length)return;let t=`hd1080`,n=null,r=null,i=null,a=!1,o=[],s=document.querySelector(`.hero .primary-button`),c=s?.getAttribute(`href`)||`#contact`,l=s?.textContent.replace(/\s+/g,` `).trim()||`Book your stay`,u=document.querySelector(`.hero .eyebrow`)?.textContent.trim()||`Casa Verde Cliff Resort & Spa`,d=e=>e.replace(/[&<>"]/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`})[e]),f=()=>{n=document.createElement(`dialog`),n.className=`cv-video`,n.setAttribute(`aria-label`,`Casa Verde film`),n.innerHTML=`
            <div class="cv-video__panel">
                <header class="cv-video__head">
                    <div class="cv-video__titles">
                        <span class="cv-video__eyebrow">${d(u)}</span>
                        <span class="cv-video__title">The Casa Verde Film</span>
                    </div>
                    <button type="button" class="cv-video__close" aria-label="Close video">
                        <span class="cv-video__esc" aria-hidden="true">Esc</span>
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
                            <path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>

                <div class="cv-video__frame">
                    <span class="cv-video__corner cv-video__corner--tl" aria-hidden="true"></span>
                    <span class="cv-video__corner cv-video__corner--br" aria-hidden="true"></span>
                    <div class="cv-video__stage">
                        <span class="cv-video__loader" aria-hidden="true"></span>
                    </div>
                </div>

                <footer class="cv-video__foot">
                    <a class="cv-video__book" href="${d(c)}">${d(l)}</a>
                    <a class="cv-video__fallback" target="_blank" rel="noopener noreferrer">Watch on YouTube</a>
                </footer>
            </div>
        `,document.body.appendChild(n),r=n.querySelector(`.cv-video__stage`),n.querySelector(`.cv-video__frame`),n.querySelector(`.cv-video__close`).addEventListener(`click`,v),n.querySelector(`.cv-video__book`).addEventListener(`click`,()=>v()),n.addEventListener(`click`,e=>{e.target.closest(`.cv-video__panel`)||v()}),n.addEventListener(`cancel`,e=>{e.preventDefault(),v()})},p=(e,t,n=[])=>{try{e.contentWindow?.postMessage(JSON.stringify({event:`command`,func:t,args:n}),`*`)}catch{}},m=e=>{p(e,`setPlaybackQualityRange`,[t,t]),p(e,`setPlaybackQuality`,[t])},h=e=>{e.contentWindow?.postMessage(JSON.stringify({event:`listening`,id:1,channel:`widget`}),`*`),[300,1200,2500,4500].forEach(t=>o.push(window.setTimeout(()=>m(e),t)))};window.addEventListener(`message`,e=>{let t=``;try{t=new URL(e.origin).hostname}catch{return}if(!/(^|\.)youtube(-nocookie)?\.com$/.test(t))return;let n=r?.querySelector(`iframe`);if(!n||e.source!==n.contentWindow)return;let i=e.data;if(typeof i==`string`)try{i=JSON.parse(i)}catch{return}let a=i?.info?.playerState;(i?.event===`onReady`||a===1)&&m(n)});let g=()=>{o.forEach(clearTimeout),o=[]},_=e=>{let o=e.dataset.videoId;if(!o||a)return;n||f(),i=e,g(),r.querySelector(`iframe`)?.remove(),r.classList.remove(`is-ready`),r.style.backgroundImage=`url('https://i.ytimg.com/vi/${encodeURIComponent(o)}/maxresdefault.jpg'), url('https://i.ytimg.com/vi/${encodeURIComponent(o)}/hqdefault.jpg')`;let s=document.createElement(`iframe`),c=new URLSearchParams({autoplay:`1`,rel:`0`,playsinline:`1`,modestbranding:`1`,color:`white`,iv_load_policy:`3`,enablejsapi:`1`,vq:t,origin:window.location.origin});s.src=`https://www.youtube-nocookie.com/embed/${encodeURIComponent(o)}?${c}`,s.title=`Casa Verde Cliff Resort & Spa film`,s.allow=`autoplay; encrypted-media; picture-in-picture; fullscreen`,s.allowFullscreen=!0,s.referrerPolicy=`strict-origin-when-cross-origin`,s.addEventListener(`load`,()=>{r.classList.add(`is-ready`),h(s)}),r.appendChild(s),n.querySelector(`.cv-video__fallback`).href=`https://www.youtube.com/watch?v=${encodeURIComponent(o)}`;let l=e.getBoundingClientRect();n.style.setProperty(`--cv-from-x`,`${l.left+l.width/2-window.innerWidth/2}px`),n.style.setProperty(`--cv-from-y`,`${l.top+l.height/2-window.innerHeight/2}px`),n.showModal(),document.documentElement.classList.add(`cv-video-lock`),requestAnimationFrame(()=>requestAnimationFrame(()=>n.classList.add(`is-open`))),n.querySelector(`.cv-video__close`).focus({preventScroll:!0})};function v(){n&&n.open&&!a&&(a=!0,g(),n.classList.remove(`is-open`),window.setTimeout(()=>{n.close(),r.querySelector(`iframe`)?.remove(),document.documentElement.classList.remove(`cv-video-lock`),a=!1,i?.focus({preventScroll:!0})},window.matchMedia(`(prefers-reduced-motion: reduce)`).matches?0:340))}e.forEach(e=>e.addEventListener(`click`,()=>_(e)))});