<main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-20 xl:px-40 py-4 sm:py-6 flex flex-col">
        <section class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6 items-start flex-1">

            <div class="md:col-span-7 lg:col-span-6">
                <h3 class="text-white font-bold text-xs sm:text-sm mb-3 sm:mb-4">NOTIFICACIONES</h3>
                <div class="mb-3 sm:mb-4 flex flex-col gap-2">
                    <button id="claimDailyBtn" class="w-fit rounded-full bg-[#BF7D24] px-4 py-2 text-white text-xs sm:text-sm font-semibold hover:bg-[#CE8F3A] transition">Reclamar pack diario</button>
                    <p id="claimDailyStatus" class="text-white text-xs sm:text-sm"></p>
                </div>
                <div class="space-y-3 sm:space-y-4">
                    <?php if (!empty($tradeNotifications)): ?>
                        <?php foreach ($tradeNotifications as $notification): ?>
                            <div class="min-h-14 sm:min-h-16 rounded-xl bg-[#006064] shadow-lg px-3 sm:px-4 py-3 text-white text-xs sm:text-sm flex items-center">
                                <p class="leading-5"><?php echo htmlspecialchars($notification['message'] ?? 'Intercambio completado.'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="min-h-14 sm:min-h-16 rounded-xl bg-[#006064] shadow-lg px-3 sm:px-4 py-3 text-white text-xs sm:text-sm flex items-center">
                            <p class="leading-5">Aun no tienes intercambios completados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="md:col-span-5 lg:col-span-6 flex justify-center md:justify-end mt-2 md:mt-0">
                <img class="w-[220px] h-[320px] sm:w-[280px] sm:h-[400px] md:w-[320px] md:h-[460px] lg:w-[350px] lg:h-[500px] rounded-xl object-cover" src="/img/Mexico.jpeg" alt="México">
            </div>

            <div class="md:col-span-12 mt-1 sm:mt-2 md:mt-0">
                <div class="flex items-center justify-between text-white text-[11px] sm:text-xs font-semibold mb-2 px-1">
                    <span>Progreso <?php echo (int)($progressPercentage ?? 0); ?>% (<?php echo (int)($ownedUniqueCards ?? 0); ?>/<?php echo (int)($totalCardsGoal ?? 180); ?>)</span>
                    <span>100%</span>
                </div>

                <div class="w-full h-4 sm:h-5 md:h-6 rounded-full bg-white/90 shadow-inner">
                    <div class="h-full bg-[#A7C34E] rounded-full transition-all duration-500" style="width: <?php echo (int)($progressPercentage ?? 0); ?>%;"></div>
                </div>
            </div>

            <!-- Upcoming Matches Section -->
            <div class="md:col-span-12 mt-4 sm:mt-6 md:mt-8">
                <h3 class="text-white font-bold text-xs sm:text-sm mb-3 sm:mb-4 flex items-center gap-2">
                    <span>PRÓXIMOS PARTIDOS</span>
                </h3>
                <div id="upcomingMatchesContainer" class="space-y-2 max-h-80 overflow-y-auto">
                    <p class="text-white/70 text-xs sm:text-sm text-center py-4">Cargando partidos...</p>
                </div>
            </div>

        </section>
    </main>

    <style>
        #upcomingMatchesContainer::-webkit-scrollbar {
            width: 6px;
        }

        #upcomingMatchesContainer::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        #upcomingMatchesContainer::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        #upcomingMatchesContainer::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch('/api/external/upcoming-matches');
                const result = await response.json();

                if (!result.ok) {
                    throw new Error(result.error || 'Error desconocido');
                }

                const { matches } = result.data;
                renderUpcomingMatches(matches);
            } catch (error) {
                console.error('Error loading upcoming matches:', error);
                const container = document.getElementById('upcomingMatchesContainer');
                container.innerHTML = '<p class="text-red-300 text-xs sm:text-sm text-center py-4">Error al cargar partidos</p>';
            }
        });

        function renderUpcomingMatches(matches) {
            const container = document.getElementById('upcomingMatchesContainer');
            
            if (!matches || matches.length === 0) {
                container.innerHTML = '<p class="text-white/70 text-xs sm:text-sm text-center py-4">Sin partidos próximos</p>';
                return;
            }

            let html = matches.map((match, idx) => {
                const homeTeam = match.homeTeam?.name || 'Por confirmar';
                const awayTeam = match.awayTeam?.name || 'Por confirmar';
                const status = match.status || 'TIMED';
                const utcDate = match.utcDate ? new Date(match.utcDate) : null;
                
                let timeStr = 'Hora no disponible';
                if (utcDate) {
                    const hours = utcDate.getHours().toString().padStart(2, '0');
                    const mins = utcDate.getMinutes().toString().padStart(2, '0');
                    const day = utcDate.getDate().toString().padStart(2, '0');
                    const month = (utcDate.getMonth() + 1).toString().padStart(2, '0');
                    timeStr = `${day}/${month} ${hours}:${mins}`;
                }

                let statusBadge = 'PRÓXIMO';
                let statusColor = 'text-blue-400';
                
                if (status === 'LIVE') {
                    statusBadge = 'EN VIVO';
                    statusColor = 'text-red-400';
                } else if (status === 'SCHEDULED' || status === 'TIMED') {
                    statusBadge = 'PRÓXIMO';
                    statusColor = 'text-blue-400';
                }

                return `
                    <div class="p-2 sm:p-3 bg-white/5 rounded-lg border border-white/10 hover:bg-white/10 transition">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold ${statusColor}">${statusBadge}</span>
                            <span class="text-xs text-white/60">${timeStr}</span>
                        </div>
                        <div class="mt-1 sm:mt-2 text-center">
                            <p class="text-white text-xs sm:text-sm font-semibold line-clamp-2">${homeTeam} <span class="text-white/50">vs</span> ${awayTeam}</p>
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        }
    </script>
