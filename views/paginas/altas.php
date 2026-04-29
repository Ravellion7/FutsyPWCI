 <main class="min-h-screen px-4 sm:px-6 md:px-10 lg:px-16 py-4 sm:py-8">

        <section class="mt-2 sm:mt-6 flex justify-center">
            <div class="w-full max-w-5xl bg-[#E8DDD7] rounded-2xl shadow-2xl overflow-hidden">

                <div class="bg-[#9C4C45] min-h-12 sm:h-14 flex items-center justify-center gap-6 sm:gap-20 md:gap-40 text-white text-sm sm:text-base font-bold px-4 py-2">
                    <button id="equipoTab" type="button" class="opacity-60 hover:opacity-100 transition-opacity">EQUIPO</button>
                    <button id="jugadorTab" type="button" class="opacity-100 hover:opacity-100 transition-opacity">JUGADOR</button>
                </div>

                <div id="jugadorWindow" class="p-4 sm:p-8 md:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 md:gap-10 items-start">

                        <div class="lg:col-span-3 flex flex-col items-center lg:items-start gap-3">
                            <p class="text-xs text-[#9C4C45] font-semibold mb-2 lg:ml-6">Fotografia</p>
                            <div class="w-24 h-36 sm:w-28 sm:h-40 bg-gray-400 rounded-xl overflow-hidden">
                                <img id="fotoJugadorPreview" src="" alt="Vista previa de fotografia" class="hidden w-full h-full object-cover" />
                            </div>
                            <label for="fotoJugador" class="cursor-pointer bg-[#9C4C45] text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-full hover:bg-[#C27670] transition">
                                Subir fotografia
                            </label>
                            <input id="fotoJugador" type="file" accept="image/*" class="hidden" name="photo" form="registrarForm" />
                        </div>

                        <div class="lg:col-span-9">
                            <form id="registrarForm" class="space-y-4" method="post" action="/altas" enctype="multipart/form-data">
                                <input type="hidden" name="form_type" value="player">

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Nombre completo</label>
                                    <input type="text" placeholder="Ingrese el nombre" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="player_name" />
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Numero de camiseta</label>
                                    <input type="number" min="1" placeholder="Ej. 01" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="shirt_number" />
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Posicion</label>
                                    <input type="text" placeholder="Ej. Delantero" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="position" />
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Rareza de la carta</label>
                                    <select class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="rarity">
                                        <option value="">Selecciona una rareza</option>
                                        <option value="Common">Common</option>
                                        <option value="Rare">Rare</option>
                                        <option value="Epic">Epic</option>
                                        <option value="Legendary">Legendary</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Equipo</label>
                                    <select class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="id_team">
                                        <option value="">Selecciona un equipo registrado</option>
                                        <?php foreach (($teams ?? []) as $team): ?>
                                            <option value="<?php echo (int)$team['id_team']; ?>"><?php echo htmlspecialchars($team['country']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="pt-4 sm:pt-8 flex justify-center sm:justify-end">
                                    <button type="submit" class="bg-[#9C4C45] text-white text-sm sm:text-base font-semibold px-8 py-2 rounded-full shadow-md hover:shadow-lg hover:bg-[#C27670] transition">
                                        Registrar jugador
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>

                <div id="equipoWindow" class="hidden p-4 sm:p-8 md:p-12">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 md:gap-10 items-start">

                        <div class="lg:col-span-3 flex flex-col items-center lg:items-start gap-3">
                            <p class="text-xs text-[#9C4C45] font-semibold mb-2 lg:ml-6">Escudo / Logo</p>
                            <div class="w-24 h-36 sm:w-28 sm:h-40 bg-gray-400 rounded-xl overflow-hidden">
                                <img id="logoEquipoPreview" src="" alt="Vista previa del escudo" class="hidden w-full h-full object-contain" />
                            </div>
                            <label for="logoEquipo" class="cursor-pointer bg-[#9C4C45] text-white text-xs sm:text-sm font-semibold px-4 py-2 rounded-full hover:bg-[#C27670] transition">
                                Subir escudo
                            </label>
                            <input id="logoEquipo" type="file" accept="image/*" class="hidden" name="flag" form="registrarEquipoForm" />
                        </div>

                        <div class="lg:col-span-9">
                            <form id="registrarEquipoForm" class="space-y-4" method="post" action="/altas" enctype="multipart/form-data">
                                <input type="hidden" name="form_type" value="team">

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Nombre de la seleccion</label>
                                    <input type="text" placeholder="Ingrese el nombre de la seleccion" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="country" />
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Cantidad de jugadores</label>
                                    <input type="number" min="1" placeholder="Ej. 23" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="players_amount" />
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Grupo</label>
                                    <select class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="group">
                                        <option value="">Selecciona un grupo</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                        <option value="F">F</option>
                                        <option value="G">G</option>
                                        <option value="H">H</option>
                                        <option value="I">I</option>
                                        <option value="J">J</option>
                                        <option value="K">K</option>
                                        <option value="L">L</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs text-[#9C4C45] font-semibold mb-1">Dato sobre el equipo</label>
                                    <input type="text" placeholder="Ingrese un dato sobre el equipo" class="w-full h-9 rounded-md bg-white px-3 focus:outline-none focus:ring-2 focus:ring-[#9E4A43]" name="team_fact" />
                                </div>

                                <div class="pt-4 sm:pt-8 flex justify-center sm:justify-end">
                                    <button type="submit" class="bg-[#9C4C45] text-white text-sm sm:text-base font-semibold px-8 py-2 rounded-full shadow-md hover:shadow-lg hover:bg-[#C27670] transition">
                                        Registrar equipo
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </section>

    </main>

<script>
    const registrarForm = document.getElementById('registrarForm');
    const registrarEquipoForm = document.getElementById('registrarEquipoForm');
    const equipoTab = document.getElementById('equipoTab');
    const jugadorTab = document.getElementById('jugadorTab');
    const equipoWindow = document.getElementById('equipoWindow');
    const jugadorWindow = document.getElementById('jugadorWindow');
    const fotoJugadorInput = document.getElementById('fotoJugador');
    const logoEquipoInput = document.getElementById('logoEquipo');
    const fotoJugadorPreview = document.getElementById('fotoJugadorPreview');
    const logoEquipoPreview = document.getElementById('logoEquipoPreview');

    function updateImagePreview(inputElement, previewElement) {
        const selectedFile = inputElement.files && inputElement.files[0];

        if (!selectedFile) {
            previewElement.src = '';
            previewElement.classList.add('hidden');
            return;
        }

        const imageUrl = URL.createObjectURL(selectedFile);
        previewElement.src = imageUrl;
        previewElement.classList.remove('hidden');

        previewElement.onload = function () {
            URL.revokeObjectURL(imageUrl);
        };
    }

    function showJugadorWindow() {
        jugadorWindow.classList.remove('hidden');
        equipoWindow.classList.add('hidden');
        jugadorTab.classList.remove('opacity-60');
        jugadorTab.classList.add('opacity-100');
        equipoTab.classList.remove('opacity-100');
        equipoTab.classList.add('opacity-60');
    }

    function showEquipoWindow() {
        equipoWindow.classList.remove('hidden');
        jugadorWindow.classList.add('hidden');
        equipoTab.classList.remove('opacity-60');
        equipoTab.classList.add('opacity-100');
        jugadorTab.classList.remove('opacity-100');
        jugadorTab.classList.add('opacity-60');
    }

    jugadorTab.addEventListener('click', showJugadorWindow);
    equipoTab.addEventListener('click', showEquipoWindow);

    fotoJugadorInput.addEventListener('change', function () {
        updateImagePreview(fotoJugadorInput, fotoJugadorPreview);
    });

    logoEquipoInput.addEventListener('change', function () {
        updateImagePreview(logoEquipoInput, logoEquipoPreview);
    });

    <?php if (($tabActiva ?? 'jugador') === 'equipo'): ?>
    showEquipoWindow();
    <?php else: ?>
    showJugadorWindow();
    <?php endif; ?>

    <?php if (!empty($erroresAltas)): ?>
    Swal.fire({
        title: 'No se pudo completar el registro',
        html: <?php echo json_encode(implode('<br>', $erroresAltas)); ?>,
        icon: 'error',
        confirmButtonColor: '#9C4C45'
    });
    <?php endif; ?>

    <?php if (!empty($exitoJugador)): ?>
    Swal.fire({
        title: 'Jugador registrado!',
        text: <?php echo json_encode($exitoJugador); ?>,
        icon: 'success',
        confirmButtonColor: '#9C4C45'
    });
    <?php endif; ?>

    <?php if (!empty($exitoEquipo)): ?>
    Swal.fire({
        title: 'Equipo registrado!',
        text: <?php echo json_encode($exitoEquipo); ?>,
        icon: 'success',
        confirmButtonColor: '#9C4C45'
    });
    <?php endif; ?>
</script>