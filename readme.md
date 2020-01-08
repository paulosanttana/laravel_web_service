<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## 1º Parte: Arquitetura API (Laravel)
	1-Intalação laravel (composer create-project --prefer-dist laravel/laravel blog "5.7.*")
	2-Alterar timezone em config/app.php ('timezone' => 'America/Sao_Paulo',)
    3-Criar Model/Migration de Category (php artisan make:model Models\\Category -m)
    4-Adiciona coluna no migrate Category, adiciona Schema::defaultStringLength(191); no AppServiceProvider.php, configura banco .env e roda migration(php artisan migrate)
    5-Criar controller (php artisan make:controller Api\\CategoryController).
    6-Cria metodo Index em CategoryController, define rota em routes/api.php (Route::get('categories', 'Api\CategoryController@index');).
    7-Faz insert manual no banco de veja o resultado (http://127.0.0.1:8000/api/categories).

## 2º Parte: Autenticação JWT Laravel
## 3º Parte: Laravel + VueJs
<hr>
