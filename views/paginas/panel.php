<main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-6 flex flex-col gap-6 sm:gap-10">
    <section class="flex justify-center">
        <div class="w-full max-w-5xl bg-[#E8DDD7] rounded-2xl shadow-2xl overflow-hidden">

            <div class="bg-[#9C4C45] h-12 sm:h-14 flex items-center justify-center text-white text-sm sm:text-base font-bold">
                JUGADORES
            </div>

            <div class="p-4 sm:p-8 md:p-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 md:gap-10 items-start">

                    <div class="lg:col-span-6">
                        <div class="flex flex-col items-center mb-6 sm:mb-8">
                            <p class="text-xs text-[#9C4C45] font-semibold mb-2">Fotografia</p>
                            <div id="playerPhotoContainer" class="w-24 h-36 sm:w-28 sm:h-40 bg-gray-400 rounded-xl overflow-hidden">
                                <?php if (!empty($selectedPlayer['photo'])): ?>
                                    <?php
                                        $photoMime = 'image/jpeg';
                                        if (function_exists('finfo_open')) {
                                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                            if ($finfo) {
                                                $detectedMime = finfo_buffer($finfo, $selectedPlayer['photo']);
                                                unset($finfo);
                                                if (is_string($detectedMime) && str_starts_with($detectedMime, 'image/')) {
                                                    $photoMime = $detectedMime;
                                                }
                                            }
                                        }
                                    ?>
                                    <img id="playerPhoto" src="data:<?php echo $photoMime; ?>;base64,<?php echo base64_encode($selectedPlayer['photo']); ?>" alt="Foto jugador" class="w-full h-full object-cover" />
                                <?php endif; ?>
                            </div>
                        </div>

                        <form id="actualizarForm" class="space-y-4 max-w-md mx-auto" method="post" action="/panel" enctype="multipart/form-data">
                            <input type="hidden" name="player_id" value="<?php echo (int)($selectedPlayer['id_player'] ?? 0); ?>">
                            <input type="hidden" name="team_filter" value="<?php echo (int)($selectedTeamId ?? 0); ?>">

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Nombre completo</label>
                                <input type="text" id="playerName" name="name" value="<?php echo htmlspecialchars($selectedPlayer['name'] ?? ''); ?>" placeholder="Ingrese el nombre" class="w-full h-9 rounded-md bg-white px-3 outline-none focus:ring-2 focus:ring-[#9E4A43]/40" />
                            </div>

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Numero de camiseta</label>
                                <input type="number" min="1" id="playerShirtNumber" name="shirtnumber" value="<?php echo (int)($selectedPlayer['shirtnumber'] ?? 0); ?>" placeholder="Ej. 01" class="w-full h-9 rounded-md bg-white px-3 outline-none focus:ring-2 focus:ring-[#9E4A43]/40" />
                            </div>

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Posicion</label>
                                <input type="text" id="playerPosition" name="position" value="<?php echo htmlspecialchars($selectedPlayer['position'] ?? ''); ?>" placeholder="Ej. Delantero" class="w-full h-9 rounded-md bg-white px-3 outline-none focus:ring-2 focus:ring-[#9E4A43]/40" />
                            </div>

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Rareza de la carta</label>
                                <select id="playerRarity" name="rarity" class="w-full h-9 rounded-md bg-white px-3 outline-none focus:ring-2 focus:ring-[#9E4A43]/40">
                                    <option value="">Selecciona</option>
                                    <?php
                                        $rarities = ['Common', 'Rare', 'Epic', 'Legendary'];
                                        $currentRarity = $selectedPlayer['rarity'] ?? '';
                                    ?>
                                    <?php foreach ($rarities as $rarity): ?>
                                        <option value="<?php echo htmlspecialchars($rarity); ?>" <?php echo $currentRarity === $rarity ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rarity); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Equipo</label>
                                <select id="playerTeam" name="id_team" class="w-full h-9 rounded-md bg-white px-3 outline-none focus:ring-2 focus:ring-[#9E4A43]/40">
                                    <option value="">Selecciona</option>
                                    <?php foreach (($teams ?? []) as $team): ?>
                                        <option value="<?php echo (int)$team['id_team']; ?>" <?php echo (int)($selectedPlayer['id_team'] ?? 0) === (int)$team['id_team'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($team['country']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Nueva fotografia (opcional)</label>
                                <input type="file" name="photo" accept="image/*" class="w-full h-9 rounded-md bg-white px-2 py-1 outline-none focus:ring-2 focus:ring-[#9E4A43]/40" />
                            </div>

                            <div class="pt-4 sm:pt-6 flex justify-center gap-3 sm:gap-4">
                                <button type="submit" name="form_type" value="update_player" class="bg-[#9C4C45] text-white text-sm sm:text-base font-semibold px-8 sm:px-10 py-2 rounded-full shadow-md hover:shadow-lg hover:bg-[#C27670] transition">
                                    Actualizar
                                </button>
                                <button type="submit" name="form_type" value="delete_player" onclick="return confirm('¿Seguro que deseas eliminar este jugador? Esta acción no se puede deshacer.');" class="bg-[#7A2323] text-white text-sm sm:text-base font-semibold px-8 sm:px-10 py-2 rounded-full shadow-md hover:shadow-lg hover:bg-[#9C2F2F] transition">
                                    Eliminar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="lg:col-span-6">
                        <form class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 mb-4 sm:mb-6">
                            <label class="text-xs text-[#9C4C45] font-semibold">Equipo</label>
                            <select id="teamSelect" class="h-8 w-full sm:w-48 rounded-md bg-white px-2 text-sm">
                                <option value="">Selecciona</option>
                                <?php foreach (($teams ?? []) as $team): ?>
                                    <option value="<?php echo (int)$team['id_team']; ?>" <?php echo (int)($selectedTeamId ?? 0) === (int)$team['id_team'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($team['country']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="space-y-3">
                            <div id="playersList" class="space-y-3">
                                <?php if (!empty($players)): ?>
                                    <?php foreach ($players as $player): ?>
                                        <button type="button" class="playerBtn h-8 w-full rounded-sm flex items-center <?php echo (int)($selectedPlayerId ?? 0) === (int)$player['id_player'] ? 'bg-[#C27670]' : 'bg-[#9C4C45]'; ?> hover:bg-[#C27670] transition-colors" data-player-id="<?php echo (int)$player['id_player']; ?>">
                                            <p class="text-white px-3 text-sm"><?php echo htmlspecialchars($player['name']); ?></p>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="h-10 rounded-sm flex items-center bg-[#9C4C45]">
                                        <p class="text-white px-3 text-sm">No hay jugadores registrados para este equipo.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const teamSelect = document.getElementById('teamSelect');
        const playersList = document.getElementById('playersList');
        let currentTeamId = null;
        let currentPlayerId = null;

        // Cargar jugadores cuando cambia el equipo
        teamSelect.addEventListener('change', async function() {
            const teamId = this.value;
            
            if (!teamId) {
                playersList.innerHTML = '<div class="h-10 rounded-sm flex items-center bg-[#9C4C45]"><p class="text-white px-3 text-sm">Selecciona un equipo primero.</p></div>';
                return;
            }

            currentTeamId = teamId;
            currentPlayerId = null;

            try {
                const response = await fetch(`/api/admin/team-players?id_team=${teamId}`);
                const result = await response.json();

                if (result.ok && result.data.length > 0) {
                    playersList.innerHTML = result.data.map(player => 
                        `<button type="button" class="playerBtn h-8 w-full rounded-sm flex items-center hover:opacity-80 transition-opacity" data-player-id="${player.id_player}" style="background-color: #9C4C45;">
                            <p class="text-white px-3 text-sm">${escapeHtml(player.name)}</p>
                        </button>`
                    ).join('');
                    
                    attachPlayerButtonListeners();
                } else {
                    playersList.innerHTML = '<div class="h-10 rounded-sm flex items-center bg-[#9C4C45]"><p class="text-white px-3 text-sm">No hay jugadores registrados para este equipo.</p></div>';
                }
            } catch (error) {
                console.error('Error loading players:', error);
                playersList.innerHTML = '<div class="h-10 rounded-sm flex items-center bg-[#9C4C45]"><p class="text-white px-3 text-sm">Error al cargar los jugadores.</p></div>';
            }
        });

        // Cargar detalles del jugador cuando se selecciona
        function attachPlayerButtonListeners() {
            document.querySelectorAll('.playerBtn').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const playerId = this.dataset.playerId;
                    
                    try {
                        const response = await fetch(`/api/admin/player-detail?id_player=${playerId}`);
                        const result = await response.json();

                        if (result.ok) {
                            const player = result.data;
                            
                            // Actualizar los campos del formulario
                            document.getElementById('playerName').value = player.name || '';
                            document.getElementById('playerShirtNumber').value = player.shirtnumber || '';
                            document.getElementById('playerPosition').value = player.position || '';
                            document.getElementById('playerRarity').value = player.rarity || '';
                            document.getElementById('playerTeam').value = player.id_team || '';
                            document.querySelector('input[name="player_id"]').value = player.id_player || '';

                            // Actualizar la foto si existe
                            if (player.photo_url) {
                                const photoContainer = document.getElementById('playerPhotoContainer');
                                photoContainer.innerHTML = `<img id="playerPhoto" src="${player.photo_url}" alt="Foto jugador" class="w-full h-full object-cover" />`;
                            }

                            // Resaltar el botón del jugador seleccionado
                            document.querySelectorAll('.playerBtn').forEach(btn => {
                                btn.style.backgroundColor = '#9C4C45';
                            });
                            this.style.backgroundColor = '#C27670';

                            currentPlayerId = playerId;
                        }
                    } catch (error) {
                        console.error('Error loading player details:', error);
                        alert('Error al cargar los detalles del jugador');
                    }
                });
            });
        }

        attachPlayerButtonListeners();

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
</script>

<?php if (!empty($erroresPanel)): ?>
<script>
    Swal.fire({
        title: 'No se pudo actualizar',
        html: <?php echo json_encode(implode('<br>', $erroresPanel)); ?>,
        icon: 'error',
        confirmButtonColor: '#9C4C45'
    });
</script>
<?php endif; ?>

<?php if (!empty($exitoPanel)): ?>
<script>
    Swal.fire({
        title: 'Actualizado!',
        text: <?php echo json_encode($exitoPanel); ?>,
        icon: 'success',
        confirmButtonColor: '#9C4C45'
    });
</script>
<?php endif; ?>