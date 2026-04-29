<?php
if (!isset($team) || !is_array($team)) {
    $team = null;
}

if (!isset($playerSlots) || !is_array($playerSlots)) {
    $playerSlots = [];
}
?>
<main class="min-h-screen px-4 sm:px-6 md:px-8 lg:px-12 py-3 sm:py-5 flex flex-col">
    <?php if (!$team): ?>
        <section class="text-center text-white py-16">
            <h1 class="text-3xl sm:text-4xl font-extrabold mb-4">Equipo no encontrado</h1>
            <a href="/album" class="inline-flex rounded-full bg-[#BF7D24] px-5 py-2 text-white font-semibold hover:bg-[#CE8F3A] transition">
                Volver al album
            </a>
        </section>
    <?php else: ?>
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start flex-1">
            <?php
                $leftSlots = array_slice($playerSlots, 0, 3);
                $rightSlots = array_slice($playerSlots, 3);
            ?>
            <div class="lg:col-span-5 flex flex-col items-center">
                <h3 class="text-white font-extrabold text-4xl sm:text-5xl md:text-6xl text-center">
                    <?= htmlspecialchars($team['country']) ?>
                </h3>
                <h4 class="text-white font-semibold text-xs sm:text-sm mt-1 sm:mt-2">
                    GRUPO <?= htmlspecialchars($team['group']) ?>
                </h4>

                <div class="mt-3 sm:mt-4">
                    <img class="w-[190px] h-[245px] sm:w-[220px] sm:h-[280px] md:w-[240px] md:h-[310px] object-contain block rounded-2xl shadow-xl border border-white/20 bg-white/10 p-2" src="<?= htmlspecialchars($team['flag_url']) ?>" alt="<?= htmlspecialchars($team['country']) ?>" loading="lazy" decoding="async">
                </div>

                <p class="mt-1 text-white text-xs sm:text-sm text-center max-w-[360px] px-2 sm:px-0">
                    <?= htmlspecialchars($team['team_fact']) ?>
                </p>

                <div class="mt-7 grid grid-cols-3 gap-x-6 sm:gap-x-8 gap-y-4 sm:gap-y-5">
                    <?php foreach ($leftSlots as $slot): ?>
                        <?php
                            $cardImage = $slot['card_url'] ?? '/img/default.png';
                            $hasCard = (bool)($slot['has_card'] ?? false);
                            $slotName = $slot['player_name'] ?? 'Faltante';
                            $cardWrapperClass = 'w-[132px] h-[182px] sm:w-[145px] sm:h-[200px] md:w-[156px] md:h-[216px] bg-[#C4B8A7]/80 rounded-xl shadow-lg overflow-hidden border border-white/20';
                            $cardImageClass = $hasCard
                                ? 'w-full h-full object-cover block scale-125'
                                : 'w-full h-full object-contain block bg-[#b5b2a4] p-3';
                        ?>
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-white text-[10px] sm:text-xs font-bold uppercase text-center max-w-[110px] <?= $hasCard ? '' : 'opacity-80' ?>">
                                <?= htmlspecialchars($slotName) ?>
                            </p>
                            <div class="<?= $cardWrapperClass ?>">
                                <img class="<?= $cardImageClass ?>" src="<?= htmlspecialchars($cardImage) ?>" alt="<?= htmlspecialchars($slotName) ?>" loading="lazy" decoding="async">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lg:col-span-7 flex flex-col items-center">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-10 sm:gap-x-12 md:gap-x-12 gap-y-10 sm:gap-y-12 md:gap-y-14">
                    <?php foreach ($rightSlots as $slot): ?>
                        <?php
                            $cardImage = $slot['card_url'] ?? '/img/default.png';
                            $hasCard = (bool)($slot['has_card'] ?? false);
                            $slotName = $slot['player_name'] ?? 'Faltante';
                            $cardWrapperClass = 'w-[132px] h-[182px] sm:w-[145px] sm:h-[200px] md:w-[156px] md:h-[216px] bg-[#C4B8A7]/80 rounded-xl shadow-lg overflow-hidden border border-white/20';
                            $cardImageClass = $hasCard
                                ? 'w-full h-full object-cover block scale-125'
                                : 'w-full h-full object-contain block bg-[#b5b2a4] p-3';
                        ?>
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-white text-[10px] sm:text-xs font-bold uppercase text-center max-w-[120px] <?= $hasCard ? '' : 'opacity-80' ?>"><?= htmlspecialchars($slotName) ?></p>
                            <div class="<?= $cardWrapperClass ?>">
                                <img class="<?= $cardImageClass ?>" src="<?= htmlspecialchars($cardImage) ?>" alt="<?= htmlspecialchars($slotName) ?>" loading="lazy" decoding="async">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>