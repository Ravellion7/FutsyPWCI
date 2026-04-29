<main class="flex-1 text-center flex flex-col px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-6">
        <h1 class="text-white font-extrabold tracking-wide text-3xl sm:text-4xl md:text-5xl lg:text-6xl mt-2 sm:mt-4 mb-6 sm:mb-8 md:mb-10">EXPANDE TU COLECCION</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5 md:gap-8 lg:gap-10 items-end justify-items-center mb-8 md:mb-16">
            <div class="w-[140px] h-[190px] sm:w-[170px] sm:h-[230px] md:w-[220px] md:h-[300px] lg:w-[300px] lg:h-[400px] rotate-[-2deg] md:rotate-[-10deg] translate-y-0 md:translate-y-12">
                <img class="w-full h-full rounded-2xl shadow-xl object-cover hover:shadow-[0_25px_50px_-12px_rgba(136,19,55,0.6)] hover:scale-105 md:hover:scale-110 hover:-translate-y-2 transition-all duration-300 cursor-pointer border border-white/20" src="/img/QTAR.jpeg" alt="QATAR">
            </div>
            <div class="w-[140px] h-[190px] sm:w-[170px] sm:h-[230px] md:w-[220px] md:h-[300px] lg:w-[300px] lg:h-[400px] rotate-[2deg] md:rotate-[-3deg] translate-y-0 md:translate-y-2">
                <img class="w-full h-full rounded-2xl shadow-xl object-cover hover:shadow-[0_25px_50px_-12px_rgba(136,19,55,0.6)] hover:scale-105 md:hover:scale-110 hover:-translate-y-2 transition-all duration-300 cursor-pointer border border-white/20" src="/img/Suiza.jpeg" alt="Suiza">
            </div>
            <div class="w-[140px] h-[190px] sm:w-[170px] sm:h-[230px] md:w-[220px] md:h-[300px] lg:w-[300px] lg:h-[400px] rotate-[-2deg] md:rotate-[3deg] translate-y-0 md:translate-y-2">
                <img class="w-full h-full rounded-2xl shadow-xl object-cover hover:shadow-[0_25px_50px_-12px_rgba(56,189,248,0.6)] hover:scale-105 md:hover:scale-110 hover:-translate-y-2 transition-all duration-300 cursor-pointer border border-white/20" src="/img/Uruguay.jpeg" alt="Uruguay">
            </div>
            <div class="w-[140px] h-[190px] sm:w-[170px] sm:h-[230px] md:w-[220px] md:h-[300px] lg:w-[300px] lg:h-[400px] rotate-[2deg] md:rotate-[10deg] translate-y-0 md:translate-y-12">
                <img class="w-full h-full rounded-2xl shadow-xl object-cover hover:shadow-[0_25px_50px_-12px_rgba(254,249,195,0.6)] hover:scale-105 md:hover:scale-110 hover:-translate-y-2 transition-all duration-300 cursor-pointer border border-white/20" src="/img/Brasil.jpeg" alt="Brasil">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-6 md:gap-20 lg:gap-40 mb-4 sm:mb-8">
            <button id="abrirTodos" class="rounded-full bg-[#BF7D24] px-4 sm:px-5 py-2 text-white text-sm sm:text-base hover:bg-[#CE8F3A] shadow-md hover:shadow-lg transition">Abrir todos</button>
            <button id="abrirUno" class="rounded-full bg-[#BF7D24] px-4 sm:px-5 py-2 text-white text-sm sm:text-base hover:bg-[#CE8F3A] shadow-md hover:shadow-lg transition">Abrir uno</button>
        </div>

        <div class="mx-auto w-full max-w-xl text-center">
            <p class="text-white text-sm sm:text-base font-semibold">Packs disponibles: <span id="packsBalanceValue">-</span></p>
            <p id="marketStatus" class="mt-2 text-xs sm:text-sm text-white/90"></p>
        </div>

        <div id="packRevealModal" style="display:none; position:fixed; inset:0; z-index:9999;">
            <div id="packRevealBackdrop" style="position:absolute; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(7px);"></div>
            <div style="position:relative; min-height:100%; display:flex; align-items:center; justify-content:center; padding:16px;">
                <div style="width:min(92vw,560px); background:#0B676B; border-radius:18px; box-shadow:0 24px 60px rgba(0,0,0,0.38); padding:20px; text-align:center; color:#fff;">
                    <div id="revealLoadingState" style="display:none;">
                        
                        <div style="margin:0 auto; width:min(72vw,230px); height:min(72vw,230px); border-radius:20px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.07); border:2px solid rgba(255,255,255,0.25);">
                            <img src="/img/pack2.png" alt="Abriendo pack" style="width:78%; height:78%; object-fit:contain; display:block; animation: pack-spin 1.2s linear infinite;">
                        </div>
                        <p style="margin-top:12px; font-size:13px; opacity:0.9;">Preparando tus cartas...</p>
                    </div>

                    <div id="revealCardsState" style="display:none;">
                        <p id="revealCounter" style="font-weight:700; font-size:14px; margin-bottom:10px;">Carta 1 de 5</p>
                        <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
                            <button id="revealPrevCardBtn" aria-label="Carta anterior" class="rounded-full bg-white/20 text-white h-10 w-10 text-lg font-bold hover:bg-white/30 transition">&larr;</button>
                            <div id="revealCardFrame" style="margin:0 auto; width:min(72vw,280px); height:min(100vw,390px); border-radius:16px; overflow:hidden; background:#111827; border:2px solid rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; transition:transform 0.35s ease, opacity 0.35s ease;">
                                <img id="revealCardImage" src="/img/image3.png" alt="Card reveal" style="width:100%; height:100%; object-fit:cover; display:block;">
                            </div>
                            <button id="revealNextCardBtn" aria-label="Siguiente carta" class="rounded-full bg-white/20 text-white h-10 w-10 text-lg font-bold hover:bg-white/30 transition">&rarr;</button>
                        </div>
                        <p id="revealCardName" style="margin-top:12px; font-weight:700; font-size:18px;">-</p>
                        <p id="revealCardMeta" style="margin-top:4px; font-size:14px; opacity:0.95;">-</p>

                        <div style="margin-top:16px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap;">
                            <button id="acceptCardsBtn" class="rounded-full bg-[#A4C351] px-4 py-2 text-sm font-semibold text-[#15321A] hover:opacity-90 transition">Aceptar cartas</button>
                            <button id="openAnotherPackBtn" class="rounded-full bg-[#BF7D24] px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition">Abrir otro pack</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @keyframes pack-spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    </main>