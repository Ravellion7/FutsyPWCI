<main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-16 pt-4 sm:pt-8 pb-6 flex flex-col gap-6 sm:gap-10">
    <section class="flex-1 flex flex-col lg:flex-row justify-center gap-6 sm:gap-10 lg:gap-20 items-center lg:items-start">
        <div class="w-full max-w-[520px] bg-[#068287] rounded-2xl shadow-2xl p-4 sm:p-6">
            <div class="flex items-center justify-between gap-3 text-white text-xs mb-4 sm:mb-6">
                <span id="tradeSelectionTitle" class="font-semibold">Selecciona las cartas de tu oferta</span>
                <input id="tradeCardSearch" type="text" placeholder="Buscar" class="h-8 w-32 sm:w-40 rounded-md bg-white px-2 text-xs text-black" />
            </div>

            <div id="tradeLeftGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 place-items-center min-h-[390px]"></div>

            <div class="mt-4 flex items-center justify-between gap-3 text-white text-xs">
                <button id="tradeLeftPrev" class="h-7 px-3 rounded-full bg-white/20 hover:bg-white/30 transition">&larr;</button>
                <span id="tradeLeftPageLabel" class="opacity-100">1 de 1</span>
                <button id="tradeLeftNext" class="h-7 px-3 rounded-full bg-white/20 hover:bg-white/30 transition">&rarr;</button>
            </div>

            <div class="mt-4 flex items-center justify-center gap-3">
                <button id="tradeLeftAccept" class="rounded-full bg-[#A4C351] px-4 py-2 text-sm font-semibold text-[#15321A] hover:opacity-90 transition">Aceptar seleccion</button>
                <button id="tradeLeftBack" class="hidden rounded-full bg-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/30 transition">Volver</button>
            </div>
            <p id="tradeBuilderStatus" class="mt-3 text-center text-xs text-white/90"></p>
        </div>

        <div class="w-full max-w-[520px] bg-[#068287] rounded-2xl shadow-2xl p-4 sm:p-6">
            <div class="flex justify-center gap-8 sm:gap-16 text-white text-xs sm:text-sm font-semibold mb-4">
                <button id="tradeTabOffer" class="opacity-100">Ofertar</button>
                <a href="/feedOfertas?filter=mine" class="opacity-80 hover:opacity-100">Mis ofertas</a>
            </div>

            <div class="bg-[#006064] rounded-xl p-3 sm:p-4 shadow-inner">
                <p class="text-white text-xs font-semibold mb-2">Mi oferta</p>
                <div id="tradeOfferGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4 min-h-[200px]"></div>

                <p class="text-white text-xs font-semibold mb-2">Estampas solicitadas</p>
                <div id="tradeRequestGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 min-h-[200px]"></div>
            </div>

            <div class="mt-5 sm:mt-6 flex justify-center">
                <button id="ofertar" class="bg-[#006064] text-white text-sm sm:text-base font-semibold px-8 sm:px-10 py-2 rounded-full shadow-md hover:shadow-lg hover:bg-[#329296] transition">
                    Ofertar
                </button>
            </div>
        </div>
    </section>
</main>
