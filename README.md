## Proceso de instalación


# Paso 1: Instalación de XAMPP

- Descarga XAMPP desde el sitio web oficial (https://www.apachefriends.org/index.html) y selecciona la versión adecuada para tu sistema operativo.

- Ejecuta el archivo de instalación descargado y sigue las instrucciones del asistente de instalación. Durante la instalación, puedes elegir los componentes que deseas instalar, como Apache (para servir páginas web), MySQL (para bases de datos), PHP y otros.

- Una vez instalado, inicia XAMPP. Puedes hacerlo desde el menú de inicio o usando la aplicación XAMPP Control Panel si estás en Windows. 

# Paso 2: Configuración del Servidor Local

- Abre el Panel de Control de XAMPP y asegúrate de que los módulos Apache y MySQL están marcados como "Start"

- Abre tu navegador web y visita "http://localhost" o "http://127.0.0.1".

- Deberías ver la página de inicio de XAMPP, lo que indica que el servidor local está funcionando correctamente.

# Paso 3: Configuración de Visual Studio Code

- Descarga e instala Visual Studio Code desde el sitio web oficial (https://code.visualstudio.com/).

- Abre Visual Studio Code y se recomienda tener la extensión "PHP Intelephense" o una extensión similar instalada para mejorar la experiencia de desarrollo PHP. Puedes instalar extensiones desde la pestaña "Extensions" en VS Code.

# Paso 4: Correr el proyecto

- Copia este comando en tu terminal ``` git clone https://github.com/alkemyTech/MSM-PHP-T2.git ```

- Una vez clonado instalá las dependencias usando ``` composer install ```

- Copia el archivo .env.example y crea uno llamado .env a través del comando ``` cp .env.example .env ```

- Genera una clave única de aplicación con el comando ``` php artisan key:generate ```

- Ejecuta las migraciones para crear las tablas de bases de datos necesarias con ``` php artisan migrate ```

- Levanta el servidor corriendo en la terminal  ``` php artisan serve ```

# Paso 6: Instalación de Postman

Si aún no tienes Postman instalado, puedes descargarlo e instalarlo desde el sitio web oficial (https://www.postman.com/downloads/).

# Paso 7: Abrir Postman

Una vez instalado, abre Postman desde tu computadora.

# Paso 8: Crear una Solicitud HTTP

Puedes crear solicitudes haciendo clic en "Add Request" y dale un nombre descriptivo a la solicitud.

# Paso 9: Configurar la Solicitud

En la solicitud que has creado, selecciona el método HTTP que deseas utilizar (GET, POST, PUT, DELETE, etc.) en el menú desplegable.

# Paso 10: Enviar la Solicitud

Una vez que hayas configurado la solicitud según tus necesidades, simplemente haz clic en el botón "Send" para enviar la solicitud a la API.

# Paso 11: Inspeccionar la Respuesta

Postman mostrará la respuesta de la API en la parte inferior de la pantalla. Puedes ver el código de estado, los encabezados y el cuerpo de la respuesta.