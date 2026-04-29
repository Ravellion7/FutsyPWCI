<?php $contenido = $contenido ?? ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futsy</title>
    <link rel="stylesheet" href="/build/app.css">
</head>
<body class="min-h-screen bg-[#4D604F] bg-[linear-gradient(rgba(77,96,79,0.80),rgba(77,96,79,0.80)),url('/img/futbolfield.jpg')] bg-[length:100%_auto] bg-repeat-y bg-top relative">

   <header class="w-[95%] md:w-[90%] mx-auto sticky top-2 md:top-4 bg-[#A4C351] rounded-xl md:rounded-2xl relative z-50 shadow-lg">
        <nav class="py-2 md:py-4 px-2 md:px-6 flex items-center justify-between">
            <div class="flex items-center">
                <img src="/img/Logo-PCI.png" alt="Logo" class="h-6 md:h-8 w-auto">
            </div>

            <!-- Hamburger button for mobile -->
            <button id="hamburgerToggle" class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-white/20 transition-colors">
                <img src="/img/hamburgericon.png" alt="Menu" class="w-6 h-6">
            </button>

            <!-- Desktop navigation -->
            <ul class="hidden md:flex justify-center items-center gap-1 sm:gap-2 lg:gap-4">
                <li><a href="#home" class="px-1 sm:px-2 lg:px-4 py-1 lg:py-2 rounded-lg text-xs sm:text-sm lg:text-base font-medium hover:bg-white/20 transition-colors">Home</a></li>
                <li><a href="#collect" class="px-1 sm:px-2 lg:px-4 py-1 lg:py-2 rounded-lg text-xs sm:text-sm lg:text-base font-medium hover:bg-white/20 transition-colors">Collect</a></li>
                <li><a href="#top" class="px-1 sm:px-2 lg:px-4 py-1 lg:py-2 rounded-lg text-xs sm:text-sm lg:text-base font-medium hover:bg-white/20 transition-colors">Top</a></li>
            </ul>

            <!-- Desktop auth buttons -->
            <div class="hidden md:flex items-center justify-end gap-1 sm:gap-2 md:gap-3">
                <a href="/login" class="px-1 sm:px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-base font-medium hover:bg-white/20 transition-colors">Login</a>
                <a href="/register" class="px-1 sm:px-2 md:px-4 py-1 md:py-2 rounded-lg text-xs md:text-base font-medium hover:bg-white/20 transition-colors">Register</a>
            </div>
        </nav>

        <!-- Mobile dropdown menu -->
        <div id="mobileMenu" class="hidden md:hidden flex-col bg-[#9FBE4E] border-t border-white/20 rounded-b-xl">
            <ul class="flex flex-col gap-0 py-2">
                <li><a href="#home" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Home</a></li>
                <li><a href="#collect" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Collect</a></li>
                <li><a href="#top" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Top</a></li>
                <li class="border-t border-white/20 mt-2"></li>
                <li><a href="/login" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Login</a></li>
                <li><a href="/register" class="block px-4 py-3 text-sm font-medium hover:bg-white/20 transition-colors">Register</a></li>
            </ul>
        </div>
    </header>

    <?php echo $contenido ?>

    <footer class="bg-[#4D604F] px-4 sm:px-8 md:px-12 lg:px-20 py-6 md:py-10 text-white">

    <div class="flex flex-col md:flex-row justify-between items-start gap-6 md:gap-0">

       
        <div class="max-w-md">
            <img src="/img/Logo-PCI.png" class="w-32 md:w-40 mb-4" alt="logo">
            <p class="text-sm md:text-base">
                Futsy League es un sitio web donde puedes coleccionar las estampillas
                de tus jugadores favoritos, colecciona y sé el primero en la tabla
                con más estampillas.
            </p>
        </div>

        <nav>
            <ul class="flex flex-wrap gap-4 md:gap-10 font-semibold text-xs md:text-sm">
                <li><a href="#" class="hover:text-[#A4C351]/80">HOME</a></li>
                <li><a href="#" class="hover:text-[#A4C351]/80">ALBUM</a></li>
                <li><a href="#" class="hover:text-[#A4C351]/80">TIENDA</a></li>
                <li><a href="#" class="hover:text-[#A4C351]/80">MERCADO</a></li>
            </ul>
        </nav>

    </div>
    
    <div class="border-t border-white/40 mt-10"></div>

    <p class="text-center text-sm text-white/70 mt-4">
        © 2026 Todos los derechos reservados.
    </p>

</footer>
       
    <script src="/build/app.js" defer></script>
</body>
</html>