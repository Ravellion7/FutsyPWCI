<?php $contenido = $contenido ?? ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsy</title>
    <link rel="stylesheet" href="/build/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_40%_35%,#8AB18D,#4D604F)]">

    <header class="w-[95%] md:w-[90%] mx-auto mt-3 sm:mt-4 bg-[#A4C351] rounded-2xl relative z-10 shadow-lg">
        <nav class="py-2 sm:py-3 md:py-4 px-3 sm:px-4 md:px-6 flex items-center justify-between">
            <div class="flex items-center">
                <img src="/img/Logo-PCI.png" alt="Logo" class="h-6 sm:h-7 md:h-8 w-auto">
            </div>

            <!-- Hamburger button for mobile -->
            <button id="hamburgerToggle" class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-white/20 transition-colors">
                <img src="/img/hamburgericon.png" alt="Menu" class="w-6 h-6">
            </button>

            <!-- Desktop navigation -->
            <ul class="hidden md:flex justify-center items-center gap-1 sm:gap-2 md:gap-4 text-xs sm:text-sm md:text-base">
                <li><a href="/altas" class="px-2 sm:px-3 md:px-4 py-1 md:py-2 rounded-lg font-medium hover:bg-white/20 transition-colors">Altas</a></li>
                <li><a href="/panel" class="px-2 sm:px-3 md:px-4 py-1 md:py-2 rounded-lg font-medium hover:bg-white/20 transition-colors">Panel</a></li>
            </ul>

            <!-- Desktop profile/logout buttons -->
            <div class="hidden md:flex items-center justify-end gap-2 md:gap-3">
                <span class="text-black font-medium text-sm md:text-base"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Perfil'); ?></span>
                <a href="#" aria-label="Ir al perfil" class="rounded-full">
                    <img src="/img/image3.png" alt="Perfil" class="h-8 w-8 md:h-10 md:w-10 rounded-full object-cover">
                </a>
                <a href="/logout" aria-label="Cerrar sesion" class="rounded-full hover:opacity-80 transition-opacity">
                    <img src="/img/logout.png" alt="Cerrar sesion" class="h-8 w-8 md:h-6 md:w-6 rounded-full object-cover">
                </a>
            </div>
        </nav>

        <!-- Mobile dropdown menu -->
        <div id="mobileMenu" class="hidden md:hidden flex-col bg-[#9FBE4E] border-t border-white/20 rounded-b-2xl">
            <ul class="flex flex-col gap-0 py-2">
                <li><a href="/altas" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Altas</a></li>
                <li><a href="/panel" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Panel</a></li>
                <li class="border-t border-white/20 mt-2"></li>
                <li><a href="/logout" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Cerrar Sesión</a></li>
            </ul>
        </div>
    </header>

    <?php echo $contenido ?>

    <footer class="bg-[#4D604F] px-4 sm:px-8 md:px-12 lg:px-20 py-6 md:py-10 text-white mt-10 md:mt-14">

        <div class="flex flex-col md:flex-row justify-between items-start gap-6 md:gap-10">
            <div class="max-w-md text-sm sm:text-base leading-6 sm:leading-7">
                <img src="/img/Logo-PCI.png" class="w-28 sm:w-32 md:w-40 mb-3 md:mb-4" alt="logo">
                <p>
                    Futsy League es un sitio web donde puedes coleccionar las estampillas
                    de tus jugadores favoritos, colecciona y se el primero en la tabla
                    con mas estampillas.
                </p>
            </div>

            <nav>
                <ul class="flex flex-wrap gap-4 sm:gap-6 md:gap-10 font-semibold text-xs sm:text-sm">
                    <li><a href="/altas" class="hover:text-[#A4C351]/80">ALTAS</a></li>
                    <li><a href="/panel" class="hover:text-[#A4C351]/80">PANEL</a></li>
                </ul>
            </nav>
        </div>

        <div class="border-t border-white/40 mt-6 md:mt-10"></div>

        <p class="text-center text-xs sm:text-sm text-white/70 mt-3 md:mt-4">
            © 2026 Todos los derechos reservados
        </p>

    </footer>

    <script src="/build/app.js" defer></script>
</body>
</html>
