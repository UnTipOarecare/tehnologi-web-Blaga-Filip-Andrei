(function(){
  // CONFIGURABILE
  const DEFAULTS = {
    shapeCount: 80,      // numarul total de forme
    minSize: 22,         // dimensiune minima (px)
    maxSize: 110,        // dimensiune maxima (px)
    speedMin: 20,        // viteza minima px/sec
    speedMax: 120,       // viteza maxima px/sec
    driftY: 30,          // magnitudine drift vertical
    opacityMin: 0.18,
    opacityMax: 0.8,
    devicePixelRatioLimit: 2 // max dpr for canvas scaling
  };

  const THEMES = {
    vibrant: ['#6C5CE7','#00B894','#FF6B6B','#FFD166','#4D96FF'],
    sunset: ['#FF6B6B','#FF9F43','#FFD166','#FF7A59','#D34D4D'],
    ocean: ['#00B894','#4D96FF','#00A6FB','#7DE2D1','#2E86AB']
  };

  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // helper: random
  function rand(min,max){ return Math.random()*(max-min)+min; }
  function randInt(min,max){ return Math.floor(rand(min,max+1)); }
  function choice(arr){ return arr[Math.floor(Math.random()*arr.length)]; }

  // shapes drawing helpers (draw on given ctx, at x,y with size)
  const ShapeDraw = {
    square(ctx,x,y,size){
      ctx.beginPath();
      ctx.rect(x - size/2, y - size/2, size, size);
      ctx.closePath();
      ctx.fill();
    },
    triangle(ctx,x,y,size){
      const h = size * Math.sqrt(3)/2;
      ctx.beginPath();
      ctx.moveTo(x, y - (2/3)*h);
      ctx.lineTo(x - size/2, y + (1/3)*h);
      ctx.lineTo(x + size/2, y + (1/3)*h);
      ctx.closePath();
      ctx.fill();
    },
    pentagon(ctx,x,y,size){
      const r = size/2;
      ctx.beginPath();
      for (let i=0;i<5;i++){
        const a = (Math.PI*2)*(i/5) - Math.PI/2;
        const px = x + Math.cos(a)*r;
        const py = y + Math.sin(a)*r;
        if(i===0) ctx.moveTo(px,py); else ctx.lineTo(px,py);
      }
      ctx.closePath(); ctx.fill();
    },
    hexagon(ctx,x,y,size){
      const r = size/2;
      ctx.beginPath();
      for (let i=0;i<6;i++){
        const a = (Math.PI*2)*(i/6) - Math.PI/2;
        const px = x + Math.cos(a)*r;
        const py = y + Math.sin(a)*r;
        if(i===0) ctx.moveTo(px,py); else ctx.lineTo(px,py);
      }
      ctx.closePath(); ctx.fill();
    },
    star(ctx,x,y,size,points=5){
      const outer = size/2;
      const inner = outer * 0.45;
      ctx.beginPath();
      for(let i=0;i<points*2;i++){
        const r = (i%2===0)?outer:inner;
        const a = (Math.PI*2)*(i/(points*2)) - Math.PI/2;
        const px = x + Math.cos(a)*r;
        const py = y + Math.sin(a)*r;
        if(i===0) ctx.moveTo(px,py); else ctx.lineTo(px,py);
      }
      ctx.closePath(); ctx.fill();
    }
  };

  // map shape names to draw functions
  const SHAPES = ['square','triangle','star','pentagon','hexagon'];

  // create canvas inside each .bg-geom
  function initAll(){
    const containers = document.querySelectorAll('.bg-geom');
    containers.forEach(initContainer);
  }

  function initContainer(el){
    // remove any old canvas (in case of double-init)
    const old = el.querySelector('canvas');
    if(old) old.remove();

    const canvas = document.createElement('canvas');
    canvas.style.position = 'absolute';
    canvas.style.inset = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.display = 'block';
    canvas.style.zIndex = -1;
    canvas.style.pointerEvents = 'none';

    el.appendChild(canvas);
    const ctx = canvas.getContext('2d', { alpha: true });

    // pixel ratio handling (cap for performance)
    const dpr = Math.min(window.devicePixelRatio || 1, DEFAULTS.devicePixelRatioLimit);

    let width = 0, height = 0;

    // generate shapes
    let shapes = [];

    function resize(){
      const rect = el.getBoundingClientRect();
      width = Math.max(300, Math.round(rect.width));
      height = Math.max(200, Math.round(rect.height));
      canvas.width = Math.round(width * dpr);
      canvas.height = Math.round(height * dpr);
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(dpr,0,0,dpr,0,0);
      createShapes(rect);
      draw(); // draw static frame
    }

    function createShapes(rect){
      shapes = [];
      const themeName = el.getAttribute('data-theme') || 'vibrant';
      const palette = THEMES[themeName] || THEMES.vibrant;

      // density adjustment for small screens
      const isSmall = window.innerWidth < 600 || /Mobi|Android/i.test(navigator.userAgent);
      const count = isSmall ? Math.max(6, Math.floor(DEFAULTS.shapeCount / 3)) : DEFAULTS.shapeCount;

      for(let i=0;i<count;i++){
        const size = rand(DEFAULTS.minSize, DEFAULTS.maxSize);
        // horizontal start position: random left of canvas (to create natural flow)
        const x = rand(-width*0.5, width);
        const y = rand(0, height);
        const speed = rand(DEFAULTS.speedMin, DEFAULTS.speedMax); // px/sec
        const vy = rand(-DEFAULTS.driftY, DEFAULTS.driftY) * 0.15; // small vertical drift
        const opacity = rand(DEFAULTS.opacityMin, DEFAULTS.opacityMax);
        const color = choice(palette);
        const shape = choice(SHAPES);
        const rotate = rand(0, Math.PI*2);
        const rotSpeed = rand(-0.6,0.6); // radians/sec
        shapes.push({ x, y, size, speed, vy, opacity, color, shape, rotate, rotSpeed });
      }
    }

    // draw frame
    function draw(){
      ctx.clearRect(0,0,width,height);

      // optional: subtle gradient background tint (kept transparent so content shows)
      // ctx.fillStyle = 'rgba(255,255,255,0.02)';
      // ctx.fillRect(0,0,width,height);

      // draw each shape: composite operation 'lighter' or 'screen' gives soft glow
      ctx.save();
      for (let i=0;i<shapes.length;i++){
        const s = shapes[i];
        ctx.save();
        ctx.globalAlpha = s.opacity;
        ctx.translate(s.x, s.y);
        ctx.rotate(s.rotate);
        ctx.fillStyle = s.color;
        // slight shadow for depth
        ctx.shadowColor = hexToRgba(s.color, 0.12);
        ctx.shadowBlur = Math.max(4, s.size * 0.08);
        // draw centered at 0,0
        const fn = ShapeDraw[s.shape] || ShapeDraw.square;
        fn(ctx, 0, 0, s.size);
        ctx.restore();
      }
      ctx.restore();
    }

    // animation loop: move shapes left->right (wrap around)
    let rafId = null;
    let last = performance.now();

    function step(now){
      const dt = Math.min(40, now - last) / 1000; // seconds
      last = now;
      // update positions
      shapes.forEach(s => {
        s.x += s.speed * dt;          // left->right
        s.y += s.vy * dt + Math.sin(now*0.0005 + s.size)*dt*8; // gentle vertical wobble
        s.rotate += s.rotSpeed * dt;
        // wrap: when fully off right, send to left
        if (s.x - s.size > width) {
          s.x = -s.size - rand(20, width*0.3);
          s.y = rand(0, height);
          s.size = rand(DEFAULTS.minSize, DEFAULTS.maxSize);
          s.speed = rand(DEFAULTS.speedMin, DEFAULTS.speedMax);
          s.opacity = rand(DEFAULTS.opacityMin, DEFAULTS.opacityMax);
          s.color = choice(THEMES[el.getAttribute('data-theme')] || THEMES.vibrant);
        }
        // clamp y
        if (s.y < -s.size) s.y = height + s.size;
        if (s.y > height + s.size) s.y = -s.size;
      });

      draw();
      rafId = requestAnimationFrame(step);
    }

    // start/stop
    function start(){
      if (prefersReduced) return; // do not animate for reduced motion
      cancelAnimationFrame(rafId);
      last = performance.now();
      rafId = requestAnimationFrame(step);
    }
    function stop(){
      cancelAnimationFrame(rafId);
      rafId = null;
    }

    // initial setup
    resize();
    if (!prefersReduced) start();

    // responsiveness
    const onResize = throttle(() => {
      resize();
      if (!prefersReduced && !rafId) start();
    }, 150);
    window.addEventListener('resize', onResize);

    // expose instance for debugging if needed
    const instance = { destroy(){ stop(); window.removeEventListener('resize', onResize); canvas.remove(); } };
    // store on element
    el.__bgGeomInstance = instance;
  }

  // utilities
  function hexToRgba(hex, a){
    if(!hex) return `rgba(0,0,0,${a})`;
    hex = hex.replace('#','');
    if (hex.length === 3) hex = hex.split('').map(c=>c+c).join('');
    const r = parseInt(hex.substr(0,2),16);
    const g = parseInt(hex.substr(2,2),16);
    const b = parseInt(hex.substr(4,2),16);
    return `rgba(${r},${g},${b},${a})`;
  }
  function throttle(fn, wait){
    let t=0, scheduled=null;
    return function(...args){
      const now = Date.now();
      if (now - t > wait){
        t = now; fn.apply(this,args);
      } else {
        clearTimeout(scheduled);
        scheduled = setTimeout(()=>{ t = Date.now(); fn.apply(this,args); }, wait - (now-t));
      }
    };
  }

  // init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

})();
