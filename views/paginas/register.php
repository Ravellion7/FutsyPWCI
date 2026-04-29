<div class="absolute top-[20px] sm:top-[40px] md:top-0 left-[5px] sm:left-[20px] md:left-10 overflow-hidden h-[610px] sm:h-[390px] md:h-[710px] z-0">
        <div class="animate-vertical-loop-mid motion-reduce:animate-none will-change-transform flex flex-col gap-[15px] sm:gap-[25px] md:gap-3">
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Francia.jpeg" alt=""></div>
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Mexico.jpeg" alt=""></div>
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Alemania.jpeg" alt=""></div>
            <div class="md:hidden"><img class="w-[80px] h-[110px] rounded-lg object-cover" src="/img/canada.png" alt=""></div>
            <div class="md:hidden"><img class="w-[80px] h-[110px] rounded-lg object-cover" src="/img/Brasil.jpeg" alt=""></div>
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Francia.jpeg" alt=""></div>
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Mexico.jpeg" alt=""></div>
            <div><img class="w-[80px] h-[110px] sm:w-[120px] sm:h-[170px] md:w-[180px] md:h-[255px] rounded-lg object-cover" src="/img/Alemania.jpeg" alt=""></div>
            <div class="md:hidden"><img class="w-[80px] h-[110px] rounded-lg object-cover" src="/img/canada.png" alt=""></div>
            <div class="md:hidden"><img class="w-[80px] h-[110px] rounded-lg object-cover" src="/img/Brasil.jpeg" alt=""></div>
        </div>
    </div>



    <h2 class="block md:hidden absolute top-[20px] text-center w-full text-white font-bold font-Anton text-3xl sm:text-4xl drop-shadow-lg z-10">FUTSY LEAGUE<br/>MUNDIAL 2026</h2>
    <h2 class="hidden md:block absolute top-[270px] left-[250px] text-white font-bold font-Anton text-[40px] sm:text-[50px] lg:text-[65px] text-left drop-shadow-lg">FUTSY LEAGUE</h2>
    <h2 class="hidden md:block absolute top-[340px] left-[150px] text-white font-bold font-Anton text-[35px] sm:text-[40px] lg:text-[50px] text-left drop-shadow-lg"> MUNDIAL 2026</h2>

    <div class="bg-[#9FBE4E] px-4 sm:px-1 md:px-10 lg:px-12 py-4 sm:py-1 md:py-10 lg:py-12 rounded-xl shadow-2xl w-[65%] max-w-[420px] md:max-w-[500px] lg:max-w-[550px] xl:w-[36%] flex flex-col justify-between border border-white/20 hover:shadow-[0_25px_50px_-12px_rgba(164,195,81,0.4)] transition-shadow duration-300 mt-12 sm:mt-20 md:mt-0 ml-auto mr-1 sm:mr-0 md:mr-0 relative z-20">

        <a href="/" class="inline-block text-white text-[11px] sm:text-xs md:text-sm underline hover:text-white/80 mb-2 sm:mb-3 md:mb-4">Volver al inicio</a>
        <h3 class="text-white text-xl sm:text-2xl md:text-3xl font-bold text-center mb-2 sm:mb-3 md:mb-5 drop-shadow-md">Registrate</h3>

        <form class="flex flex-col gap-1.5 sm:gap-2 md:gap-3" action="" method="post">
            <div class="flex flex-col gap-0.5 sm:gap-1 md:gap-2">
                <label class="text-white text-[11px] sm:text-xs md:text-sm lg:text-base font-medium" for="name">Nombre completo</label>
                <input class="p-1.5 sm:p-2 md:p-2.5 lg:p-3 rounded-md border-2 border-transparent outline-none text-gray-600 text-[11px] sm:text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-[#BF7D24] focus:border-[#BF7D24] hover:border-[#BF7D24]/50 transition-all duration-200 shadow-sm" type="text" id="name" name="name" placeholder="Nombre completo" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
            </div>

            <div class="flex flex-col gap-0.5 sm:gap-1 md:gap-2">
                <label class="text-white text-[11px] sm:text-xs md:text-sm lg:text-base font-medium" for="email">Correo electrónico</label>
                <input class="p-1.5 sm:p-2 md:p-2.5 lg:p-3 rounded-md border-2 border-transparent outline-none text-gray-600 text-[11px] sm:text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-[#BF7D24] focus:border-[#BF7D24] hover:border-[#BF7D24]/50 transition-all duration-200 shadow-sm" type="email" id="email" name="email" placeholder="Ejemplo@correo.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="flex flex-col gap-0.5 sm:gap-1 md:gap-2">
                <label class="text-white text-[11px] sm:text-xs md:text-sm lg:text-base font-medium" for="password">Contraseña</label>
                <input class="p-1.5 sm:p-2 md:p-2.5 lg:p-3 rounded-md border-2 border-transparent outline-none text-gray-600 text-[11px] sm:text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-[#BF7D24] focus:border-[#BF7D24] hover:border-[#BF7D24]/50 transition-all duration-200 shadow-sm" type="password" id="password" name="password" placeholder="Ingrese su contraseña">
            </div>

            <div class="flex flex-col gap-0.5 sm:gap-1 md:gap-2">
                <label class="text-white text-[11px] sm:text-xs md:text-sm lg:text-base font-medium" for="confirmPassword">Confirmar contraseña</label>
                <input class="p-1.5 sm:p-2 md:p-2.5 lg:p-3 rounded-md border-2 border-transparent outline-none text-gray-600 text-[11px] sm:text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-[#BF7D24] focus:border-[#BF7D24] hover:border-[#BF7D24]/50 transition-all duration-200 shadow-sm" type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirmar contraseña">
            </div>

            <button type="submit" class="bg-[#CE8F3A] text-white font-bold py-1.5 sm:py-2 md:py-2.5 lg:py-3 px-5 sm:px-6 md:px-7 lg:px-8 rounded-full mt-1.5 sm:mt-2 md:mt-3 lg:mt-4 hover:bg-[#D99A45] hover:shadow-lg hover:shadow-[#CE8F3A]/40 transition-all duration-200 self-center text-[11px] sm:text-xs md:text-sm lg:text-base transform hover:scale-105 cursor-pointer active:scale-95">Registrarse</button>
            <p class="text-white text-center text-[11px] sm:text-xs md:text-sm mt-2 sm:mt-3 md:mt-4">Ya tienes una cuenta? <a href="/login" class="underline hover:text-white/80">Inicia sesión</a></p>
        </form>
    </div>

<?php if (!empty($errores)): ?>
<script>
    Swal.fire({
        title: 'Revisa estos errores',
        html: <?php echo json_encode(implode('<br>', $errores)); ?>,
        icon: 'error',
        confirmButtonColor: '#CE8F3A'
    });
</script>
<?php endif; ?>

<?php if (!empty($exito)): ?>
<script>
    Swal.fire({
        title: 'Registro exitoso',
        text: <?php echo json_encode($exito); ?>,
        icon: 'success',
        confirmButtonText: 'Ir a iniciar sesion',
        confirmButtonColor: '#CE8F3A',
        timer: 2500,
        timerProgressBar: true
    }).then(() => {
        window.location.href = '/login';
    });
</script>
<?php endif; ?>