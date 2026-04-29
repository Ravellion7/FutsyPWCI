<main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-6">
    <div class="mt-2 sm:mt-6 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
        <a href="/ofertas" class="inline-flex items-center justify-center h-8 px-4 rounded-md bg-[#BF7D24] text-white text-xs font-semibold hover:bg-[#D99A45] transition-colors w-fit">
            Hacer oferta
        </a>
        <div class="flex items-center gap-2 sm:gap-3">
            <label class="text-xs text-white font-semibold" for="tradeFeedFilter">Filtro</label>
            <select id="tradeFeedFilter" class="h-8 w-[200px] sm:w-56 rounded-md bg-white px-2 text-sm">
                <option value="all">Todas</option>
                <option value="mine">Mis ofertas</option>
                <option value="received">Ofertas que puedo completar</option>
                <option value="completed">Ofertas completadas</option>
            </select>
        </div>
    </div>

    <p id="tradeFeedStatus" class="mt-4 text-sm text-white/90"></p>

    <section id="tradeFeedList" class="mt-8 sm:mt-10 md:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8 md:gap-10 lg:gap-14"></section>
</main>
