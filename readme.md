
<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<br>


**Content**

## Contents

- [Arquitetura API](#1º_Parte:_Arquitetura_API_(Laravel))
- [Available Methods](#available-methods)


## 1º Parte: Arquitetura API (Laravel)

1. Intalação projeto Laravel 5.7
    
        composer create-project --prefer-dist laravel/laravel blog "5.7.*"
    
2. Alterar timezone em config/app.php
```php
        'timezone' => 'America/Sao_Paulo',
```
3. Criar Model/Migration de Category
```bash
        php artisan make:model Models\\Category -m
```
4. Adiciona coluna no migrate Category, adiciona no AppServiceProvider.php dentro do metodo  boot().
```php
        Schema::defaultStringLength(191); 
```
Configura banco .env e roda migration
```bash
php artisan migrate
```
5. Criar controller
```bash
php artisan make:controller Api\\CategoryController
```
6. Cria metodo Index em CategoryController
```php
public function index(Category $category)
{
    $categories = $category->all();

    return response()->json($categories, 200);
}
```
Define rota do tipo GET em routes/api.php
```php
        Route::get('categories', 'Api\CategoryController@index');
```

7. Faz insert manual no banco de veja o resultado (http://127.0.0.1:8000/api/categories).


8. Altera index, passando resposábilidade para model Category.
```php    
        public function index(Category $category, Request $request)
        {
            $categories = $category->getResults($request->name);

        return response()->json($categories, 200);
        }
```

9. Adiciona método na model Category
```php
        public function getResults($name = null)
        {   
            if (!$name)
                return $this->get();

            return $this->where('name', 'LIKE', "%{$name}%")
                    ->get();
        }
```
Faça teste no browser ou postman passando filtro com ou sem nome

    http://127.0.0.1:8000/api/categories?name=test

    http://127.0.0.1:8000/api/categories


**INSERT_Category** 

10. Adicione método construtor no controller CategoryController.
```php
        private $category;

        public function __construct(Category $category)
        {   
            $this->category = $category;
        }
```
10.1 Adicione método store
```php
        public function store(Request $request)
        {
            $category = $this->category->create($request->all());

            return response()->json($category, 201);
        }
```
10.2 Altera linha para receber propriedade '$this->category' criada no construct que recebe objeto.
```php
        $categories = $this->category->getResults($request->name);
```
10.3 Adicione rota do tipo POST para store()
```php
        Route::post('categories', 'Api\CategoryController@store');
```
10.4 Adicione fillable no model Category para permitir o insert
```php
        protected $fillable = ['name'];
```
Faça teste de insert pelo postman (http://127.0.0.1:8000/api/categories?name=Nova Categoria)

<br>


**EDITAR Category**

11. Adiciona método update() em CategoryController
```php
        public function update(Request $request, $id)
        {
            $category = $this->category->find($id);

            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            $category->update($request->all());

            return response()->json($category);
        }
```
11.1 Adiciona rota do tipo PUT
```php
        Route::put('categories/{id}', 'Api\CategoryController@update');
```

<br>


**VALIDAÇÃO Category**

12. Criar formRequest, após criar estará disponível em app\Http\Request
```bash
        php artisan make:request StoreUpdateCategoryFormRequest
```

12.1 Primeiro passo, passar o authorize() para true
```php
        public function authorize()
        {
            return true;
        }
```
12.2 Definir regras de validações. Informar que o campo 'name' vai ser requerido, minimo 3 caracteres, maximo 50 caracteres e unico na tabela categories.
```php
        public function rules()
        {
            return [
                'name' => 'required|min:3|max:50|unique:categories',
            ];
        }
```
12.3 No método store() mudar parametro Request para o StoreUpdateCategoryFormRequest criado. Não esqueça de importar (use App\Http\Requests\StoreUpdateCategoryFormRequest;)
```php
        public function store(StoreUpdateCategoryFormRequest $request)
        {
            $category = $this->category->create($request->all());

            return response()->json($category, 201);
        }
```
<br>


**Permitir Editar registro cuja informações são unicas no banco de dados.**

13. Altere o parametro Request para StoreUpdateCategoryFormRequest no método update()
```php
        public function update(StoreUpdateCategoryFormRequest $request, $id)
        {
            ...
```
13.1 Altere o StoreUpdateCategoryFormRequest para que quando o valor for o mesmo o laravel permite alterar.
```php
        public function rules()
        {
            return [
                'name' => "required|min:3|max:50|unique:categories,name,{$this->segment(3)},id",
            ...
```
<br>


**DELETE Category**

14 Criar método delete()
```php
        public function delete($id)
        {
            $category = Category::find($id);
            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            $category->delete();
            
            return response()->json(['success' => true], 204);
        }
```
Observação: usado model 'Category::find($id)' ao invez do '$this->category->find($id)'.


14.1 Adiciona rota do tipo DELETE
```php
        Route::delete('categories/{id}', 'Api\CategoryController@delete');

```
<br>


**Rota API Simplificada**

15. Comente as rotas já criada e adicione Rota API Simplificada (index, store, update, destroy).
```php
        Route::apiResource('categories', 'Api\CategoryController');
 ```

<br>


**Visualizar detalhes de category com método show()**

16. Adicionar método show()
```php
        public function show($id)
        {
            $category = $this->category->find($id);
            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            return response()->json($category, 200);
        }
```
Faça pesquisa pela url passando o id (http://127.0.0.1:8000/api/categories/2)

<br>

**Gestão de PRODUTOS com upload de imagens**

1. Criar Model
```bash
        php artisan make:model Models\\Product -m
```

2. Defina campos da tabela no migrate 'products' conforme abaixo:

```php
        public function up()
        {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->timestamps();
            });
        }
```
<br>

<i>coluna 'name' vai aceitar 100 caracteres e tem que ser unico</i><br>

<i>colunas 'description' e 'image' inicia com valor null</i>
<br>


2.1 Execute a migration
```bash
        php artisan migrate
```

3. Criar factory Produtos para popular tabela com dados ficticios.
```bash
        php artisan make:seeder UsersTableSeeder
```

3.1 Inserir novo usuário no seed 'UsersTableSeeder'
```php
        public function run()
        {
            User::create([
                'name'      => 'José Santana',
                'email'     => 'josesantana@gmail.com',
                'password'  => bcrypt('123456'),
            ]);
        }
```

3.2 Descomente o retorno do método run() do seed 'DatabaseSeeder'.
```php
        $this->call(UsersTableSeeder::class);
```

3.3-Execute o seed
```bash
        php artisan db:seed
```

4. Criar Factory para inserir usuários fake
```bash
        php artisan make:factory ProductFactory
```

4.1 Defina os valores no factory criado.
```php
        use App\Models\Product;
        use Faker\Generator as Faker;

        $factory->define(Product::class, function (Faker $faker) {
            return [
                'name'          => $faker->unique()->word,
                'description'   => $faker->sentence(),
            ];
        });
```

4.2 Criar novo seeder para definir quando registros deseja criar.
```bash
        php artisan make:seeder ProductsTableSeeder
```

4.3 Insira quantidade no seed ProductsTableSeeder, será criado 50 registros. 
```php
        public function run()
        {
            factory(Product::class, 50)->create();
        }
```

4.4 Adicione no seed 'DatabaseSeeder' o seed 'ProductsTableSeeder'.
```php
        public function run()
        {
            $this->call([
                UsersTableSeeder::class,
                ProductsTableSeeder::class,
            ]);
        }
```

4.5 Execute no teminal
```bash
        php artisan db:seed --class=ProductsTableSeeder
```

<br>

**RELACIONAMENTO entre Product e Categoriy**

5.  Alterar migrate de produto 'CreateProductsTable', adicionar relacionamento com a coluna 'category_id' e chave estrangeira na tabela Products.
```php
        public function up()
            {
                Schema::create('products', function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('category_id')->unsigned(); //adicionado
                    $table->foreign('category_id') //adicionado
                                ->references('id') //adicionado
                                ->on('categories') //adicionado
                                ->onDelete('cascade'); //adicionado
                    $table->string('name', 100)->unique();
                    $table->text('description')->nullable();
                    $table->string('image')->nullable();
                    $table->timestamps();
                });
            }

```
5.1 Adicionar 'category_id' na factory 'ProductFactory.php'.
```php
        $factory->define(Product::class, function (Faker $faker) {
            return [
                'category_id'   => 1,
                'name'          => $faker->unique()->word,
                'description'   => $faker->sentence(),
            ];
        });
```
5.2 Criar novo seed 
```bash
        php artisan make:seeder CategoriesTableSeeder
```

5.3 Adiciona novo name com valor
```php
        public function run()
        {
            Category::create([
                'name' => 'PHP',
            ]);
        }
```
5.4 Incluir no seeder 'CategoriesTableSeeder' no 'DataBaseSeeder.php'
```php
        public function run()
        {
            $this->call([
                UsersTableSeeder::class,
                CategoriesTableSeeder::class,
                ProductsTableSeeder::class,
            ]);
        }
```
5.5 Execute as migrations novamente com refresh e --seed para que seja excluido todas tabelas e criado novamente com todos seeds.
```bash
        php artisan migrate:refresh --seed
```

6. Listar produtos. Crie novo controller 'ProductController'
```bash
        php artisan make:controller Api\\ProductController --resource
```

6.1 Configura método index(). Não esqueça de importar model Product.
```php
        public function index()
        {
            $products = Product::all();

            return response()->json($products, 200);
        }
```
<br>

**INSERT Produto**

7. Criar método store() no controller ProductController
```php
            public function store(Request $request)
            {
                $novoProduto = $request->all();

                $product = $this->product->create($novoProduto);

                return response()->json($product, 201);
            }
```
7.1 Adiciona coluna 'category_id' no model Product 
```php
            protected $fillable = ['name', 'description', 'image', 'category_id'];
```

<br>

**EDITE Produto**

8. Adiciona método update em ProductController
```php
            public function update(Request $request, $id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                $product->update($request->all());
            }
```
<i>Faça teste no postman, passa url com o id do produto e passe os parametros desejado (name, description, category_id).</i>
    
            http://127.0.0.1:8000/api/products/54

<br>

**VALIDAÇÃO Produto**

9. Cria formRequest com nome StoreUpdateProductFormRequest
```bash
            php artisan make:request StoreUpdateProductFormRequest
```

9.1 Configura StoreUpdateProductFormRequest com as validações de cada campo. Passe o return do método authorize() para true.
```php
            public function authorize()
            {
                return true;
            }

            public function rules()
            {
                $id = $this->segment(3);
                
                return [
                    'category_id'   => 'required|exists:categories,id',
                    'name'          => "required|min:3|max:10|unique:products,name,{$id},id",
                    'description'   => 'max:1000',
                    'image'         => 'image',
                ];
            }

```
<br>

**DELETE Produto**

10. Cria método destroy()
```php
            public function destroy($id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                $product->delete();
                
                return response()->json(['Success' => true], 204);
            }
```
<br>

**Show Produto**

11. Adicione método show()
```php
            public function show($id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                return response()->json($product);
            }
```
<br>

**UPLOAD Imagens - Produto**

12. Edite filesystems (config\filesystems.php), altere o segundo parametro 'local' para 'public'.
```php
            'default' => env('FILESYSTEM_DRIVER', 'public'),
```
12.1 Criar link simbolico para que seja criado pasta 'storage'(storage\app\public) dentro do diretório /public. 
```bash
            php artisan storage:link
```

12.2 Atualizar método store()
```php
            public function store(Request $request)
            {
                $data = $request->all();

                if ($request->hasFile('image') && $request->file('image')->isValid()) {

                    $name = kebab_case($request->name);
                    $extension = $request->image->extension();
                    
                    $nameFile = "{$name}.{$extension}";
                    $data['image'] = $nameFile;
                    
                    $upload = $request->image->storeAs('products', $nameFile);

                    if (!$upload)
                        return response()->json(['error' => 'Fail_Upload'], 500);
                }

                $product = $this->product->create($data);

                return response()->json($product, 201);
            }
```
13. Atualizar método update() para atualizar imagen de upload.
```php
            public function update(Request $request, $id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                $data = $request->all();
                    
                if ($request->hasFile('image') && $request->file('image')->isValid()) {

                    if ($product->image) {
                        if (Storage::exists("{$this->path}/{$product->image}"))
                            Storage::delete("{$this->path}/{$product->image}");
                    }

                    $name = kebab_case($request->name);
                    $extension = $request->image->extension();
                    
                    $nameFile = "{$name}.{$extension}";
                    $data['image'] = $nameFile;
                    
                    $upload = $request->image->storeAs($this->path, $nameFile);

                    if (!$upload)
                        return response()->json(['error' => 'Fail_Upload'], 500);
                }

                $product->update($data);

                return response()->json($product);
            }
```
13.1 Adicionado variavel $path com caminho da pasta de imagem. Adicionado nova variavel nos métodos Store() e update()
```php
            class ProductController extends Controller
            {
                private $product, $totalPage = 10;
                private $path = 'products';

                ...
```
<i>Método store()</i>

```php
            $upload = $request->image->storeAs($this->path, $nameFile);
```
<br>

**DELETE Imagens - Produto**

14. Adiciona logica de verificação se a imagem existe, se sim vai deletar imagem
```php
            public function destroy($id)
            {
                $product = $this->product->find($id);   

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);
                
                // Deleta imagem
                if ($product->image) {
                    if (Storage::exists("{$this->path}/{$product->image}"))
                        Storage::delete("{$this->path}/{$product->image}");
                }            

                $product->delete();
                
                return response()->json(['Success' => true ], 204);
            }

```
<br>

**RELACIONAMENTO - Category X Produto**

Primeiro vamos fazer na Model Category, onde irá retornar todos os produtos relacionado a categoria.
Relacionamento um pra muitos '1 -> N' (Categoria pode ter vários produtos, mas 1 Produto só tem 1 categoria)

15. Adicione método `products()` na model Category. O método `hasMany()` faz relacionamento UM pra MUITOS.
O $this faz referencia a propria classe 'Category'. 
Como a class Product está no mesmo namespace não precisa importar.
```php
            public function productsCategory()
            {
                return $this->hasMany(Product::class);
            }

```

Segundo vamos fazer na Model Product.
15.1 Adiconar método category() para retorar a categoria do produto.
Produto só tem uma categoria vinculada a ele.
```php
            public function categoryProduct()
            {
                return $this->belongsTo(Category::class);
            }
```
15.2 Cria rota do relacionamento antes das rotas API "Route::apiResource(... ".
```php
            Route::get('categories/{id}/products', 'Api\CategoryController@products');
```
15.3 Cria o método productsCategory() em CategoryController.

```php
            // Método de RELACIONAMENTO Category -> Product
            public function products($id)
            {   // Recupera Categoria pelo seu ID
                $category = $this->category->find($id);
                if(!$category)
                    return response()->json(['error' => 'Not found'], 404);

                // Recupera Produtos. Método productsCategory() é o mesmo criado na model Category.
                $products = $category->productsCategory()->paginate($this->totalPage);        
                
                // Retorna produtos e categorias
                return response()->json([
                    'category' => $category,
                    'products' => $products,
                ]);
            }
```
15.4 Para listar os produtos da categoria adicione no método show() na listagem do produto o método 'with()', como parametro desse método passe o método criado no model category 'categoryProduct()'.  
```php
            public function show($id)
            {
                $product = $this->product->with('categoryProduct')->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                return response()->json($product);
            }

```
<br>

**VERSIONAMENTO de APIs**

16. Versionar rotas, adicione todas as rotas dentro do grupo de rotas, como parametro passe o prefix como 'v1' e namespace 'Api\v1'. Retire o prefixo 'Api\' dos controllers.
```php
            Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function(){

                Route::get('categories/{id}/products', 'CategoryController@products');
                Route::apiResource('categories', 'CategoryController');
                
                Route::apiResource('products', 'ProductController');
                
            });
```
16.1 Versionar controllers, crie uma pasta 'v1' no diretório Api (app\Http\Controllers\Api).
```php
            app\Http\Controllers\Api\v1
```
16.2 Atualize o namespace dos controllers adicionando a pasta 'v1'. 
```php
            namespace App\Http\Controllers\Api\v1;
```

<br>

**Configurações de Rotas (observação)**

As configurações de rotas estão disponíveis em app\Providers\RouteServiceProvider.php .

Configuração de namespace 
```php
            protected $namespace = 'App\Http\Controllers';
```
Configuração default de rotas API está no método mapApiRoutes()
```php
            protected function mapApiRoutes()
            {
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($this->namespace)
                    ->group(base_path('routes/api.php'));
            }

```
<br>

**Limitar requisições de APIs**

Para configurar limitar requisições, acessar app\Http\Kernel.php, no Middleware api alterar o valor da propriedade 'throttle', por default está configurado para 60 requisições.
```php
            'api' => [
                'throttle:60,1',
                'bindings',
            ],

```
<br>

**Tratamento de erros API**

Os error podem ser tratados no arquivo app\Exceptions\Handler.php

Tratando Error 404, adicionar if no método render() para quando a requisição for NotFoundHttpException que é o erro 404. 
```php
            public function render($request, Exception $exception)
            {
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
                    return response()->json(['error' => 'Not_found_URI'], 404);

                return parent::render($request, $exception);
            }
```
Tratamento de requisições AJAX. Faz verificação se a requisição e via ajax ou não.
```php
            public function render($request, Exception $exception)
            {
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
                    if ($request->expectsJson())
                        return response()->json(['error' => 'Not_found_URI'], 404);

                return parent::render($request, $exception);
            }
```
Requisição com verbo http errado. Adicione outra condição if() no método render() para tipo de execessão quando o metódo não existe 'MethodNotAllowedHttpException'.
```php
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException)
                if ($request->expectsJson())
                    return response()->json(['error' => 'Method_Not_Allowed'], $exception->getStatusCode());
```
<br>

**Liberação de CORS**

No CORS você pode autorizar ou não  requisições externas. Poderá utilizar o AJAX ou JavaScript puro, porém vamos utilizar o Axios.


1. Instalar Axios para fazer requisição Ajax (https://github.com/axios/axios). Executar no terminal 






<br>

****


## 2º Parte: Autenticação JWT Laravel
## 3º Parte: Laravel + VueJs

