<main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-16 pt-4 sm:pt-10 md:pt-16 pb-10 md:pb-20">
    <?php
        if (!isset($erroresProfile) || !is_array($erroresProfile)) {
            $erroresProfile = [];
        }
        if (!isset($erroresAvatar) || !is_array($erroresAvatar)) {
            $erroresAvatar = [];
        }
        if (!isset($erroresPassword) || !is_array($erroresPassword)) {
            $erroresPassword = [];
        }
        if (!isset($exitoAvatar) || !is_string($exitoAvatar)) {
            $exitoAvatar = '';
        }
        if (!isset($exitoPassword) || !is_string($exitoPassword)) {
            $exitoPassword = '';
        }

        $collectorName = $collector['fullname'] ?? ($_SESSION['fullname'] ?? 'Perfil');
        $collectorAvatar = $collector['avatar_url'] ?? '/img/image3.png';
        $totalCards = (int)($stats['total_cards'] ?? 0);
        $ranking = (int)($stats['ranking'] ?? 1);
        $collectorId = (int)($collector['id_collector'] ?? 0);
    ?>

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-10 items-start">
        <div class="flex flex-col gap-6">
            <div class="rounded-3xl bg-[#0B676B]/90 shadow-xl border border-white/15 p-5 sm:p-6 md:p-8">
                <div class="flex flex-col sm:flex-row items-center gap-5 sm:gap-6 text-center sm:text-left">
                    <div class="relative shrink-0">
                        <img id="avatarPreview" src="<?php echo htmlspecialchars($collectorAvatar); ?>" alt="Foto de perfil" class="w-28 h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 rounded-full object-cover border-4 border-white/30 shadow-lg">
                    </div>

                    <div class="text-white flex-1">
                        <p class="text-xs sm:text-sm uppercase tracking-[0.28em] text-white/70 font-semibold">Coleccionista</p>
                        <h1 class="mt-2 text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight"><?php echo htmlspecialchars($collectorName); ?></h1>
                        <div class="mt-4 flex flex-wrap justify-center sm:justify-start gap-3 sm:gap-4 text-xs sm:text-sm font-semibold">
                            <span class="rounded-full bg-white/15 px-4 py-2"><?php echo number_format($totalCards); ?> estampas</span>
                            <span class="rounded-full bg-white/15 px-4 py-2">Ranking #<?php echo number_format($ranking); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($erroresProfile)): ?>
                    <div class="mt-5 rounded-2xl border border-red-300/40 bg-red-500/15 px-4 py-3 text-sm text-red-100">
                        <?php foreach ($erroresProfile as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erroresAvatar)): ?>
                    <div class="mt-5 rounded-2xl border border-red-300/40 bg-red-500/15 px-4 py-3 text-sm text-red-100">
                        <?php foreach ($erroresAvatar as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($exitoAvatar)): ?>
                    <div class="mt-5 rounded-2xl border border-lime-300/40 bg-lime-500/15 px-4 py-3 text-sm text-lime-50">
                        <?php echo htmlspecialchars($exitoAvatar); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="mt-6 flex flex-col gap-4">
                    <input type="hidden" name="profile_action" value="avatar">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-white/90 text-sm sm:text-base">
                            <p class="font-semibold">Cambiar foto de perfil</p>
                            <p class="text-xs sm:text-sm text-white/65">Sube una nueva imagen para reemplazar la foto por defecto.</p>
                        </div>
                        <label for="avatarInput" class="inline-flex cursor-pointer items-center justify-center rounded-full bg-[#BF7D24] px-4 py-2 text-sm font-semibold text-white hover:bg-[#CE8F3A] transition shadow-md">
                            Elegir foto
                        </label>
                    </div>

                    <input id="avatarInput" type="file" name="avatar" accept="image/*" class="hidden">

                    <button type="submit" class="w-full sm:w-auto self-start rounded-full bg-white/15 px-5 py-2 text-sm font-semibold text-white hover:bg-white/25 transition border border-white/15">
                        Guardar foto
                    </button>
                </form>
            </div>

            <div class="rounded-3xl bg-[#0B676B]/90 shadow-xl border border-white/15 p-5 sm:p-6 md:p-8">
                <h2 class="text-white text-xl sm:text-2xl font-extrabold mb-4">Nueva contraseña</h2>

                <?php if (!empty($erroresPassword)): ?>
                    <div class="mb-4 rounded-2xl border border-red-300/40 bg-red-500/15 px-4 py-3 text-sm text-red-100">
                        <?php foreach ($erroresPassword as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($exitoPassword)): ?>
                    <div class="mb-4 rounded-2xl border border-lime-300/40 bg-lime-500/15 px-4 py-3 text-sm text-lime-50">
                        <?php echo htmlspecialchars($exitoPassword); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="profile_action" value="password">

                    <div>
                        <label class="block text-white text-xs sm:text-sm font-semibold mb-2" for="newPassword">Nueva contraseña</label>
                        <input id="newPassword" name="password" type="password" autocomplete="new-password" class="w-full rounded-md bg-white px-3 py-2 outline-none focus:ring-2 focus:ring-[#9E4A43]/35">
                    </div>

                    <div>
                        <label class="block text-white text-xs sm:text-sm font-semibold mb-2" for="confirmPassword">Confirmar contraseña</label>
                        <input id="confirmPassword" name="confirmPassword" type="password" autocomplete="new-password" class="w-full rounded-md bg-white px-3 py-2 outline-none focus:ring-2 focus:ring-[#9E4A43]/35">
                    </div>

                    <button id="enviarBtn" class="w-40 mx-auto block mt-2 rounded-full bg-[#BF7D24] px-4 py-2 text-white text-sm hover:bg-[#CE8F3A] shadow-md hover:shadow-lg transition" type="submit">
                        Actualizar contraseña
                    </button>
                </form>
            </div>
        </div>

        <div class="flex justify-center lg:justify-end pt-0 lg:pt-4">
            <div class="w-full max-w-[480px] rounded-3xl bg-[#006064] shadow-xl border border-white/10 p-5 sm:p-6 md:p-8 text-white">
                <div>
                    <p class="text-xs sm:text-sm uppercase tracking-[0.22em] text-white/70 font-semibold mb-3">Intercambios recientes</p>
                    <div class="space-y-3">
                        <?php if (!empty($tradeNotifications)): ?>
                            <?php foreach ($tradeNotifications as $notification): ?>
                                <div class="rounded-2xl bg-white/10 px-4 py-3">
                                    <p class="text-xs sm:text-sm leading-5"><?php echo htmlspecialchars($notification['message'] ?? 'Intercambio completado.'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rounded-2xl bg-white/10 px-4 py-3">
                                <p class="text-xs sm:text-sm leading-5">Aun no tienes intercambios completados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-10 sm:mt-12 md:mt-14">
        <div class="min-h-[64px] sm:h-[80px] bg-[#9FBE4E] rounded-xl shadow-lg py-3 sm:py-4 px-4 flex justify-center items-center">
            <h3 class="text-white text-2xl sm:text-3xl md:text-5xl font-extrabold text-center">INTERCAMBIA AHORA</h3>
        </div>

        <div class="mt-8 sm:mt-12 md:mt-16">
            <?php if (!empty($duplicateCards)): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 md:gap-8 lg:gap-12 justify-items-center">
                    <?php foreach ($duplicateCards as $duplicateCard): ?>
                        <article class="relative w-[120px] h-[170px] sm:w-[140px] sm:h-[200px] md:w-[160px] md:h-[220px] lg:w-[170px] lg:h-[236px]">
                            <img class="rounded-3xl shadow-lg w-full h-full object-cover border border-white/15" src="<?php echo htmlspecialchars($duplicateCard['card_url'] ?? '/img/image3.png'); ?>" alt="<?php echo htmlspecialchars($duplicateCard['player_name'] ?? 'Carta duplicada'); ?>">
                            <div class="absolute top-2 right-2 rounded-full bg-black/70 px-3 py-1 text-xs font-bold text-white shadow">x<?php echo (int)($duplicateCard['quantity'] ?? 0); ?></div>
                            <div class="absolute inset-x-2 bottom-2 rounded-2xl bg-black/60 m-1 px-3 py-2 text-center text-white backdrop-blur-sm">
                                <p class="text-xs font-bold truncate"><?php echo htmlspecialchars($duplicateCard['player_name'] ?? 'Sin nombre'); ?></p>
                                <p class="text-[11px] text-white/80 truncate"><?php echo htmlspecialchars($duplicateCard['rarity'] ?? 'Unknown'); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="rounded-3xl border border-white/20 bg-white/10 px-6 py-10 text-center text-white/90 shadow-lg">
                    <p class="text-lg font-semibold">Todavia no tienes cartas duplicadas.</p>
                    <p class="mt-2 text-sm text-white/70">Cuando repitas una carta en tu inventario, aparecera aqui para usarla en intercambios.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    (function () {
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');

        if (!avatarInput || !avatarPreview) {
            return;
        }

        avatarInput.addEventListener('change', function () {
            const file = avatarInput.files && avatarInput.files[0] ? avatarInput.files[0] : null;
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                if (event.target && typeof event.target.result === 'string') {
                    avatarPreview.src = event.target.result;
                }
            };
            reader.readAsDataURL(file);
        });
    })();
</script>
