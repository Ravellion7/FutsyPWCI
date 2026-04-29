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
<body class="min-h-screen bg-[radial-gradient(circle,#8AB18D,#4D604F)] flex items-center justify-start md:justify-end px-4 sm:px-6 md:pr-[200px] py-8 md:py-0">

        <?php echo $contenido ?>
</body>
</html>
