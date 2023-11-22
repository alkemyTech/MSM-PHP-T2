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

- Generá las claves de encriptación para crear tokens de acceso seguros ``` php artisan passport:install ```

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

# Paso 11: Testear Endpoints

Para testear endpoints con PHP UNIT se necesita:

- Copia el archivo .env.testing.example y crea uno llamado .env.testing a través del comando ``` cp .env.testing.example .env.testing ```

- Ejecuta las migraciones para crear las tablas de la base de datos para testing  con ``` php artisan migrate --env=testing```

- Generá las claves de encriptación para crear tokens de acceso seguros pero en la base de datos de testing ``` php artisan passport:install --env=testing```

- Corré los tests con el entorno de testing para no alterar la base de datos con este comando ``` php artisan test --env=testing ```

## ¿Como poblar las tablas con datos preeliminares?
En el proyecto se incorporaron Seeders y Factories que permiten junto con la libreria Faker, poblar las tablas con datos de prueba.
No es necesario instalar, dado que las carpetas están incluidas en el proyecto.

Para ejecutar, es necesario utilizar el comando php artisan db:seed. Se puede utilizar cuantas veces se requiera.

En el archivo database/seeders/DatebaseSeeder.php se encuentra la funcion run(), que se ejecuta con el comando php artisan db:seed. Esta función genera los datos de las tablas. Podemos seleccionar cuantos registros se desean agregar modificando el parametro en el metodo count().

Por otro lado, tambien se puede definir la locación de los datos que se van a generar. Dentro del archivo database/factories/UserFactory.php podemos decidir esta locación editando el parametro dentro del metodo fake(), correspondiente a los atributos 'name' y 'last_name', para crear nombres y apellidos de diferentes regiones del mundo.

Para conocer los id (UCI) puede visitar la siguiente web: https://www.localeplanet.com/icu/.

## ¿Cómo actualizar la descripción de una transaccion?

Mediante la funcion "updateTransaction" podemos actualizar la descripcion de una transacción realizada con anterioridad. 
Esta función espera el parametro "descrption" dentro del cuerpo de la solicitud HTTP.

#### Configuración de la solicitud

- Primero, crear una solicitud del tipo 'patch' en Postman
- Luego, colocar la URL: '/transactions/{transaction_id}'
- Por ultimo, en la pestaña 'row', se debe completar un campo llamado 'description', que no puede ser nulo.

> Nota: {transaction_id} debee reemplazarse por el numero id de la transacción que se desea actualizar.
