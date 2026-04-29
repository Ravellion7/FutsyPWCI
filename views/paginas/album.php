<?php
if (!isset($teams) || !is_array($teams)) {
    $teams = [];
}
?>
<main class="px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-6">
    <section class="text-center">
        <h1 class="text-white font-extrabold tracking-wide text-3xl sm:text-4xl md:text-5xl mt-2 sm:mt-4 mb-4 sm:mb-6">
            ALBUM DE SELECCIONES
        </h1>
        <p class="text-white/85 text-xs sm:text-sm max-w-2xl mx-auto mb-6 sm:mb-10">
            Selecciona un equipo para ver su plantilla y las cartas que ya has desbloqueado en tu inventario.
        </p>
    </section>

    <section class="flex-1">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-y-5 sm:gap-y-6 md:gap-y-7 gap-x-3 sm:gap-x-4 md:gap-x-5 place-items-center">
            <?php foreach ($teams as $team): ?>
                <article class="flex flex-col items-center gap-1.5 sm:gap-2">
                    <p class="text-white text-[11px] sm:text-xs font-bold text-center"><?= htmlspecialchars($team['country']) ?></p>

                    <a href="<?= htmlspecialchars($team['team_url']) ?>" class="block">
                        <img class="w-[110px] h-[150px] sm:w-[130px] sm:h-[180px] md:w-[150px] md:h-[200px] rounded-xl shadow-lg hover:shadow-[0_25px_50px_-12px_rgba(255,255,255,0.35)] hover:scale-90 hover:-translate-y-2 transition-all duration-300 cursor-pointer border border-white/20 object-cover bg-white/10" src="<?= htmlspecialchars($team['flag_url']) ?>" alt="<?= htmlspecialchars($team['country']) ?>">
                    </a>

                    <p class="text-white text-[11px] sm:text-xs font-semibold">
                        <?= (int)$team['owned_cards'] ?> / <?= (int)$team['total_cards'] ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>