<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<br>

## 1º Parte: Arquitetura API (Laravel)

1. Intalação projeto Laravel 5.7
    
        composer create-project --prefer-dist laravel/laravel blog "5.7.*"
    
2. Alterar timezone em config/app.php

        'timezone' => 'America/Sao_Paulo',

3. Criar Model/Migration de Category

        php artisan make:model Models\\Category -m

4. Adiciona coluna no migrate Category, adiciona no AppServiceProvider.php dentro do metodo  boot().

        Schema::defaultStringLength(191); 

Configura banco .env e roda migration

        php artisan migrate

5. Criar controller

        php artisan make:controller Api\\CategoryController

6. Cria metodo Index em CategoryController

        public function index(Category $category)
        {
            $categories = $category->all();

            return response()->json($categories, 200);
        }

Define rota do tipo GET em routes/api.php

        Route::get('categories', 'Api\CategoryController@index');

7. Faz insert manual no banco de veja o resultado (http://127.0.0.1:8000/api/categories).

8. Altera index, passando resposábilidade para model Category.
    
        public function index(Category $category, Request $request)
        {
            $categories = $category->getResults($request->name);

        return response()->json($categories, 200);
        }

9. Adiciona método na model Category

        public function getResults($name = null)
        {   
            if (!$name)
                return $this->get();

            return $this->where('name', 'LIKE', "%{$name}%")
                    ->get();
        }

Faça teste no browser ou postman passando filtro com ou sem nome

    http://127.0.0.1:8000/api/categories?name=test

    http://127.0.0.1:8000/api/categories

<br>
<b>INSERT Category</b>    

10. Adicione método construtor no controller CategoryController.

        private $category;

        public function __construct(Category $category)
        {   
            $this->category = $category;
        }

10.1 Adicione método store

        public function store(Request $request)
        {
            $category = $this->category->create($request->all());

            return response()->json($category, 201);
        }

10.2 Altera linha para receber propriedade '$this->category' criada no construct que recebe objeto.

        $categories = $this->category->getResults($request->name);

10.3 Adicione rota do tipo POST para store()

        Route::post('categories', 'Api\CategoryController@store');

10.4 Adicione fillable no model Category para permitir o insert

        protected $fillable = ['name'];

Faça teste de insert pelo postman (http://127.0.0.1:8000/api/categories?name=Nova Categoria)

<br>
<b>EDITAR Category</b>

11. Adiciona método update() em CategoryController

        public function update(Request $request, $id)
        {
            $category = $this->category->find($id);

            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            $category->update($request->all());

            return response()->json($category);
        }

11.1 Adiciona rota do tipo PUT

        Route::put('categories/{id}', 'Api\CategoryController@update');


<br>
<b>VALIDAÇÃO Category</b>

12. Criar formRequest, após criar estará disponível em app\Http\Request

        php artisan make:request StoreUpdateCategoryFormRequest

12.1 Primeiro passo, passar o authorize() para true

        public function authorize()
        {
            return true;
        }

12.2 Definir regras de validações. Informar que o campo 'name' vai ser requerido, minimo 3 caracteres, maximo 50 caracteres e unico na tabela categories.

        public function rules()
        {
            return [
                'name' => 'required|min:3|max:50|unique:categories',
            ];
        }

12.3 No método store() mudar parametro Request para o StoreUpdateCategoryFormRequest criado. Não esqueça de importar (use App\Http\Requests\StoreUpdateCategoryFormRequest;)

        public function store(StoreUpdateCategoryFormRequest $request)
        {
            $category = $this->category->create($request->all());

            return response()->json($category, 201);
        }

<br>
<b>Permitir Editar registro cuja informações são unicas no banco de dados.</b>

13. Altere o parametro Request para StoreUpdateCategoryFormRequest no método update()

        public function update(StoreUpdateCategoryFormRequest $request, $id)
        {
            ...

13.1 Altere o StoreUpdateCategoryFormRequest para que quando o valor for o mesmo o laravel permite alterar.

        public function rules()
        {
            return [
                'name' => "required|min:3|max:50|unique:categories,name,{$this->segment(3)},id",
            ...

<br>
<b>DELETE Category</b>

14 Criar método delete()

        public function delete($id)
        {
            $category = Category::find($id);
            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            $category->delete();
            
            return response()->json(['success' => true], 204);
        }

Observação: usado model 'Category::find($id)' ao invez do '$this->category->find($id)'.


14.1 Adiciona rota do tipo DELETE

        Route::delete('categories/{id}', 'Api\CategoryController@delete');


<br>
<b>Rota API Simplificada</b>

15. Comente as rotas já criada e adicione Rota API Simplificada (index, store, update, destroy).

        Route::apiResource('categories', 'Api\CategoryController');


<br>
<b>Visualizar detalhes de category com método show()</b>

16. Adicionar método show()

        public function show($id)
        {
            $category = $this->category->find($id);
            if(!$category)
                return response()->json(['error' => 'Not found'], 404);

            return response()->json($category, 200);
        }

Faça pesquisa pela url passando o id (http://127.0.0.1:8000/api/categories/2)

<br>
<b>Gestão de PRODUTOS com upload de imagens</b>

1. Criar Model

        php artisan make:model Models\\Product -m

2. Defina campos da tabela no migrate 'products' conforme abaixo:

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

<br>
<i>coluna 'name' vai aceitar 100 caracteres e tem que ser unico</i><br>
<i>colunas 'description' e 'image' inicia com valor null</i>
<br>

2.1 Execute a migration

        php artisan migrate

3. Criar factory Produtos para popular tabela com dados ficticios.

        php artisan make:seeder UsersTableSeeder

3.1 Inserir novo usuário no seed 'UsersTableSeeder'

        public function run()
        {
            User::create([
                'name'      => 'José Santana',
                'email'     => 'josesantana@gmail.com',
                'password'  => bcrypt('123456'),
            ]);
        }

3.2 Descomente o retorno do método run() do seed 'DatabaseSeeder'.

        $this->call(UsersTableSeeder::class);

3.3-Execute o seed

        php artisan db:seed

4. Criar Factory para inserir usuários fake

        php artisan make:factory ProductFactory

4.1 Defina os valores no factory criado.

        use App\Models\Product;
        use Faker\Generator as Faker;

        $factory->define(Product::class, function (Faker $faker) {
            return [
                'name'          => $faker->unique()->word,
                'description'   => $faker->sentence(),
            ];
        });

4.2 Criar novo seeder para definir quando registros deseja criar.

        php artisan make:seeder ProductsTableSeeder

4.3 Insira quantidade no seed ProductsTableSeeder, será criado 50 registros. 

        public function run()
        {
            factory(Product::class, 50)->create();
        }

4.4 Adicione no seed 'DatabaseSeeder' o seed 'ProductsTableSeeder'.

        public function run()
        {
            $this->call([
                UsersTableSeeder::class,
                ProductsTableSeeder::class,
            ]);
        }

4.5 Execute no teminal

        php artisan db:seed --class=ProductsTableSeeder

<br>
<b>RELACIONAMENTO entre Product e Categoriy</b>

5.  Alterar migrate de produto 'CreateProductsTable', adicionar relacionamento com a coluna 'category_id' e chave estrangeira na tabela Products.

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


5.1 Adicionar 'category_id' na factory 'ProductFactory.php'.

        $factory->define(Product::class, function (Faker $faker) {
            return [
                'category_id'   => 1,
                'name'          => $faker->unique()->word,
                'description'   => $faker->sentence(),
            ];
        });

5.2 Criar novo seed 

        php artisan make:seeder CategoriesTableSeeder

5.3 Adiciona novo name com valor

        public function run()
        {
            Category::create([
                'name' => 'PHP',
            ]);
        }

5.4 Incluir no seeder 'CategoriesTableSeeder' no 'DataBaseSeeder.php'

        public function run()
        {
            $this->call([
                UsersTableSeeder::class,
                CategoriesTableSeeder::class,
                ProductsTableSeeder::class,
            ]);
        }

5.5 Execute as migrations novamente com refresh e --seed para que seja excluido todas tabelas e criado novamente com todos seeds.

        php artisan migrate:refresh --seed

6. Listar produtos. Crie novo controller 'ProductController'

        php artisan make:controller Api\\ProductController --resource

6.1 Configura método index(). Não esqueça de importar model Product.

        public function index()
        {
            $products = Product::all();

            return response()->json($products, 200);
        }

<br>
<b>INSERT Produto</b>

7. Criar método store() no controller ProductController

            public function store(Request $request)
            {
                $novoProduto = $request->all();

                $product = $this->product->create($novoProduto);

                return response()->json($product, 201);
            }

7.1 Adiciona coluna 'category_id' no model Product 

            protected $fillable = ['name', 'description', 'image', 'category_id'];


<br>
<b>EDITE Produto</b>

8. Adiciona método update em ProductController

            public function update(Request $request, $id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                $product->update($request->all());
            }

<i>Faça teste no postman, passa url com o id do produto e passe os parametros desejado (name, description, category_id).</i>
    
            http://127.0.0.1:8000/api/products/54

<br>
<b>VALIDAÇÃO Produto</b>

9. Cria formRequest com nome StoreUpdateProductFormRequest

            php artisan make:request StoreUpdateProductFormRequest

9.1 Configura StoreUpdateProductFormRequest com as validações de cada campo. Passe o return do método authorize() para true.

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


<br>
<b>DELETE Produto</b>

10. Cria método destroy()

            public function destroy($id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                $product->delete();
                
                return response()->json(['Success' => true], 204);
            }

<br>
<b>Show Produto</b>

11. Adicione método show()

            public function show($id)
            {
                $product = $this->product->find($id);

                if(!$product)
                    return response()->json(['error' => 'Not Found'], 404);

                return response()->json($product);
            }

<br>
<b>UPLOAD Imagens - Produto</b>

12. Edite filesystems (config\filesystems.php), altere o segundo parametro 'local' para 'public'.

            'default' => env('FILESYSTEM_DRIVER', 'public'),

12.1 Criar link simbolico para que seja criado pasta 'storage'(storage\app\public) dentro do diretório /public. 

            php artisan storage:link

12.2 Atualizar método store()

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

13. Atualizar método update() para atualizar imagen de upload.

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

13.1 Adicionado variavel $path com caminho da pasta de imagem. Adicionado nova variavel nos métodos Store() e update()

            class ProductController extends Controller
            {
                private $product, $totalPage = 10;
                private $path = 'products';

                ...

<i>Método store()</i>

            $upload = $request->image->storeAs($this->path, $nameFile);


<br>
<b>DELETE Imagens - Produto</b>

14. Adiciona logica de verificação se a imagem existe, se sim vai deletar imagem

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



## 2º Parte: Autenticação JWT Laravel
## 3º Parte: Laravel + VueJs

