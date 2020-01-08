<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

## 1º Parte: Arquitetura API (Laravel)

1-Intalação projeto Laravel 5.7

    composer create-project --prefer-dist laravel/laravel blog "5.7.*"

2-Alterar timezone em config/app.php

    'timezone' => 'America/Sao_Paulo',

3-Criar Model/Migration de Category

    php artisan make:model Models\\Category -m

4-Adiciona coluna no migrate Category, adiciona no AppServiceProvider.php dentro do metodo  boot().

    Schema::defaultStringLength(191); 

Configura banco .env e roda migration

    php artisan migrate

5-Criar controller

    php artisan make:controller Api\\CategoryController

6-Cria metodo Index em CategoryController

    public function index(Category $category)
    {
        $categories = $category->all();

        return response()->json($categories, 200);
    }

Define rota em routes/api.php

    Route::get('categories', 'Api\CategoryController@index');

7-Faz insert manual no banco de veja o resultado (http://127.0.0.1:8000/api/categories).

8-Altera index, passando resposábilidade para model Category.

    public function index(Category $category, Request $request)
    {
        $categories = $category->getResults($request->name);

        return response()->json($categories, 200);
    }

9-Adiciona método na model Category

    public function getResults($name = null)
    {   // verifica se está passando nome na pesquisa, Se sim traz tudo!
        if (!$name)
            return $this->get();

        // Se não, faz o like
        return $this->where('name', 'LIKE', "%{$name}%")
                ->get();
    }

Faça teste no browser ou postman passando filtro com ou sem nome

    http://127.0.0.1:8000/api/categories?name=test

    http://127.0.0.1:8000/api/categories

<br>
<b>INSERT Category</b>    

10-Adicione método construtor no controller CategoryController.

    // Propriedade category usando no construct
    private $category;
    
    // Criado construct para injetar Category automaticamente
    public function __construct(Category $category)
    {   //propriedade $category recebe objeto Category
        $this->category = $category;
    }

10.1-Adicione método store

    public function store(Request $request)
    {
        $category = $this->category->create($request->all());

        return response()->json($category, 201);
    }

10.2-Altera linha para receber propriedade '$this->category' criada no construct que recebe objeto.

    $categories = $this->category->getResults($request->name);

10.3-Adicione rota para store()

    Route::post('categories', 'Api\CategoryController@store');

10.4-Adicione fillable no model Category para permitir o insert

    protected $fillable = ['name'];

Faça teste de insert pelo postman (http://127.0.0.1:8000/api/categories?name=Nova Categoria)

<br>
<b>EDITAR Category</b>




## 2º Parte: Autenticação JWT Laravel
## 3º Parte: Laravel + VueJs

