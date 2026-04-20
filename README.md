# sistema_gestion_tareas_reportes

TASK MANAGEMENT API - SYMFONY + VUE

Proyecto full-stack con Symfony (backend) y Vue 3 (frontend) en arquitectura monolítica. Incluye autenticación JWT, roles, reportes y frontend integrado.

============================================================
INICIO DEL PROYECTO
============================================================

Repositorio:
git clone <url-del-repo>
cd <nombre-del-proyecto>

Diseño previo (opcional):
https://excalidraw.com/

============================================================
CREAR PROYECTO SYMFONY
============================================================

composer create-project symfony/skeleton

============================================================
DEPENDENCIAS
============================================================

composer require doctrine orm
composer require symfony/validator
composer require symfony/security-bundle
composer require lexik/jwt-authentication-bundle
composer require symfony/serializer
composer require symfony/form
composer require symfony/messenger
composer require symfony/maker-bundle --dev

============================================================
BASE DE DATOS (.env)
============================================================

DATABASE_URL="mysql://root:@127.0.0.1:3306/task_db?serverVersion=mariadb-10.4.32&charset=utf8mb4"

============================================================
ENTIDADES Y MIGRACIONES
============================================================

Crear entidades:
php bin/console make:entity Task
php bin/console make:user

Migraciones:
php bin/console make:migration
php bin/console doctrine:migrations:migrate

============================================================
BACKEND (LÓGICA)
============================================================

Service:
TaskService

Controller:
php bin/console make:controller TaskController

Endpoints:
POST
GET
PUT
DELETE

Filtros:
Se manejan en TaskRepository

============================================================
JWT AUTHENTICATION
============================================================

Generar llaves:
php bin/console lexik:jwt:generate-keypair

Archivos generados:
private.pem
public.pem

============================================================
CONFIGURACIÓN SECURITY.YAML
============================================================

json_login:
    check_path: /api/login_check

jwt: ~

stateless: true

access_control:
    - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }

============================================================
CONFIGURACIÓN JWT
============================================================

lexik_jwt_authentication:
    secret_key: '%env(JWT_SECRET_KEY)%'
    public_key: '%kernel.project_dir%/config/jwt/public.pem'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600

============================================================
LOGIN ROUTE
============================================================

api_login_check:
    path: /api/login_check

============================================================
REFRESH TOKEN
============================================================

composer require gesdinet/jwt-refresh-token-bundle

api_token_refresh:
    path: /api/token/refresh
    stateless: true

============================================================
REPORTERÍA
============================================================

DTO de filtros
ReportService (CSV)
ReportController

PDF:
composer require dompdf/dompdf

Comando:
php bin/console make:command app:report:daily
php bin/console app:report:daily

============================================================
OPTIMIZACIÓN
============================================================

$sql = $query->getSQL();
$params = $query->getParameters();
dd($sql, $params);

Índice compuesto en Task:
created_at
status
priority

============================================================
FRONTEND (MONOLITO)
============================================================

Webpack Encore:
composer require symfony/webpack-encore-bundle

npm install @symfony/webpack-encore webpack webpack-cli --save-dev

Vue 3:
npm install vue
npm install vue-router@4
npm install axios core-js
npm install --save-dev vue-loader @vue/compiler-sfc

Twig:
composer require symfony/twig-bundle

Frontend:
assets/js/router.js para rutas Vue
Axios para comunicación con API
Twig para renderizado backend

============================================================
ROLES Y SEGURIDAD
============================================================

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]

============================================================
EJECUTAR SERVIDOR
============================================================

symfony local:server:start

============================================================
NOTAS FINALES
============================================================

Proyecto monolítico Symfony + Vue
API REST con JWT
Reportes en CSV y PDF
Frontend integrado
Control de acceso por roles
