# Futsy 

## Requisitos
- PHP 
- MySQL 
- Node.js y npm

## Instalación
1. Crea la base de datos importando el archivo schema.sql en MySQL
2. Copia y pega el archivo ".env.example, cámbiale el nombre a .env y configura tus credenciales.
3. Instala dependencias de frontend:

```bash
npm install
```

## Ejecutar el proyecto
1. Compila los estilos de Tailwind:

```bash
npm run build:css
```

2. Inicia el servidor PHP apuntando a la carpeta `public`:

```bash
php -S localhost:8000 -t public
```

3. Abre en el navegador:

```bash
http://localhost:8000
```
